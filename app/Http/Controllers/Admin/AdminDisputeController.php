<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminActivityLog;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\Pandit\Pandit;
use App\Models\PaymentAttempt;
use App\Models\PaymentRefund;
use App\Models\User;
use App\Services\PanditDisputeNotificationService;
use App\Services\PanditPayoutLedgerService;
use App\Services\RazorpayPaymentService;
use App\Services\UserBookingNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminDisputeController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $statuses = [Dispute::STATUS_OPEN, Dispute::STATUS_UNDER_REVIEW, Dispute::STATUS_RESOLVED, Dispute::STATUS_REJECTED];

        $disputes = Dispute::with(['user', 'pandit', 'disputable.service'])
            ->whereIn('disputable_type', [HawanSession::class, PoojaSession::class])
            ->when(in_array($status, $statuses, true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.disputes.index', compact('disputes', 'status', 'statuses'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load([
            'user',
            'pandit',
            'evidences',
            'disputable.user',
            'disputable.pandit',
            'disputable.service',
            'disputable.sankalp',
            'disputable.latestPaymentAttempt.logs',
            'paymentRefund',
            'resolvedByAdmin',
            'disputable.videoMeetingAttendances',
        ]);

        abort_unless($dispute->disputable instanceof HawanSession || $dispute->disputable instanceof PoojaSession, 404);

        return view('admin.disputes.show', [
            'dispute' => $dispute,
            'booking' => $dispute->disputable,
            'type' => $dispute->disputable instanceof HawanSession ? 'hawan' : 'pooja',
            'userProofs' => $dispute->evidences->where('uploaded_by_type', User::class),
            'panditProofs' => $dispute->evidences->where('uploaded_by_type', Pandit::class),
        ]);
    }

    public function update(Request $request, Dispute $dispute)
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:'.Dispute::STATUS_UNDER_REVIEW],
            'admin_review_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $changes = [];

        if (($data['status'] ?? null) === Dispute::STATUS_UNDER_REVIEW && $dispute->status === Dispute::STATUS_OPEN) {
            $changes['status'] = Dispute::STATUS_UNDER_REVIEW;
        }

        if (array_key_exists('admin_review_note', $data)) {
            $changes['admin_review_note'] = $data['admin_review_note'];
        }

        if ($changes) {
            $dispute->update($changes);

            if (($changes['status'] ?? null) === Dispute::STATUS_UNDER_REVIEW) {
                app(UserBookingNotificationService::class)->reportUnderReview($dispute->fresh('user'));
                app(PanditDisputeNotificationService::class)->reportUnderReview($dispute->fresh());
            }

            $this->log('review_update', 'Dispute #'.$dispute->id.' reviewed by admin.');
        }

        return back()->with('success', 'Dispute review updated.');
    }

    public function refundUser(Dispute $dispute)
    {
        $result = DB::transaction(function () use ($dispute) {
            $lockedDispute = Dispute::whereKey($dispute->id)->lockForUpdate()->firstOrFail();
            $booking = $this->lockedBooking($lockedDispute);
            $attempt = $this->lockedPaidAttempt($booking);

            if ($lockedDispute->status === Dispute::STATUS_RESOLVED) {
                throw ValidationException::withMessages(['resolution' => 'This dispute is already resolved.']);
            }

            if ($attempt->refunds()
                ->whereNotIn('status', [PaymentRefund::STATUS_FAILED, PaymentRefund::STATUS_REJECTED, PaymentRefund::STATUS_CANCELLED])
                ->lockForUpdate()
                ->exists()) {
                throw ValidationException::withMessages(['payment' => 'Refund is already initiated for this booking.']);
            }

            $refundData = app(RazorpayPaymentService::class)->refund($attempt, 'admin_dispute_refund_'.$lockedDispute->id);
            $gatewayStatus = $refundData['status'] ?? null;
            $refundStatus = $this->localRefundStatus($gatewayStatus);

            $refund = PaymentRefund::create([
                'payment_attempt_id' => $attempt->id,
                'donation_id' => $attempt->donation_id,
                'user_id' => $booking->user_id,
                'requested_by_admin_id' => Auth::guard('admin')->id(),
                'processed_by_admin_id' => Auth::guard('admin')->id(),
                'amount' => $attempt->amount,
                'currency' => $attempt->currency,
                'reason' => 'Dispute resolved in user favour.',
                'status' => $refundStatus,
                'gateway_refund_id' => $refundData['id'] ?? null,
                'gateway_status' => $gatewayStatus,
                'requested_at' => now(),
                'approved_at' => now(),
                'processed_at' => $refundStatus === PaymentRefund::STATUS_REFUNDED ? now() : null,
                'failed_at' => $refundStatus === PaymentRefund::STATUS_FAILED ? now() : null,
                'metadata' => $refundData,
            ]);

            if ($refundStatus === PaymentRefund::STATUS_FAILED) {
                $this->log(
                    'dispute_refund_failed',
                    'Razorpay refund failed for dispute #'.$lockedDispute->id.'.'
                );

                return [
                    'failed' => true,
                    'processed' => false,
                    'refund_id' => $refund->gateway_refund_id,
                ];
            }

            if ($refundStatus === PaymentRefund::STATUS_REFUNDED) {
                $booking->update(['payment_status' => 'refunded']);
                $attempt->donation?->update([
                    'payment_status' => 'refunded',
                    'refunded_amount' => $attempt->amount,
                ]);
            }

            app(PanditPayoutLedgerService::class)->cancelForRefund($booking, $attempt, 'dispute_refund_user');

            $lockedDispute->update([
                'status' => Dispute::STATUS_RESOLVED,
                'resolution' => Dispute::RESOLUTION_REFUND_USER,
                'resolved_by_admin_id' => Auth::guard('admin')->id(),
                'resolved_at' => now(),
                'payment_refund_id' => $refund->id,
            ]);

            app(UserBookingNotificationService::class)->disputeResolvedForUser($lockedDispute->fresh('user'));
            app(PanditDisputeNotificationService::class)->disputeResolvedForUser($lockedDispute->fresh(), $refund);

            if ($refundStatus === PaymentRefund::STATUS_REFUNDED) {
                app(UserBookingNotificationService::class)->refundProcessed($refund, $lockedDispute->fresh('user'));
                app(PanditDisputeNotificationService::class)->refundProcessed($refund, $lockedDispute->fresh());
            }

            $this->log(
                'dispute_refund_user',
                'Dispute #'.$lockedDispute->id.' resolved with Razorpay refund '.$refund->gateway_refund_id.'.'
            );

            return [
                'failed' => false,
                'processed' => $refundStatus === PaymentRefund::STATUS_REFUNDED,
                'refund_id' => $refund->gateway_refund_id,
            ];
        });

        if ($result['failed']) {
            return back()->withErrors(['payment' => 'Razorpay rejected this refund request.']);
        }

        $message = $result['processed']
            ? 'Refund processed by Razorpay and dispute resolved.'
            : 'Refund initiated with Razorpay and dispute resolved.';

        return back()->with('success', $message);
    }

    public function resolvePanditFavour(Dispute $dispute)
    {
        DB::transaction(function () use ($dispute) {
            $lockedDispute = Dispute::whereKey($dispute->id)->lockForUpdate()->firstOrFail();
            $booking = $this->lockedBooking($lockedDispute);

            if ($lockedDispute->status === Dispute::STATUS_RESOLVED) {
                throw ValidationException::withMessages(['resolution' => 'This dispute is already resolved.']);
            }

            $lockedDispute->update([
                'status' => Dispute::STATUS_RESOLVED,
                'resolution' => Dispute::RESOLUTION_PANDIT_FAVOUR,
                'resolved_by_admin_id' => Auth::guard('admin')->id(),
                'resolved_at' => now(),
            ]);

            app(PanditPayoutLedgerService::class)->readyForPanditFavour($booking);

            app(UserBookingNotificationService::class)->disputeResolvedForPandit($lockedDispute->fresh('user'));
            app(PanditDisputeNotificationService::class)->disputeResolvedForPandit($lockedDispute->fresh());

            $this->log(
                'dispute_pandit_favour',
                'Dispute #'.$lockedDispute->id.' resolved in pandit favour.'
            );
        });

        return back()->with('success', 'Dispute resolved in pandit favour.');
    }

    public function evidence(Dispute $dispute, DisputeEvidence $evidence)
    {
        abort_unless((int) $evidence->dispute_id === (int) $dispute->id, 404);

        $path = Storage::disk('local')->path($evidence->file_path);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => $evidence->mime_type,
            'Content-Disposition' => 'inline; filename="'.Str::ascii($evidence->original_name).'"',
        ]);
    }

    private function log(string $action, string $description): void
    {
        AdminActivityLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'module' => 'Disputes',
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function lockedBooking(Dispute $dispute): Model
    {
        abort_unless(in_array($dispute->disputable_type, [HawanSession::class, PoojaSession::class], true), 404);

        return $dispute->disputable_type::whereKey($dispute->disputable_id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockedPaidAttempt(Model $booking): PaymentAttempt
    {
        if ($booking->payment_status !== 'paid') {
            throw ValidationException::withMessages(['payment' => 'Only paid bookings can be refunded.']);
        }

        if (!$booking->latest_payment_attempt_id) {
            throw ValidationException::withMessages(['payment' => 'Paid payment attempt is missing.']);
        }

        $attempt = PaymentAttempt::with('donation')
            ->whereKey($booking->latest_payment_attempt_id)
            ->lockForUpdate()
            ->first();

        if (
            !$attempt
            || $attempt->status !== PaymentAttempt::STATUS_PAID
            || $attempt->payable_type !== get_class($booking)
            || (int) $attempt->payable_id !== (int) $booking->getKey()
        ) {
            throw ValidationException::withMessages(['payment' => 'Paid payment attempt is missing.']);
        }

        if (!$attempt->gateway_payment_id) {
            throw ValidationException::withMessages(['payment' => 'Razorpay payment id is missing.']);
        }

        return $attempt;
    }

    private function localRefundStatus(?string $gatewayStatus): string
    {
        return match ($gatewayStatus) {
            'processed' => PaymentRefund::STATUS_REFUNDED,
            'failed' => PaymentRefund::STATUS_FAILED,
            default => PaymentRefund::STATUS_PROCESSING,
        };
    }
}
