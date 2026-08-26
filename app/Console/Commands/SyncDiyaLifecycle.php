<?php

namespace App\Console\Commands;

use App\Models\Admin\DiyaSession;
use Illuminate\Console\Command;

class SyncDiyaLifecycle extends Command
{
    protected $signature = 'diyas:sync-lifecycle';

    protected $description = 'Synchronize Diya session statuses from start_at and end_at timestamps.';

    public function handle(): int
    {
        $now = now();

        $scheduled = DiyaSession::query()
            ->where('payment_status', 'paid')
            ->whereNotNull('start_at')
            ->where('start_at', '>', $now)
            ->whereNotIn('status', [DiyaSession::STATUS_SCHEDULED, DiyaSession::STATUS_COMPLETED, 'cancelled'])
            ->update([
                'status' => DiyaSession::STATUS_SCHEDULED,
                'updated_at' => $now,
            ]);

        $activated = DiyaSession::query()
            ->where('payment_status', 'paid')
            ->whereNotNull('start_at')
            ->where('start_at', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>', $now);
            })
            ->whereIn('status', [DiyaSession::STATUS_SCHEDULED, 'pending', 'confirmed'])
            ->update([
                'status' => DiyaSession::STATUS_ACTIVE,
                'updated_at' => $now,
            ]);

        $completed = DiyaSession::query()
            ->where('payment_status', 'paid')
            ->whereNotNull('end_at')
            ->where('end_at', '<=', $now)
            ->whereNotIn('status', [DiyaSession::STATUS_COMPLETED, 'cancelled'])
            ->update([
                'status' => DiyaSession::STATUS_COMPLETED,
                'completed_at' => $now,
                'updated_at' => $now,
            ]);

        $this->info("Diya lifecycle synced. Scheduled: {$scheduled}, activated: {$activated}, completed: {$completed}.");

        return self::SUCCESS;
    }
}
