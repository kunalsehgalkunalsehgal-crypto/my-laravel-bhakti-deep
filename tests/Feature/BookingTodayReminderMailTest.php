<?php

namespace Tests\Feature;

use App\Contracts\VideoMeetingProvider;
use App\Mail\BookingTodayReminderMail;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Models\VideoMeeting;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingTodayReminderMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 9, 30, 10, 0, 0, 'Asia/Kolkata'));
        config(['services.razorpay.key_secret' => 'rzp_test_secret']);
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[DataProvider('eligibleBookingProvider')]
    public function test_today_paid_confirmed_booking_mails_user_and_pandit(string $type, string $mode): void
    {
        $session = $this->createSession($type, [
            'booking_mode' => $mode,
        ], withMeeting: $mode === 'online');

        $this->artisan('bookings:send-today-reminders')
            ->expectsOutput('Today booking reminder emails sent: 2')
            ->assertSuccessful();

        Mail::assertSent(BookingTodayReminderMail::class, function (BookingTodayReminderMail $mail) use ($session, $type, $mode) {
            if (! $mail->hasTo($session->user->email)) {
                return false;
            }

            $html = $mail->render();

            $this->assertStringContainsString('Reminder '.ucfirst($type), $html);
            $this->assertStringContainsString(ucfirst($mode), $html);
            $this->assertSame(
                'Today: Your '.ucfirst($type).' at 11:00 AM - 12:00 PM | BhaktiDeep',
                $mail->subject
            );

            return $mail->details['recipient_role'] === 'user'
                && $mail->details['mode_label'] === ucfirst($mode)
                && $mail->details['booking_reference'] === 'BD-'.strtoupper($type).'-'.$session->id;
        });
        Mail::assertSent(BookingTodayReminderMail::class, function (BookingTodayReminderMail $mail) use ($session, $type, $mode) {
            if (! $mail->hasTo($session->pandit->email)) {
                return false;
            }

            $html = $mail->render();

            $this->assertSame(
                'Today: '.ucfirst($type).' booking at 11:00 AM - 12:00 PM | BhaktiDeep',
                $mail->subject
            );
            $this->assertStringContainsString('Aaj aapki assigned '.ucfirst($type).' booking hai', $html);

            return $mail->details['recipient_role'] === 'pandit'
                && $mail->details['mode_label'] === ucfirst($mode);
        });
        Mail::assertSent(BookingTodayReminderMail::class, 2);
        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseMissing('notifications', ['delivery_status' => 'pending']);
        $this->assertDatabaseMissing('notifications', ['delivery_status' => 'failed']);
    }

    public static function eligibleBookingProvider(): array
    {
        return [
            'online Pooja' => ['pooja', 'online'],
            'offline Pooja' => ['pooja', 'offline'],
            'online Hawan' => ['hawan', 'online'],
            'offline Hawan' => ['hawan', 'offline'],
        ];
    }

    public function test_same_day_paid_booking_is_reminded_after_pandit_accepts_it(): void
    {
        $this->app->instance(VideoMeetingProvider::class, new TodayReminderFakeVideoMeetingProvider);

        $session = $this->createSession('pooja', [
            'status' => 'pending',
            'payment_status' => 'pending',
            'booking_mode' => 'online',
        ], withMeeting: false);

        $attempt = PaymentAttempt::create([
            'user_id' => $session->user_id,
            'payable_type' => PoojaSession::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'gateway' => 'razorpay',
            'gateway_order_id' => 'order_same_day_reminder',
            'amount' => 1100,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PENDING,
            'hold_started_at' => now(),
            'hold_expires_at' => null,
            'metadata' => [
                'pooja_name' => 'Reminder Pooja',
                'package_amount' => 1000,
                'donation_amount' => 100,
                'total_amount' => 1100,
            ],
        ]);
        $session->update([
            'latest_payment_attempt_id' => $attempt->id,
            'payment_hold_started_at' => $attempt->hold_started_at,
            'payment_hold_expires_at' => $attempt->hold_expires_at,
        ]);

        $this->assertTrue($session->created_at->isSameDay(now('Asia/Kolkata')));

        $paymentId = 'pay_same_day_reminder';
        $this->actingAs($session->user)
            ->postJson(route('payments.razorpay.verify'), [
                'payment_attempt_id' => $attempt->id,
                'razorpay_order_id' => $attempt->gateway_order_id,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => hash_hmac(
                    'sha256',
                    $attempt->gateway_order_id.'|'.$paymentId,
                    'rzp_test_secret'
                ),
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('scheduled', $session->fresh()->status);
        $this->assertSame('paid', $session->fresh()->payment_status);

        $this->actingAs($session->pandit, 'pandit')
            ->post(route('pandit.bookings.accept', ['type' => 'pooja', 'id' => $session->id]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $session->fresh()->status);
        $this->assertNotNull($session->fresh()->videoMeeting);

        Mail::fake();

        $this->artisan('bookings:send-today-reminders')
            ->expectsOutput('Today booking reminder emails sent: 2')
            ->assertSuccessful();

        Mail::assertSent(BookingTodayReminderMail::class, 2);
        Mail::assertSent(BookingTodayReminderMail::class, fn (BookingTodayReminderMail $mail) => $mail->hasTo($session->user->email));
        Mail::assertSent(BookingTodayReminderMail::class, fn (BookingTodayReminderMail $mail) => $mail->hasTo($session->pandit->email));
    }

    public function test_scheduled_unpaid_and_future_bookings_are_not_reminded(): void
    {
        $this->createSession('pooja', ['status' => 'scheduled'], withMeeting: true);
        $this->createSession('hawan', ['payment_status' => 'pending'], withMeeting: true);
        $this->createSession('pooja', ['booking_date' => now('Asia/Kolkata')->addDay()->toDateString()], withMeeting: true);

        $this->artisan('bookings:send-today-reminders')
            ->expectsOutput('Today booking reminder emails sent: 0')
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_digital_pooja_is_not_reminded(): void
    {
        $this->createSession('pooja', [
            'pooja_type' => 'digital',
            'pooja_type_title' => 'Recorded Digital Pooja',
        ], withMeeting: true);

        $this->artisan('bookings:send-today-reminders')
            ->expectsOutput('Today booking reminder emails sent: 0')
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertDatabaseCount('notifications', 0);
    }

    #[DataProvider('ritualTypeProvider')]
    public function test_online_booking_without_video_meeting_is_not_reminded(string $type): void
    {
        $this->createSession($type, ['booking_mode' => 'online'], withMeeting: false);

        $this->artisan('bookings:send-today-reminders')
            ->expectsOutput('Today booking reminder emails sent: 0')
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertDatabaseCount('notifications', 0);
    }

    public static function ritualTypeProvider(): array
    {
        return [
            'Pooja' => ['pooja'],
            'Hawan' => ['hawan'],
        ];
    }

    public function test_running_command_twice_does_not_send_duplicate_mail(): void
    {
        $session = $this->createSession('hawan', ['booking_mode' => 'offline'], withMeeting: false);

        $this->artisan('bookings:send-today-reminders')
            ->expectsOutput('Today booking reminder emails sent: 2')
            ->assertSuccessful();

        $this->artisan('bookings:send-today-reminders')
            ->expectsOutput('Today booking reminder emails sent: 0')
            ->assertSuccessful();

        Mail::assertSent(BookingTodayReminderMail::class, 2);
        Mail::assertSent(BookingTodayReminderMail::class, fn (BookingTodayReminderMail $mail) => $mail->hasTo($session->user->email));
        Mail::assertSent(BookingTodayReminderMail::class, fn (BookingTodayReminderMail $mail) => $mail->hasTo($session->pandit->email));
        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_missing_user_or_pandit_email_is_skipped_without_crashing_command(): void
    {
        $panditOnlySession = $this->createSession('pooja', [
            'user_id' => null,
            'booking_mode' => 'offline',
        ], withMeeting: false);

        $userOnlySession = $this->createSession('hawan', [
            'booking_mode' => 'offline',
        ], withMeeting: false);
        $userOnlySession->pandit->update(['email' => null]);

        $noRecipientSession = $this->createSession('pooja', [
            'user_id' => null,
            'booking_mode' => 'offline',
        ], withMeeting: false);
        $noRecipientSession->pandit->update(['email' => null]);

        $this->artisan('bookings:send-today-reminders')
            ->expectsOutput('Today booking reminder emails sent: 2')
            ->assertSuccessful();

        Mail::assertSent(BookingTodayReminderMail::class, 2);
        Mail::assertSent(BookingTodayReminderMail::class, fn (BookingTodayReminderMail $mail) => $mail->hasTo($panditOnlySession->pandit->email));
        Mail::assertSent(BookingTodayReminderMail::class, fn (BookingTodayReminderMail $mail) => $mail->hasTo($userOnlySession->user->email));
        $this->assertDatabaseCount('notifications', 2);
    }

    private function createSession(string $type, array $overrides = [], bool $withMeeting = false): Model
    {
        $user = User::factory()->create();
        $pandit = Pandit::create([
            'full_name' => 'Reminder Pandit',
            'pandit_name' => 'Reminder Pandit',
            'email' => fake()->unique()->safeEmail(),
            'status' => 'verified',
        ]);

        $model = $type === 'pooja' ? PoojaSession::class : HawanSession::class;
        $data = [
            'user_id' => $user->id,
            'service_type' => $type,
            'booking_mode' => 'online',
            'pandit_id' => $pandit->id,
            'booking_date' => now('Asia/Kolkata')->toDateString(),
            'slot' => '11:00 AM - 12:00 PM',
            'slot_start_time' => '11:00:00',
            'slot_end_time' => '12:00:00',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'admin_note' => json_encode([$type.'_name' => 'Reminder '.ucfirst($type)]),
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

        $session = $model::create(array_merge($data, $overrides));

        if ($withMeeting) {
            $session->videoMeeting()->create([
                'provider' => 'fake',
                'external_meeting_id' => 'reminder-'.$type.'-'.$session->id,
                'join_url' => 'https://meet.example.test/join/'.$session->id,
                'host_url' => 'https://meet.example.test/start/'.$session->id,
                'passcode' => '123456',
                'status' => 'scheduled',
                'starts_at' => now('Asia/Kolkata')->setTime(11, 0),
                'duration_minutes' => 60,
                'pandit_id' => $pandit->id,
            ]);
        }

        return $session->fresh(['user', 'pandit', 'videoMeeting']);
    }
}

class TodayReminderFakeVideoMeetingProvider implements VideoMeetingProvider
{
    public function providerName(): string
    {
        return 'fake';
    }

    public function createMeeting(Pandit $pandit, string $topic, CarbonInterface|string $startTime, int $duration): array
    {
        return [
            'provider' => 'fake',
            'external_meeting_id' => 'same-day-meeting',
            'join_url' => 'https://meet.example.test/join/same-day-meeting',
            'host_url' => 'https://meet.example.test/start/same-day-meeting',
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
