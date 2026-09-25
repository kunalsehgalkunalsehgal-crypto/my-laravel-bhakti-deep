<?php

namespace Tests\Feature;

use App\Jobs\ProcessPanditPayout;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PlatformSetting;
use App\Models\BookingUserConfirmation;
use App\Models\Dispute;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditBankDetail;
use App\Models\Pandit\PanditService;
use App\Models\PanditPayout;
use App\Models\PaymentAttempt;
use App\Models\SessionCompletionProof;
use App\Models\User;
use App\Services\PanditBookingService;
use App\Services\PanditPayoutLedgerService;
use App\Services\RazorpayRoutePayoutService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RazorpayRoutePayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.razorpay.key_id' => 'rzp_test_route_key',
            'services.razorpay.key_secret' => 'route_secret',
            'services.razorpay.webhook_secret' => 'route_webhook_secret',
            'services.payouts.mode' => 'route',
            'services.payouts.route_enabled' => true,
        ]);
    }

    public function test_route_transfer_success_marks_processed_transfer_paid(): void
    {
        $payout = $this->readyPayout();
        Http::fake([
            'https://api.razorpay.com/v1/payments/*/transfers' => Http::response([
                'entity' => 'collection',
                'count' => 1,
                'items' => [[
                    'id' => 'trf_success_123',
                    'status' => 'processed',
                    'recipient' => 'acc_route_ready',
                    'amount' => 470100,
                    'currency' => 'INR',
                ]],
            ]),
        ]);

        (new ProcessPanditPayout($payout->id))->handle(app(RazorpayRoutePayoutService::class));

        $payout->refresh();
        $this->assertSame(PanditPayout::STATUS_PAID, $payout->status);
        $this->assertSame('trf_success_123', $payout->razorpay_transfer_id);
        $this->assertNotNull($payout->paid_at);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'https://api.razorpay.com/v1/payments/pay_route_123/transfers'
            && $request['transfers'][0]['account'] === 'acc_route_ready'
            && $request['transfers'][0]['amount'] === 470100
            && $request['transfers'][0]['notes']['bhaktideep_payout_uuid'] === $payout->uuid
        );
    }

    public function test_route_transfer_failure_is_recorded(): void
    {
        $payout = $this->readyPayout();
        Http::fake([
            'https://api.razorpay.com/v1/payments/*/transfers' => Http::response([
                'error' => ['description' => 'Linked account is not eligible for transfers.'],
            ], 400),
        ]);

        (new ProcessPanditPayout($payout->id))->handle(app(RazorpayRoutePayoutService::class));

        $payout->refresh();
        $this->assertSame(PanditPayout::STATUS_FAILED, $payout->status);
        $this->assertSame('Linked account is not eligible for transfers.', $payout->last_error);
        $this->assertNotNull($payout->failed_at);
    }

    public function test_transfer_id_prevents_duplicate_route_transfer(): void
    {
        $payout = $this->readyPayout();
        Http::fake([
            'https://api.razorpay.com/v1/payments/*/transfers' => Http::response([
                'items' => [[
                    'id' => 'trf_pending_123',
                    'status' => 'pending',
                    'recipient' => 'acc_route_ready',
                    'amount' => 470100,
                ]],
            ]),
        ]);

        (new ProcessPanditPayout($payout->id))->handle(app(RazorpayRoutePayoutService::class));
        (new ProcessPanditPayout($payout->id))->handle(app(RazorpayRoutePayoutService::class));

        $this->assertSame(PanditPayout::STATUS_PROCESSING, $payout->fresh()->status);
        $this->assertSame('trf_pending_123', $payout->fresh()->razorpay_transfer_id);
        Http::assertSentCount(1);
    }

    public function test_retry_reconciles_existing_transfer_before_posting_again(): void
    {
        $payout = $this->readyPayout();
        $payout->update([
            'status' => PanditPayout::STATUS_FAILED,
            'transfer_attempts' => 1,
            'failed_at' => now(),
        ]);
        Http::fake([
            'https://api.razorpay.com/v1/payments/*/transfers' => Http::response([
                'items' => [[
                    'id' => 'trf_reconciled_123',
                    'status' => 'processed',
                    'recipient' => 'acc_route_ready',
                    'amount' => 470100,
                    'notes' => ['bhaktideep_payout_uuid' => $payout->uuid],
                ]],
            ]),
        ]);

        (new ProcessPanditPayout($payout->id))->handle(app(RazorpayRoutePayoutService::class));

        $this->assertSame(PanditPayout::STATUS_PAID, $payout->fresh()->status);
        $this->assertSame('trf_reconciled_123', $payout->fresh()->razorpay_transfer_id);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->method() === 'GET');
    }

    public function test_linked_account_is_not_ready_until_route_and_bank_are_activated(): void
    {
        [$user, $pandit] = $this->bookingBase();
        $bank = PanditBankDetail::create([
            'pandit_id' => $pandit->id,
            'account_holder_name' => 'Route Pandit',
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
            'ifsc_code' => 'HDFC0001234',
            'pan_number' => 'ABCDE1234F',
        ]);
        config(['services.razorpay.route.business_type' => 'individual']);
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST') {
                return Http::response([
                    'id' => 'acc_activation_test',
                    'status' => 'created',
                    'product_config' => [
                        'id' => 'acc_prd_activation_test',
                        'activation_status' => 'activated',
                        'active_configuration' => [
                            'settlement_accounts' => [[
                                'id' => 'sa_activation_test',
                                'is_default' => true,
                                'active' => true,
                                'verification_status' => 'pending',
                            ]],
                        ],
                    ],
                ]);
            }

            return Http::response([
                'id' => 'acc_activation_test',
                'status' => 'created',
                'product_config' => [
                    'id' => 'acc_prd_activation_test',
                    'activation_status' => 'activated',
                    'active_configuration' => [
                        'settlement_accounts' => [[
                            'id' => 'sa_activation_test',
                            'is_default' => true,
                            'active' => true,
                            'verification_status' => 'verified',
                        ]],
                    ],
                ],
            ]);
        });

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bank-details.razorpay-linked-account'))
            ->assertRedirect();

        $bank->refresh();
        $this->assertSame('acc_activation_test', $bank->razorpay_linked_account_id);
        $this->assertFalse($bank->isRazorpayPayoutReady());
        $this->assertSame('pending', $bank->razorpay_bank_verification_status);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request['settlement_accounts'][0]['bank_account']['account_number'] === '1234567890'
            && filled($request->header('Idempotency-Key')[0] ?? null)
        );

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bank-details.razorpay-linked-account.sync'))
            ->assertRedirect();

        $this->assertTrue($bank->fresh()->isRazorpayPayoutReady());
        $this->assertNotNull($bank->fresh()->razorpay_verified_at);
    }

    public function test_transfer_webhooks_mark_processing_payout_paid_or_failed(): void
    {
        $paidPayout = $this->readyPayout();
        $paidPayout->update([
            'status' => PanditPayout::STATUS_PROCESSING,
            'razorpay_transfer_id' => 'trf_webhook_paid',
            'provider_payout_id' => 'trf_webhook_paid',
        ]);
        $this->sendTransferWebhook('transfer.processed', [
            'id' => 'trf_webhook_paid',
            'status' => 'processed',
            'notes' => ['bhaktideep_payout_uuid' => $paidPayout->uuid],
        ])->assertOk();
        $this->assertSame(PanditPayout::STATUS_PAID, $paidPayout->fresh()->status);

        $failedPayout = $this->readyPayout('pay_route_failed');
        $failedPayout->update([
            'status' => PanditPayout::STATUS_PROCESSING,
            'razorpay_transfer_id' => 'trf_webhook_failed',
            'provider_payout_id' => 'trf_webhook_failed',
        ]);
        $this->sendTransferWebhook('transfer.failed', [
            'id' => 'trf_webhook_failed',
            'status' => 'failed',
            'notes' => ['bhaktideep_payout_uuid' => $failedPayout->uuid],
            'error' => ['description' => 'Transfer balance unavailable.'],
        ])->assertOk();
        $this->assertSame(PanditPayout::STATUS_FAILED, $failedPayout->fresh()->status);
        $this->assertSame('Transfer balance unavailable.', $failedPayout->fresh()->last_error);
    }

    public function test_customer_confirmation_uses_central_ready_trigger(): void
    {
        Queue::fake();
        [$user, $session] = $this->completedBooking();

        $this->actingAs($user)
            ->post(route('live.completion.confirm', ['type' => 'hawan', 'id' => $session->id]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(PanditPayout::STATUS_READY, PanditPayout::firstOrFail()->status);
        Queue::assertPushed(ProcessPanditPayout::class, 1);
    }

    public function test_auto_confirmation_uses_central_ready_trigger(): void
    {
        Queue::fake();
        [$user, $session] = $this->completedBooking();
        BookingUserConfirmation::create([
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'user_id' => $user->id,
            'status' => BookingUserConfirmation::STATUS_PENDING,
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('bookings:auto-confirm-completions')->assertExitCode(0);

        $this->assertSame(BookingUserConfirmation::STATUS_AUTO_CONFIRMED, BookingUserConfirmation::firstOrFail()->status);
        $this->assertSame(PanditPayout::STATUS_READY, PanditPayout::firstOrFail()->status);
        Queue::assertPushed(ProcessPanditPayout::class, 1);
    }

    public function test_pandit_favour_dispute_uses_central_ready_trigger(): void
    {
        Queue::fake();
        [$user, $session] = $this->completedBooking();
        $admin = $this->admin();
        $dispute = $session->disputes()->create([
            'user_id' => $user->id,
            'pandit_id' => $session->pandit_id,
            'reason' => 'session_incomplete',
            'description' => 'Please review the completion.',
            'status' => Dispute::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.disputes.resolve-pandit-favour', $dispute))
            ->assertRedirect();

        $this->assertSame(PanditPayout::STATUS_READY, PanditPayout::firstOrFail()->status);
        Queue::assertPushed(ProcessPanditPayout::class, 1);
    }

    public function test_user_favour_refund_cancels_payout_without_route_transfer(): void
    {
        Queue::fake();
        Http::fake([
            'https://api.razorpay.com/v1/payments/*/refund' => Http::response([
                'id' => 'rfnd_route_user',
                'status' => 'processed',
            ]),
        ]);
        [$user, $session] = $this->completedBooking();
        $admin = $this->admin();
        $dispute = $session->disputes()->create([
            'user_id' => $user->id,
            'pandit_id' => $session->pandit_id,
            'reason' => 'session_incomplete',
            'description' => 'Refund requested.',
            'status' => Dispute::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.disputes.refund-user', $dispute))
            ->assertRedirect();

        $payout = PanditPayout::firstOrFail();
        $this->assertSame(PanditPayout::STATUS_CANCELLED, $payout->status);
        $this->assertSame(4601.0, (float) $payout->pandit_amount);
        $this->assertSame(0.0, (float) $payout->payout_amount);
        Queue::assertNotPushed(ProcessPanditPayout::class);
    }

    public function test_route_disabled_stops_at_ready(): void
    {
        Queue::fake();
        config(['services.payouts.route_enabled' => false]);
        [$user, $session] = $this->completedBooking();

        $this->actingAs($user)
            ->post(route('live.completion.confirm', ['type' => 'hawan', 'id' => $session->id]))
            ->assertRedirect();

        $this->assertSame(PanditPayout::STATUS_READY, PanditPayout::firstOrFail()->status);
        Queue::assertNotPushed(ProcessPanditPayout::class);
    }

    public function test_manual_mode_stops_at_ready_even_when_route_feature_is_enabled(): void
    {
        Queue::fake();
        config([
            'services.payouts.mode' => 'manual',
            'services.payouts.route_enabled' => true,
        ]);
        [$user, $session] = $this->completedBooking();

        $this->actingAs($user)
            ->post(route('live.completion.confirm', ['type' => 'hawan', 'id' => $session->id]))
            ->assertRedirect();

        $this->assertSame(PanditPayout::STATUS_READY, PanditPayout::firstOrFail()->status);
        Queue::assertNotPushed(ProcessPanditPayout::class);
    }

    public function test_commission_is_frozen_at_payment_time_and_excludes_dakshina(): void
    {
        PlatformSetting::create([
            'key' => 'payout_platform_commission_percent',
            'value' => '10',
            'type' => 'number',
            'group' => 'payouts',
        ]);
        [$user, $pandit, $session] = $this->bookingBase();

        $attempt = app(PanditBookingService::class)->createPendingPayment($session, 1200, [
            'package_amount' => 1000,
            'dakshina' => 200,
        ]);
        PlatformSetting::where('key', 'payout_platform_commission_percent')->update(['value' => '50']);
        $attempt->update([
            'gateway_payment_id' => 'pay_commission_frozen',
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
        ]);
        $session->update(['payment_status' => 'paid', 'latest_payment_attempt_id' => $attempt->id]);

        app(PanditPayoutLedgerService::class)->holdForSuccessfulPayment($session->fresh(), $attempt->fresh());

        $payout = PanditPayout::firstOrFail();
        $this->assertSame(1000.0, (float) $payout->service_amount);
        $this->assertSame(200.0, (float) $payout->dakshina_amount);
        $this->assertSame(100.0, (float) $payout->platform_amount);
        $this->assertSame(1100.0, (float) $payout->pandit_amount);
        $this->assertSame(10.0, (float) $payout->commission_percent);
        $this->assertTrue($attempt->fresh()->metadata['financial_snapshot_frozen']);
    }

    private function readyPayout(string $paymentId = 'pay_route_123'): PanditPayout
    {
        [$user, $pandit, $session] = $this->bookingBase();
        PanditBankDetail::create([
            'pandit_id' => $pandit->id,
            'account_holder_name' => $pandit->full_name,
            'account_number' => '1234567890',
            'ifsc_code' => 'HDFC0001234',
            'razorpay_linked_account_id' => 'acc_route_ready',
            'razorpay_linked_account_status' => 'activated',
            'razorpay_bank_verification_status' => 'verified',
            'razorpay_payout_enabled' => true,
            'razorpay_verified_at' => now(),
        ]);
        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'payable_type' => HawanSession::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'gateway' => 'razorpay_test',
            'gateway_order_id' => 'order_'.$paymentId,
            'gateway_payment_id' => $paymentId,
            'amount' => 5101,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
        ]);
        $session->update(['payment_status' => 'paid', 'latest_payment_attempt_id' => $attempt->id]);

        return PanditPayout::create([
            'pandit_id' => $pandit->id,
            'payment_attempt_id' => $attempt->id,
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'payout_type' => PanditPayout::TYPE_BOOKING,
            'service_amount' => 5001,
            'booking_amount' => 5101,
            'pandit_amount' => 4701,
            'platform_amount' => 400,
            'commission_percent' => 8,
            'gross_amount' => 5101,
            'platform_fee' => 400,
            'dakshina_amount' => 100,
            'payout_amount' => 4701,
            'currency' => 'INR',
            'status' => PanditPayout::STATUS_READY,
            'eligible_at' => now(),
            'approved_at' => now(),
        ]);
    }

    private function completedBooking(): array
    {
        [$user, $pandit, $session] = $this->bookingBase();
        $session->update([
            'status' => 'completed',
            'payment_status' => 'paid',
            'completed_at' => now(),
        ]);
        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'payable_type' => HawanSession::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'gateway' => 'razorpay_test',
            'gateway_order_id' => 'order_completed_'.$session->id,
            'gateway_payment_id' => 'pay_completed_'.$session->id,
            'amount' => 4601,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
            'metadata' => ['package_amount' => 4601, 'dakshina' => 0],
        ]);
        $session->update(['latest_payment_attempt_id' => $attempt->id]);
        SessionCompletionProof::create([
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'pandit_id' => $pandit->id,
            'user_id' => $user->id,
            'file_path' => 'completion-proofs/route-test.jpg',
            'status' => SessionCompletionProof::STATUS_PENDING,
            'submitted_at' => now(),
        ]);
        app(PanditPayoutLedgerService::class)->holdForSuccessfulPayment($session->fresh(), $attempt);

        return [$user, $session->fresh()];
    }

    private function bookingBase(): array
    {
        $user = User::create([
            'name' => 'Route User',
            'email' => uniqid('route-user').'@example.test',
            'password' => Hash::make('password'),
        ]);
        $pandit = Pandit::create([
            'full_name' => 'Route Pandit',
            'pandit_name' => 'Route Pandit',
            'email' => uniqid('route-pandit').'@example.test',
            'mobile' => '9876543210',
            'status' => 'verified',
        ]);
        $hawan = Hawan::create([
            'name' => 'Route Hawan',
            'slug' => uniqid('route-hawan-'),
            'base_price' => 4601,
            'status' => 'active',
        ]);
        $service = PanditService::create([
            'pandit_id' => $pandit->id,
            'service_type' => 'hawan',
            'service_name' => $hawan->name,
            'hawan_id' => $hawan->id,
            'status' => 'approved',
        ]);
        $session = HawanSession::create([
            'user_id' => $user->id,
            'service_type' => 'hawan',
            'booking_mode' => 'offline',
            'ritual_id' => $hawan->id,
            'ritual_slug' => $hawan->slug,
            'hawan_type' => 'special',
            'hawan_type_title' => 'Special Hawan',
            'hawan_type_price' => 4601,
            'pandit_id' => $pandit->id,
            'pandit_service_id' => $service->id,
            'booking_date' => Carbon::tomorrow('Asia/Kolkata')->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'slot_start_time' => '07:00:00',
            'slot_end_time' => '08:00:00',
            'status' => 'scheduled',
            'payment_status' => 'pending',
            'admin_note' => json_encode(['hawan_name' => $hawan->name, 'package_amount' => 4601, 'dakshina' => 0, 'total_amount' => 4601]),
        ]);

        return [$user, $pandit, $session];
    }

    private function admin(): Admin
    {
        $role = AdminRole::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'status' => 'active']
        );

        return Admin::create([
            'name' => 'Route Admin',
            'email' => uniqid('route-admin').'@example.test',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }

    private function sendTransferWebhook(string $event, array $transfer)
    {
        $payload = json_encode([
            'event' => $event,
            'payload' => ['transfer' => ['entity' => $transfer]],
        ], JSON_UNESCAPED_SLASHES);

        return $this->call(
            'POST',
            route('payments.razorpay.webhook'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => hash_hmac('sha256', $payload, 'route_webhook_secret'),
            ],
            $payload
        );
    }
}
