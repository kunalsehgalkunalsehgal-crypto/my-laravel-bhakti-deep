<?php

namespace App\Services;

use App\Jobs\ProcessPanditPayout;
use App\Models\PanditPayout;
use Illuminate\Support\Facades\DB;

class PanditPayoutAutomationService
{
    public function enabled(): bool
    {
        return config('services.payouts.mode', 'manual') === 'route'
            && (bool) config('services.payouts.route_enabled', false);
    }

    public function payoutBecameReady(PanditPayout $payout, ?string $previousStatus): void
    {
        if (! $this->enabled() || $previousStatus === PanditPayout::STATUS_READY) {
            return;
        }

        DB::afterCommit(fn () => ProcessPanditPayout::dispatch($payout->id));
    }

    public function retryFailedForPandit(int $panditId): void
    {
        PanditPayout::query()
            ->where('pandit_id', $panditId)
            ->where('status', PanditPayout::STATUS_FAILED)
            ->whereNull('razorpay_transfer_id')
            ->eachById(function (PanditPayout $payout) {
                $updated = DB::transaction(function () use ($payout) {
                    $locked = PanditPayout::whereKey($payout->id)->lockForUpdate()->firstOrFail();

                    if ($locked->status !== PanditPayout::STATUS_FAILED || $locked->razorpay_transfer_id) {
                        return null;
                    }

                    $locked->update([
                        'status' => PanditPayout::STATUS_READY,
                        'failed_at' => null,
                        'last_error' => null,
                    ]);

                    return $locked->fresh();
                });

                if ($updated) {
                    $this->payoutBecameReady($updated, PanditPayout::STATUS_FAILED);
                }
            });
    }
}
