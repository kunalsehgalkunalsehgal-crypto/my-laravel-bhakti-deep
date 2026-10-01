<?php

namespace Tests\Feature;

use App\Contracts\VideoMeetingProvider;
use App\Mail\BookingLifecycleMail;
use App\Models\Admin\HawanSession;
use App\Models\Admin\NotificationLog;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Models\VideoMeeting;
use App\Services\UserBookingNotificationService;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class BookingLifecycleMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_paid_booking_mail_is_sent_to_pandit_once(): void
    {
        Mail::fake();

        [$user, $pandit] = $this->people();

        $session = HawanSession::create([
            'user_id' => $user->id,
            'pandit_id' => $pandit->id,
            'service_type' => 'hawan',
            'booking_mode' => 'online',
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '7:00 PM - 8:00 PM',
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['hawan_name' => 'Maha Hawan']),
        ]);

        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'payable_type' => HawanSession::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'gateway' => 'razorpay_test',
            'gateway_order_id' => 'order_mail_test',
            'gateway_payment_id' => 'pay_mail_test',
            'amount' => 501,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $service = app(UserBookingNotificationService::class);
        $service->newPaidBookingForPandit($session, $attempt);
        $service->newPaidBookingForPandit($session->fresh(), $attempt->fresh());

        Mail::assertSent(BookingLifecycleMail::class, 1);
        Mail::assertSent(BookingLifecycleMail::class, function (BookingLifecycleMail $mail) use ($pandit) {
            return $mail->hasTo($pandit->email)
                && $mail->details['eyebrow'] === 'NEW PAID BOOKING'
                && $mail->details['heading'] === 'A New Booking Is Waiting For You';
        });

        $this->assertDatabaseHas('notifications', [
            'user_id' => null,
            'channel' => 'email',
            'message_type' => 'pandit_new_paid_booking_hawan_'.$session->id.'_'.$attempt->id,
            'recipient' => $pandit->email,
            'delivery_status' => 'sent',
        ]);
    }

    #[DataProvider('acceptedBookingProvider')]
    public function test_booking_accepted_route_mails_user_once(string $type, string $mode): void
    {
        Mail::fake();
        $this->app->instance(VideoMeetingProvider::class, new LifecycleMailFakeVideoMeetingProvider);

        [$user, $pandit] = $this->people(
            $type.'-'.$mode.'-accepted-user@example.test',
            $type.'-'.$mode.'-accepted-pandit@example.test'
        );

        $model = $type === 'pooja' ? PoojaSession::class : HawanSession::class;
        $data = [
            'user_id' => $user->id,
            'pandit_id' => $pandit->id,
            'service_type' => $type,
            'booking_mode' => $mode,
            'state' => $mode === 'offline' ? 'Punjab' : null,
            'city' => $mode === 'offline' ? 'Ludhiana' : null,
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '10:00 AM - 11:00 AM',
            'slot_start_time' => '10:00:00',
            'slot_end_time' => '11:00:00',
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'admin_note' => json_encode([$type.'_name' => 'Accepted '.ucfirst($type)]),
        ];

        if ($type === 'pooja') {
            $data += [
                'pooja_type' => 'live',
                'pooja_type_title' => 'Live Pooja',
            ];
        } else {
            $data += [
                'hawan_type' => 'special',
                'hawan_type_title' => 'Special Hawan',
            ];
        }

        $session = $model::create($data);

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.accept', ['type' => $type, 'id' => $session->id]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $session->fresh()->status);
        $this->assertSame($mode === 'online', $session->fresh()->videoMeeting()->exists());

        Mail::assertSent(BookingLifecycleMail::class, 1);
        Mail::assertSent(BookingLifecycleMail::class, function (BookingLifecycleMail $mail) use ($user, $type, $mode) {
            $html = $mail->render();

            return $mail->hasTo($user->email)
                && $mail->details['eyebrow'] === 'BOOKING CONFIRMED'
                && $mail->details['heading'] === 'Pandit Ji Has Accepted Your Booking'
                && $mail->subject === ucfirst($type).' booking accepted - Accepted '.ucfirst($type).' | BhaktiDeep'
                && str_contains($html, ucfirst($mode));
        });

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'channel' => 'email',
            'message_type' => 'user_booking_accepted_'.$type.'_'.$session->id,
            'recipient' => $user->email,
            'delivery_status' => 'sent',
        ]);

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.accept', ['type' => $type, 'id' => $session->id]))
            ->assertRedirect()
            ->assertSessionHasErrors('booking');

        Mail::assertSent(BookingLifecycleMail::class, 1);
    }

    public static function acceptedBookingProvider(): array
    {
        return [
            'online Pooja' => ['pooja', 'online'],
            'offline Pooja' => ['pooja', 'offline'],
            'online Hawan' => ['hawan', 'online'],
            'offline Hawan' => ['hawan', 'offline'],
        ];
    }

    public function test_mail_failure_does_not_break_booking_acceptance(): void
    {
        Mail::fake();

        [$user, $pandit] = $this->people('failure-user@example.test', 'failure-pandit@example.test');
        $session = HawanSession::create([
            'user_id' => $user->id,
            'pandit_id' => $pandit->id,
            'service_type' => 'hawan',
            'hawan_type' => 'special',
            'hawan_type_title' => 'Special Hawan',
            'booking_mode' => 'offline',
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '1:00 PM - 2:00 PM',
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['hawan_name' => 'Failure-safe Hawan']),
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new RuntimeException('Simulated SMTP failure'));

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.accept', ['type' => 'hawan', 'id' => $session->id]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $session->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'message_type' => 'user_booking_accepted_hawan_'.$session->id,
            'delivery_status' => 'failed',
        ]);
        $this->assertSame(1, NotificationLog::where('delivery_status', 'failed')->count());
    }

    public function test_digital_pooja_does_not_send_these_pandit_lifecycle_mails(): void
    {
        Mail::fake();

        [$user] = $this->people('digital-user@example.test', 'unused-pandit@example.test');

        $session = PoojaSession::create([
            'user_id' => $user->id,
            'service_type' => 'pooja',
            'pooja_type' => 'digital',
            'pooja_type_title' => 'Digital Pooja',
            'status' => 'active',
            'payment_status' => 'paid',
        ]);

        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'payable_type' => PoojaSession::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'gateway' => 'razorpay_test',
            'gateway_order_id' => 'order_digital_mail_test',
            'gateway_payment_id' => 'pay_digital_mail_test',
            'amount' => 251,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $service = app(UserBookingNotificationService::class);
        $service->newPaidBookingForPandit($session, $attempt);
        $service->bookingAcceptedForUser($session);

        Mail::assertNothingSent();
    }

    private function people(
        string $userEmail = 'mail-user@example.test',
        string $panditEmail = 'mail-pandit@example.test'
    ): array {
        $user = User::factory()->create([
            'name' => 'Mail User',
            'email' => $userEmail,
        ]);

        $pandit = Pandit::create([
            'full_name' => 'Pandit Mail',
            'pandit_name' => 'Pandit Mail',
            'email' => $panditEmail,
            'status' => 'verified',
        ]);

        return [$user, $pandit];
    }
}

class LifecycleMailFakeVideoMeetingProvider implements VideoMeetingProvider
{
    public function providerName(): string
    {
        return 'fake';
    }

    public function createMeeting(Pandit $pandit, string $topic, CarbonInterface|string $startTime, int $duration): array
    {
        return [
            'provider' => 'fake',
            'external_meeting_id' => 'lifecycle-'.$pandit->id,
            'join_url' => 'https://meet.example.test/join/'.$pandit->id,
            'host_url' => 'https://meet.example.test/start/'.$pandit->id,
            'passcode' => '123456',
            'status' => 'scheduled',
        ];
    }

    public function hostUrl(VideoMeeting $meeting): string
    {
        return (string) $meeting->host_url;
    }

    public function embeddedMeetingConfig(VideoMeeting $meeting, bool $host, string $userName, ?string $userEmail = null): array
    {
        return [];
    }
}
