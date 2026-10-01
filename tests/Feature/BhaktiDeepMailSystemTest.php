<?php

namespace Tests\Feature;

use App\Mail\BookingLifecycleMail;
use App\Mail\BookingRefundMail;
use App\Mail\PaymentSuccessfulMail;
use App\Models\Admin\Donation;
use App\Models\Admin\HawanSession;
use App\Models\Admin\NotificationLog;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class BhaktiDeepMailSystemTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.razorpay.key_id' => 'rzp_test_key',
            'services.razorpay.key_secret' => 'rzp_test_secret',
        ]);

        Mail::fake();
        Http::preventStrayRequests();
    }

    public function test_login_otp_has_correct_recipient_subject_and_content(): void
    {
        $user = User::factory()->create([
            'name' => 'Login Devotee',
            'email' => 'login-otp@example.test',
        ]);

        $this->expectRawOtp(
            $user->email,
            'Your BhaktiDeep login OTP',
            'login'
        );

        $this->post(route('login.send-otp'), ['email' => strtoupper($user->email)])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'OTP sent to your Gmail address.');

        $this->assertSame($user->email, session('otp_flow.email'));
        $this->assertSame('login', session('otp_flow.context'));
    }

    #[DataProvider('accountTypeProvider')]
    public function test_registration_otp_has_correct_recipient_subject_and_content(string $accountType): void
    {
        $email = $accountType.'-registration-otp@example.test';

        $this->expectRawOtp(
            $email,
            'Your BhaktiDeep registration OTP',
            'register'
        );

        $this->post(route('register.send-otp', ['type' => $accountType]), [
            'full_name' => 'Registration Devotee',
            'mobile' => $accountType === 'user' ? '9000000001' : '9000000002',
            'email' => $email,
            'terms' => '1',
        ])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'OTP sent to your Gmail address.');

        $this->assertSame($email, session('otp_flow.email'));
        $this->assertSame($accountType, session('otp_flow.type'));
    }

    public static function accountTypeProvider(): array
    {
        return [
            'user registration' => ['user'],
            'pandit registration' => ['pandit'],
        ];
    }

    #[DataProvider('ritualTypeProvider')]
    public function test_review_otp_has_correct_recipient_subject_and_content(string $type): void
    {
        $user = User::factory()->create([
            'name' => 'Review Devotee',
            'email' => $type.'-review-otp@example.test',
        ]);
        $session = $this->createSession($type, 'offline', [
            'user_id' => $user->id,
            'status' => 'completed',
            'payment_status' => 'paid',
            'completed_at' => now(),
        ]);

        $this->expectRawOtp(
            $user->email,
            'BhaktiDeep review verification OTP',
            'review_submission'
        );

        $this->actingAs($user)
            ->post(route('reviews.image-otp'), [
                'booking_type' => $type,
                'booking_id' => $session->id,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('review_success');

        $this->assertDatabaseHas('review_image_otps', [
            'user_type' => 'user',
            'user_id' => $user->id,
            'email' => $user->email,
            'purpose' => 'review_submission',
        ]);
    }

    #[DataProvider('liveBookingProvider')]
    public function test_successful_payment_mails_user_and_new_booking_mails_pandit_once(string $type, string $mode): void
    {
        [$session, $attempt] = $this->pendingPayment($type, $mode);

        $payload = $this->paymentPayload($attempt);

        $this->actingAs($session->user)
            ->postJson(route('payments.razorpay.verify'), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('paid', $session->fresh()->payment_status);
        $this->assertSame('scheduled', $session->fresh()->status);

        Mail::assertSent(PaymentSuccessfulMail::class, function (PaymentSuccessfulMail $mail) use ($session, $type, $mode) {
            if (! $mail->hasTo($session->user->email)) {
                return false;
            }

            $html = $mail->render();

            $this->assertSame(
                'Payment successful - Mail Test '.ucfirst($type).' | BhaktiDeep',
                $mail->subject
            );
            $this->assertStringContainsString('Mail Test '.ucfirst($type), $html);
            $this->assertStringContainsString(ucfirst($mode), $html);
            $this->assertStringContainsString('₹1,100.00', $html);

            return true;
        });

        Mail::assertSent(BookingLifecycleMail::class, function (BookingLifecycleMail $mail) use ($session, $type, $mode) {
            if (! $mail->hasTo($session->pandit->email)) {
                return false;
            }

            $html = $mail->render();

            $this->assertSame(
                'New paid '.ucfirst($type).' booking - Mail Test '.ucfirst($type).' | BhaktiDeep',
                $mail->subject
            );
            $this->assertStringContainsString('NEW PAID BOOKING', $html);
            $this->assertStringContainsString(ucfirst($mode), $html);

            return true;
        });

        $this->actingAs($session->user)
            ->postJson(route('payments.razorpay.verify'), $payload)
            ->assertOk();

        Mail::assertSent(PaymentSuccessfulMail::class, 1);
        Mail::assertSent(BookingLifecycleMail::class, 1);
        Mail::assertSentCount(2);
    }

    public static function liveBookingProvider(): array
    {
        return [
            'online Pooja' => ['pooja', 'online'],
            'offline Pooja' => ['pooja', 'offline'],
            'online Hawan' => ['hawan', 'online'],
            'offline Hawan' => ['hawan', 'offline'],
        ];
    }

    public static function ritualTypeProvider(): array
    {
        return [
            'Pooja' => ['pooja'],
            'Hawan' => ['hawan'],
        ];
    }

    public function test_digital_pooja_payment_mails_user_but_not_pandit(): void
    {
        [$session, $attempt] = $this->pendingPayment('pooja', null, digital: true);

        $this->actingAs($session->user)
            ->postJson(route('payments.razorpay.verify'), $this->paymentPayload($attempt))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('active', $session->fresh()->status);
        $this->assertSame('paid', $session->fresh()->payment_status);

        Mail::assertSent(PaymentSuccessfulMail::class, function (PaymentSuccessfulMail $mail) use ($session) {
            $html = $mail->render();

            return $mail->hasTo($session->user->email)
                && $mail->details['mode_label'] === 'Digital Pooja'
                && str_contains($html, 'Open Digital Pooja');
        });
        Mail::assertNotSent(BookingLifecycleMail::class);
        Mail::assertSentCount(1);
    }

    public function test_invalid_and_failed_payments_do_not_send_payment_or_pandit_mail(): void
    {
        [$invalidSession, $invalidAttempt] = $this->pendingPayment('pooja', 'online');
        $invalidPayload = $this->paymentPayload($invalidAttempt);
        $invalidPayload['razorpay_signature'] = 'invalid-signature';

        $this->actingAs($invalidSession->user)
            ->postJson(route('payments.razorpay.verify'), $invalidPayload)
            ->assertUnprocessable();

        [$failedSession, $failedAttempt] = $this->pendingPayment('hawan', 'offline');

        $this->actingAs($failedSession->user)
            ->postJson(route('payments.razorpay.failure'), [
                'payment_attempt_id' => $failedAttempt->id,
                'razorpay_order_id' => $failedAttempt->gateway_order_id,
                'error' => ['description' => 'Card declined'],
            ])
            ->assertOk();

        $this->assertSame(PaymentAttempt::STATUS_FAILED, $invalidAttempt->fresh()->status);
        $this->assertSame(PaymentAttempt::STATUS_FAILED, $failedAttempt->fresh()->status);
        $this->assertSame('pending', $invalidSession->fresh()->payment_status);
        $this->assertSame('pending', $failedSession->fresh()->payment_status);
        Mail::assertNothingSent();
    }

    public function test_mail_failure_does_not_break_successful_payment_or_new_booking_flow(): void
    {
        [$session, $attempt] = $this->pendingPayment('hawan', 'offline');

        Mail::shouldReceive('to')
            ->twice()
            ->andThrow(new RuntimeException('Simulated SMTP failure'));

        $this->actingAs($session->user)
            ->postJson(route('payments.razorpay.verify'), $this->paymentPayload($attempt))
            ->assertOk()
            ->assertJson(['success' => true]);

        $session->refresh();
        $this->assertSame('paid', $session->payment_status);
        $this->assertSame('scheduled', $session->status);
        $this->assertSame(PaymentAttempt::STATUS_PAID, $attempt->fresh()->status);
        $this->assertSame(2, NotificationLog::where('delivery_status', 'failed')->count());
    }

    public function test_cancel_refund_mails_user_with_correct_subject_content_and_no_duplicate(): void
    {
        [$pandit, $session] = $this->paidCancellationBooking('pooja', 'offline');

        $this->fakeProcessedRefund($session->latestPaymentAttempt);

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.cancel', ['type' => 'pooja', 'id' => $session->id]), [
                'reason' => 'Unable to attend this booking.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success')
            ->assertSessionHas('info');

        Mail::assertSent(BookingRefundMail::class, function (BookingRefundMail $mail) use ($session) {
            $html = $mail->render();

            return $mail->hasTo($session->user->email)
                && $mail->subject === 'BhaktiDeep booking refund update'
                && $mail->refundMessage === 'Refund Processed ₹1,100'
                && str_contains($html, 'Refund Processed ₹1,100');
        });
        Mail::assertSent(BookingRefundMail::class, function (BookingRefundMail $mail) use ($session) {
            $html = $mail->render();

            return $mail->hasTo($session->user->email)
                && $mail->subject === 'BhaktiDeep booking refund update'
                && $mail->refundMessage === 'Refund of ₹1,100 initiated.'
                && str_contains($html, 'Refund of ₹1,100 initiated.');
        });
        Mail::assertSent(BookingRefundMail::class, 2);

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.cancel', ['type' => 'pooja', 'id' => $session->id]), [
                'reason' => 'Duplicate cancellation attempt.',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('booking');

        Mail::assertSent(BookingRefundMail::class, 2);
        $this->assertDatabaseCount('payment_refunds', 1);
    }

    public function test_mail_failure_does_not_break_cancel_refund_flow(): void
    {
        [$pandit, $session] = $this->paidCancellationBooking('hawan', 'online');

        $this->fakeProcessedRefund($session->latestPaymentAttempt);
        Mail::shouldReceive('to')
            ->twice()
            ->andThrow(new RuntimeException('Simulated SMTP failure'));

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.cancel', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'Emergency cancellation.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success')
            ->assertSessionHas('info');

        $session->refresh();
        $this->assertSame('cancelled_by_pandit', $session->status);
        $this->assertSame('refunded', $session->payment_status);
        $this->assertDatabaseCount('payment_refunds', 1);
        $this->assertSame(2, NotificationLog::where('delivery_status', 'failed')->count());
    }

    private function expectRawOtp(string $recipient, string $subject, string $context): void
    {
        Mail::shouldReceive('send')
            ->once()
            ->withArgs(function ($view, array $data, $callback) use ($recipient, $subject, $context) {
                $this->assertSame('emails.otp', $view);
                $this->assertSame($subject, $data['subject']);
                $this->assertSame($context, $data['context']);
                $this->assertMatchesRegularExpression('/^\d{6}$/', $data['otp']);

                $html = view($view, $data)->render();
                $this->assertStringContainsString($data['otp'], $html);
                $this->assertStringContainsString($subject, $html);

                $message = Mockery::mock(Message::class);
                $message->shouldReceive('to')->once()->with($recipient)->andReturnSelf();
                $message->shouldReceive('subject')->once()->with($subject)->andReturnSelf();
                $callback($message);

                return true;
            });
    }

    private function pendingPayment(string $type, ?string $mode, bool $digital = false): array
    {
        $overrides = [
            'status' => 'pending',
            'payment_status' => 'pending',
            'booking_date' => $digital ? null : now('Asia/Kolkata')->addDay()->toDateString(),
            'slot' => $digital ? null : '11:00 AM - 12:00 PM',
            'slot_start_time' => $digital ? null : '11:00:00',
            'slot_end_time' => $digital ? null : '12:00:00',
        ];

        if ($type === 'pooja') {
            $overrides += [
                'pooja_type' => $digital ? 'digital' : 'live',
                'pooja_type_title' => $digital ? 'Digital Pooja' : 'Live Pooja',
                'digital_access_minutes' => $digital ? 120 : null,
            ];
        }

        $session = $this->createSession($type, $mode, $overrides, assignPandit: ! $digital);

        $attempt = PaymentAttempt::create([
            'user_id' => $session->user_id,
            'payable_type' => $session::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'gateway' => 'razorpay_test',
            'gateway_order_id' => 'order_mail_system_'.$session::class.'_'.$session->id,
            'amount' => 1100,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PENDING,
            'metadata' => [
                $type.'_name' => 'Mail Test '.ucfirst($type),
                'package_amount' => 1000,
                'donation_amount' => 100,
                'total_amount' => 1100,
            ],
        ]);

        $session->update(['latest_payment_attempt_id' => $attempt->id]);

        return [$session->fresh(['user', 'pandit']), $attempt->fresh()];
    }

    private function paymentPayload(PaymentAttempt $attempt): array
    {
        $paymentId = 'pay_mail_system_'.$attempt->id;

        return [
            'payment_attempt_id' => $attempt->id,
            'razorpay_order_id' => $attempt->gateway_order_id,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => hash_hmac(
                'sha256',
                $attempt->gateway_order_id.'|'.$paymentId,
                'rzp_test_secret'
            ),
        ];
    }

    private function paidCancellationBooking(string $type, string $mode): array
    {
        $session = $this->createSession($type, $mode, [
            'status' => 'scheduled',
            'payment_status' => 'paid',
        ]);

        $donation = Donation::create([
            'user_id' => $session->user_id,
            'payment_purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'session_type' => $session::class,
            'session_id' => $session->id,
            'amount' => 1100,
            'currency' => 'INR',
            'payment_status' => 'paid',
            'razorpay_order_id' => 'order_cancel_'.$session->id,
            'razorpay_payment_id' => 'pay_cancel_'.$session->id,
            'paid_at' => now(),
        ]);
        $attempt = PaymentAttempt::create([
            'user_id' => $session->user_id,
            'donation_id' => $donation->id,
            'payable_type' => $session::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'gateway' => 'razorpay_test',
            'gateway_order_id' => $donation->razorpay_order_id,
            'gateway_payment_id' => $donation->razorpay_payment_id,
            'amount' => 1100,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
            'metadata' => [
                $type.'_name' => 'Mail Test '.ucfirst($type),
                'total_amount' => 1100,
            ],
        ]);

        $donation->update(['latest_payment_attempt_id' => $attempt->id]);
        $session->update(['latest_payment_attempt_id' => $attempt->id]);

        return [$session->pandit, $session->fresh(['user', 'pandit', 'latestPaymentAttempt'])];
    }

    private function fakeProcessedRefund(PaymentAttempt $attempt): void
    {
        Http::fake([
            'https://api.razorpay.com/v1/payments/'.$attempt->gateway_payment_id.'/refund' => Http::response([
                'id' => 'rfnd_mail_system_'.$attempt->id,
                'amount' => 110000,
                'currency' => 'INR',
                'payment_id' => $attempt->gateway_payment_id,
                'status' => 'processed',
            ]),
        ]);
    }

    private function createSession(
        string $type,
        ?string $mode,
        array $overrides = [],
        bool $assignPandit = true
    ): Model {
        $this->sequence++;
        $user = User::factory()->create([
            'name' => 'Mail Devotee '.$this->sequence,
            'email' => 'mail-devotee-'.$this->sequence.'@example.test',
        ]);
        $pandit = $assignPandit
            ? Pandit::create([
                'full_name' => 'Mail Pandit '.$this->sequence,
                'pandit_name' => 'Mail Pandit '.$this->sequence,
                'email' => 'mail-pandit-'.$this->sequence.'@example.test',
                'status' => 'verified',
            ])
            : null;

        $model = $type === 'pooja' ? PoojaSession::class : HawanSession::class;
        $data = [
            'user_id' => $user->id,
            'service_type' => $type,
            'booking_mode' => $mode,
            'pandit_id' => $pandit?->id,
            'booking_date' => now('Asia/Kolkata')->addDay()->toDateString(),
            'slot' => '11:00 AM - 12:00 PM',
            'slot_start_time' => '11:00:00',
            'slot_end_time' => '12:00:00',
            'state' => $mode === 'offline' ? 'Punjab' : null,
            'city' => $mode === 'offline' ? 'Ludhiana' : null,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'admin_note' => json_encode([
                $type.'_name' => 'Mail Test '.ucfirst($type),
                'total_amount' => 1100,
            ]),
        ];

        if ($type === 'pooja') {
            $data += [
                'pooja_type' => 'live',
                'pooja_type_title' => 'Live Pooja',
                'pooja_type_price' => 1000,
            ];
        } else {
            $data += [
                'hawan_type' => 'special',
                'hawan_type_title' => 'Special Hawan',
                'hawan_type_price' => 1000,
            ];
        }

        return $model::create(array_merge($data, $overrides))->fresh(['user', 'pandit']);
    }
}
