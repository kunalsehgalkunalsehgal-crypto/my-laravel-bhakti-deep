<?php

namespace App\Http\Controllers;

use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\Admin\DiyaSession;
use App\Models\Admin\Donation;
use App\Models\Admin\PaymentLog;
use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\PaymentAttempt;
use App\Services\PanditBookingService;
use App\Services\PanditPayoutLedgerService;
use App\Services\RazorpayPaymentService;
use App\Services\UserBookingNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function retry(Request $request, string $type, string $id): JsonResponse
    {
        $attempt = DB::transaction(function () use ($request, $type, $id) {
            $session = $this->sessionQuery($type)
                ->whereKey($id)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->payment_status === 'paid' || $this->bookingClosedForPayment($session)) {
                throw ValidationException::withMessages(['payment' => 'This booking cannot be paid again.']);
            }

            if ($type === 'diya') {
                $this->cancelOpenAttempts($session);

                return $this->createDiyaPaymentAttempt($session->fresh(['diya', 'sankalp', 'latestPaymentAttempt']));
            }

            if (!$session->payment_hold_expires_at || $session->payment_hold_expires_at->lte(now())) {
                $this->refreshHoldIfAvailable($session, $type);
            }

            $this->cancelOpenAttempts($session);

            return app(PanditBookingService::class)->createPendingPayment(
                $session->fresh(),
                $this->amount($session),
                $this->meta($session, $type)
            );
        });

        $payment = app(RazorpayPaymentService::class)->createOrder($attempt, $attempt->payable, $request->user());

        return response()->json(['success' => true, 'payment' => $payment]);
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_attempt_id' => ['required', 'integer'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        return DB::transaction(function () use ($request, $data) {
            $attempt = PaymentAttempt::with('donation')
                ->whereKey($data['payment_attempt_id'])
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            $session = $this->lockedPayable($attempt);

            if ($attempt->status === PaymentAttempt::STATUS_PAID) {
                return response()->json(['success' => true, 'redirect_url' => route('user.profile')]);
            }

            if ($session->payment_status === 'paid') {
                $this->cancelAttempt($attempt);
                return response()->json(['success' => true, 'redirect_url' => route('user.profile')]);
            }

            if ($this->bookingClosedForPayment($session)) {
                if (in_array($attempt->status, [PaymentAttempt::STATUS_PENDING, PaymentAttempt::STATUS_PROCESSING], true)) {
                    $this->cancelAttempt($attempt);
                }

                return response()->json(['message' => 'This booking cannot be paid again.'], 422);
            }

            if (!in_array($attempt->status, [PaymentAttempt::STATUS_PENDING, PaymentAttempt::STATUS_PROCESSING], true)) {
                return response()->json(['message' => 'This payment attempt cannot be processed. Please retry.'], 422);
            }

            if ($attempt->gateway_order_id !== $data['razorpay_order_id']) {
                $this->failAttempt($attempt, 'payment_order_mismatch', $data);
                return response()->json(['message' => 'Payment order mismatch. Please retry.'], 422);
            }

            if ($attempt->hold_expires_at && $attempt->hold_expires_at->lte(now())) {
                $attempt->update(['status' => PaymentAttempt::STATUS_EXPIRED, 'expired_at' => now()]);
                $this->log($attempt, 'payment_hold_expired', 'expired', $data);
                return response()->json(['message' => 'Payment hold expired. Please retry.'], 422);
            }

            if (!app(RazorpayPaymentService::class)->validSignature($attempt->gateway_order_id, $data['razorpay_payment_id'], $data['razorpay_signature'])) {
                $this->failAttempt($attempt, 'payment_signature_invalid', $data);
                return response()->json(['message' => 'Invalid payment signature. Please retry.'], 422);
            }

            $this->completePaymentAttempt($attempt, $session, $data['razorpay_payment_id'], $data, 'payment_verified');

            return response()->json(['success' => true, 'redirect_url' => route('user.profile')]);
        });
    }

    public function failure(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_attempt_id' => ['required', 'integer'],
            'razorpay_order_id' => ['required', 'string'],
            'error' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $attempt = PaymentAttempt::with('donation')
                ->whereKey($data['payment_attempt_id'])
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->status !== PaymentAttempt::STATUS_PAID && $attempt->gateway_order_id === $data['razorpay_order_id']) {
                $this->failAttempt($attempt, 'payment_failed', $data);
            }
        });

        return response()->json(['success' => true, 'message' => 'Payment failed. Please retry.']);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature');

        if (!app(RazorpayPaymentService::class)->validWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid webhook signature.'], 403);
        }

        $data = $request->json()->all();
        $payment = $data['payload']['payment']['entity'] ?? [];
        $orderId = $payment['order_id'] ?? null;
        $paymentId = $payment['id'] ?? null;

        if (($data['event'] ?? null) !== 'payment.captured' || !$orderId || !$paymentId) {
            return response()->json(['success' => true, 'message' => 'Webhook ignored.']);
        }

        DB::transaction(function () use ($data, $orderId, $paymentId) {
            $attempt = PaymentAttempt::with('donation')
                ->where('gateway_order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (!$attempt) {
                return;
            }

            $session = $this->lockedPayable($attempt);

            if ($attempt->status === PaymentAttempt::STATUS_PAID) {
                return;
            }

            if ($session->payment_status === 'paid') {
                $this->cancelAttempt($attempt);
                return;
            }

            if ($this->bookingClosedForPayment($session)) {
                if (in_array($attempt->status, [PaymentAttempt::STATUS_PENDING, PaymentAttempt::STATUS_PROCESSING], true)) {
                    $this->cancelAttempt($attempt);
                }

                return;
            }

            if (!in_array($attempt->status, [PaymentAttempt::STATUS_PENDING, PaymentAttempt::STATUS_PROCESSING], true)) {
                return;
            }

            if ($attempt->hold_expires_at && $attempt->hold_expires_at->lte(now())) {
                $attempt->update(['status' => PaymentAttempt::STATUS_EXPIRED, 'expired_at' => now()]);
                $this->log($attempt, 'payment_hold_expired', 'expired', $data);
                return;
            }

            $this->completePaymentAttempt($attempt, $session, $paymentId, $data, 'payment_captured_webhook');
        });

        return response()->json(['success' => true]);
    }

    private function refreshHoldIfAvailable(Model $session, string $type): void
    {
        $service = app(PanditBookingService::class);
        Pandit::whereKey($session->pandit_id)->lockForUpdate()->firstOrFail();

        if ($type === 'hawan') {
            $ritual = Hawan::active()->whereKey($session->ritual_id)->firstOrFail();
            $hawanType = $session->hawan_type ?: 'special';
            [$pandit] = $service->ensurePanditCanServe($session->pandit_id, 'hawan', $service->serviceNames($ritual->name, 'hawan'), $session->booking_date->toDateString(), $session->slot, 'online', $session->pandit_service_id, $ritual->id);
            $times = $service->slotTimes($session->slot);

            if ($service->hasBlockingHawanBooking($pandit->id, $ritual->id, $hawanType, $session->booking_date->toDateString(), $times['start'], $times['end'])) {
                throw ValidationException::withMessages(['slot' => 'This slot is no longer available. Please choose another pandit or slot.']);
            }

            if ($hawanType === 'samuhik' && !$service->samuhikHawanSessionHasCapacity($pandit->id, $ritual->id, $session->booking_date->toDateString(), $times['start'], $times['end'])) {
                throw ValidationException::withMessages(['slot' => 'This Samuhik Hawan session is full. Please choose another slot.']);
            }
        } else {
            $ritual = Pooja::active()->whereKey($session->ritual_id)->firstOrFail();
            $times = $service->slotTimes($session->slot);
            $service->ensurePanditCanServe($session->pandit_id, 'pooja', $service->serviceNames($ritual->name, 'pooja'), $session->booking_date->toDateString(), $session->slot, 'online', $session->pandit_service_id, $ritual->id);

            if ($service->hasOverlappingBooking($session->pandit_id, $session->booking_date->toDateString(), $times['start'], $times['end'])) {
                throw ValidationException::withMessages(['slot' => 'This slot is no longer available. Please choose another pandit or slot.']);
            }
        }

        [$start, $end] = $service->holdTimes();
        $session->update(['payment_hold_started_at' => $start, 'payment_hold_expires_at' => $end]);
    }

    private function sessionQuery(string $type)
    {
        return match ($type) {
            'diya' => DiyaSession::query(),
            'hawan' => HawanSession::query(),
            'pooja' => PoojaSession::query(),
            default => abort(404),
        };
    }

    private function createDiyaPaymentAttempt(DiyaSession $session): PaymentAttempt
    {
        $amount = $this->diyaAmount($session);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['payment' => 'Diya payment amount is missing.']);
        }

        $meta = $this->meta($session, 'diya') + [
            'booking_type' => 'diya',
            'diya_session_id' => $session->id,
            'diya_id' => $session->diya_id,
            'deity_id' => $session->deity_id,
            'sankalp_form_id' => $session->sankalp_form_id,
            'donation_amount' => $amount,
        ];

        $donation = Donation::create([
            'user_id' => $session->user_id,
            'payment_purpose' => 'diya',
            'session_type' => DiyaSession::class,
            'session_id' => $session->id,
            'amount' => $amount,
            'currency' => 'INR',
            'donor_name' => $meta['donor_name'] ?? null,
            'donor_mobile' => $meta['donor_mobile'] ?? null,
            'payment_status' => 'pending',
        ]);

        $attempt = PaymentAttempt::create([
            'user_id' => $session->user_id,
            'donation_id' => $donation->id,
            'payable_type' => DiyaSession::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_DONATION,
            'gateway' => 'none',
            'amount' => $amount,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PENDING,
            'metadata' => $meta,
        ]);

        $donation->update(['latest_payment_attempt_id' => $attempt->id]);
        $session->update(['latest_payment_attempt_id' => $attempt->id]);

        PaymentLog::create([
            'donation_id' => $donation->id,
            'payment_attempt_id' => $attempt->id,
            'loggable_type' => DiyaSession::class,
            'loggable_id' => $session->id,
            'user_id' => $session->user_id,
            'gateway' => 'none',
            'event_type' => 'payment_hold_created',
            'status' => 'pending',
            'occurred_at' => now(),
            'amount' => $amount,
            'payload' => $meta,
        ]);

        return $attempt;
    }

    private function lockedPayable(PaymentAttempt $attempt): Model
    {
        return $attempt->payable_type::whereKey($attempt->payable_id)->lockForUpdate()->firstOrFail();
    }

    private function amount(Model $session): float
    {
        $meta = $this->meta($session, $session instanceof HawanSession ? 'hawan' : 'pooja');

        return (float) ($meta['total_amount'] ?? $meta['package_amount'] ?? $session->hawan_type_price ?? 0);
    }

    private function meta(Model $session, string $type): array
    {
        $meta = $session->admin_note ? (json_decode($session->admin_note, true) ?: []) : [];

        return $meta + [
            'booking_type' => $type,
            'donor_name' => $session->sankalp?->full_name,
            'donor_mobile' => $session->sankalp?->mobile,
        ];
    }

    private function diyaAmount(DiyaSession $session): float
    {
        $meta = $session->admin_note ? (json_decode($session->admin_note, true) ?: []) : [];

        return (float) (
            $meta['total_amount']
            ?? $meta['donation_amount']
            ?? $session->latestPaymentAttempt?->amount
            ?? 0
        );
    }

    private function cancelOpenAttempts(Model $session): void
    {
        PaymentAttempt::where('payable_type', get_class($session))
            ->where('payable_id', $session->id)
            ->whereIn('status', [PaymentAttempt::STATUS_PENDING, PaymentAttempt::STATUS_PROCESSING])
            ->update(['status' => PaymentAttempt::STATUS_CANCELLED, 'cancelled_at' => now()]);
    }

    private function bookingClosedForPayment(Model $session): bool
    {
        return in_array($session->status, ['cancelled', 'cancelled_by_pandit', 'completed', 'refunded'], true)
            || $session->payment_status === 'refunded';
    }

    private function markPayablePaid(Model $session): void
    {
        if ($session instanceof DiyaSession) {
            $startAt = now();
            $endAt = $this->diyaEndAt($startAt, $session->diya?->duration);

            $session->update([
                'status' => DiyaSession::STATUS_ACTIVE,
                'payment_status' => 'paid',
                'start_at' => $startAt,
                'end_at' => $endAt,
                'expires_at' => $endAt,
            ]);

            return;
        }

        $session->update([
            'status' => 'scheduled',
            'payment_status' => 'paid',
        ]);
    }

    private function diyaEndAt($startAt, ?string $duration)
    {
        if (!$duration || !preg_match('/(\d+)\s*(day|days|hour|hours|hr|hrs|minute|minutes|min|mins)/i', $duration, $matches)) {
            return null;
        }

        $number = (int) $matches[1];
        $unit = strtolower($matches[2]);

        if (str_starts_with($unit, 'day')) {
            return $startAt->copy()->addDays($number);
        }

        if (str_starts_with($unit, 'hour') || str_starts_with($unit, 'hr')) {
            return $startAt->copy()->addHours($number);
        }

        return $startAt->copy()->addMinutes($number);
    }

    private function completePaymentAttempt(PaymentAttempt $attempt, Model $session, string $paymentId, array $payload, string $event): void
    {
        $attempt->update([
            'gateway_payment_id' => $paymentId,
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $attempt->donation?->update([
            'razorpay_payment_id' => $paymentId,
            'payment_status' => 'paid',
            'receipt_number' => 'BDP-'.now()->format('Ymd').'-'.$attempt->id,
            'paid_at' => now(),
        ]);

        $this->markPayablePaid($session);

        if ($session instanceof DiyaSession) {
            app(UserBookingNotificationService::class)->diyaPaymentSuccessful($session->fresh(['user', 'diya', 'deity']));
        }

        if ($attempt->purpose === PaymentAttempt::PURPOSE_BOOKING) {
            app(PanditPayoutLedgerService::class)->holdForSuccessfulPayment($session, $attempt);
        }

        $this->log($attempt, $event, 'paid', $payload);
    }

    private function cancelAttempt(PaymentAttempt $attempt): void
    {
        $attempt->update(['status' => PaymentAttempt::STATUS_CANCELLED, 'cancelled_at' => now()]);
        $this->log($attempt, 'payment_duplicate_ignored', 'cancelled', []);
    }

    private function failAttempt(PaymentAttempt $attempt, string $event, array $payload): void
    {
        $attempt->update(['status' => PaymentAttempt::STATUS_FAILED, 'failed_at' => now()]);
        $this->log($attempt, $event, 'failed', $payload);
    }

    private function log(PaymentAttempt $attempt, string $event, string $status, array $payload): void
    {
        PaymentLog::create([
            'donation_id' => $attempt->donation_id,
            'payment_attempt_id' => $attempt->id,
            'loggable_type' => $attempt->payable_type,
            'loggable_id' => $attempt->payable_id,
            'user_id' => $attempt->user_id,
            'gateway' => 'razorpay_test',
            'event_type' => $event,
            'order_id' => $attempt->gateway_order_id,
            'payment_id' => $attempt->gateway_payment_id,
            'status' => $status,
            'occurred_at' => now(),
            'amount' => $attempt->amount,
            'payload' => $payload,
        ]);
    }
}
