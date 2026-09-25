<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\PanditPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminPayoutController extends Controller
{
    public function index(Request $request)
    {
        $statuses = [
            PanditPayout::STATUS_HOLD,
            PanditPayout::STATUS_READY,
            PanditPayout::STATUS_PROCESSING,
            PanditPayout::STATUS_PAID,
            PanditPayout::STATUS_CANCELLED,
            PanditPayout::STATUS_FAILED,
        ];
        $status = $request->query('status');

        $payouts = PanditPayout::with(['pandit', 'paymentAttempt', 'session'])
            ->when(in_array($status, $statuses, true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $payoutMode = config('services.payouts.mode', 'manual');
        $routeAutomatic = $payoutMode === 'route'
            && (bool) config('services.payouts.route_enabled', false);
        $admin = auth('admin')->user();
        $canManagePayouts = $admin?->hasPermission('manage-payouts')
            && in_array($admin->role?->slug, ['super-admin', 'finance-admin'], true);
        $readyGroups = $payoutMode === 'manual' && $canManagePayouts
            ? PanditPayout::with('pandit.bankDetail')
                ->where('status', PanditPayout::STATUS_READY)
                ->orderBy('id')
                ->get()
                ->groupBy('pandit_id')
            : collect();

        return view('admin.payouts.index', [
            'payouts' => $payouts,
            'statuses' => $statuses,
            'status' => $status,
            'routeAutomatic' => $routeAutomatic,
            'payoutMode' => $payoutMode,
            'canManagePayouts' => $canManagePayouts,
            'readyGroups' => $readyGroups,
            'readyTotal' => PanditPayout::where('status', PanditPayout::STATUS_READY)->sum('pandit_amount'),
            'hawanSessionClass' => HawanSession::class,
            'poojaSessionClass' => PoojaSession::class,
        ]);
    }

    public function settle(Request $request)
    {
        abort_unless(in_array(auth('admin')->user()?->role?->slug, ['super-admin', 'finance-admin'], true), 403);

        if (config('services.payouts.mode', 'manual') !== 'manual') {
            throw ValidationException::withMessages(['manual_payout' => 'Manual payouts are disabled.']);
        }

        $data = $request->validate([
            'payout_ids' => ['required', 'array', 'min:1'],
            'payout_ids.*' => ['integer', 'distinct', 'exists:pandit_payouts,id'],
            'payment_method' => ['required', Rule::in(['bank_transfer', 'neft', 'rtgs', 'imps', 'upi'])],
            'utr' => ['required', 'string', 'max:100'],
        ]);

        $ids = collect($data['payout_ids'])->map(fn ($id) => (int) $id)->sort()->values();
        $utr = strtoupper(trim($data['utr']));
        $total = DB::transaction(function () use ($ids, $data, $utr) {
            $payouts = PanditPayout::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();

            if ($payouts->count() !== $ids->count()
                || $payouts->contains(fn ($payout) => $payout->status !== PanditPayout::STATUS_READY)
                || $payouts->pluck('pandit_id')->unique()->count() !== 1) {
                throw ValidationException::withMessages(['manual_payout' => 'Select READY payouts for one Pandit only.']);
            }

            if (PanditPayout::where('status', PanditPayout::STATUS_PAID)
                ->where('payout_reference', $utr)
                ->whereNotIn('id', $ids)
                ->lockForUpdate()
                ->exists()) {
                throw ValidationException::withMessages(['utr' => 'This UTR has already been used.']);
            }

            $total = round((float) $payouts->sum('pandit_amount'), 2);
            $history = [
                'payment_method' => $data['payment_method'],
                'utr' => $utr,
                'amount' => $total,
                'payout_ids' => $ids->all(),
                'admin_id' => auth('admin')->id(),
                'paid_at' => now()->toIso8601String(),
            ];

            foreach ($payouts as $payout) {
                $metadata = $payout->metadata ?: [];
                $metadata['manual_settlement'] = $history;
                $payout->update([
                    'status' => PanditPayout::STATUS_PAID,
                    'payout_amount' => $payout->pandit_amount,
                    'payout_reference' => $utr,
                    'paid_at' => now(),
                    'metadata' => $metadata,
                ]);
            }

            return $total;
        });

        return back()->with('success', 'Manual payout of Rs '.number_format($total, 2).' marked as PAID.');
    }
}
