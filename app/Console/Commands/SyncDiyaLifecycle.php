<?php

namespace App\Console\Commands;

use App\Models\Admin\DiyaSession;
use App\Services\UserBookingNotificationService;
use Illuminate\Console\Command;

class SyncDiyaLifecycle extends Command
{
    protected $signature = 'diyas:sync-lifecycle';

    protected $description = 'Synchronize Diya session statuses from start_at and end_at timestamps.';

    public function handle(): int
    {
        $now = now();
        $notifications = app(UserBookingNotificationService::class);

        $scheduled = DiyaSession::query()
            ->where('payment_status', 'paid')
            ->whereNotNull('start_at')
            ->where('start_at', '>', $now)
            ->whereNotIn('status', [DiyaSession::STATUS_SCHEDULED, DiyaSession::STATUS_COMPLETED, 'cancelled'])
            ->update([
                'status' => DiyaSession::STATUS_SCHEDULED,
                'updated_at' => $now,
            ]);

        $activatingSessions = DiyaSession::with(['user', 'diya', 'deity'])
            ->where('payment_status', 'paid')
            ->whereNotNull('start_at')
            ->where('start_at', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>', $now);
            })
            ->whereIn('status', [DiyaSession::STATUS_SCHEDULED, 'pending', 'confirmed'])
            ->get();

        foreach ($activatingSessions as $session) {
            $session->update([
                'status' => DiyaSession::STATUS_ACTIVE,
                'updated_at' => $now,
            ]);

            $notifications->diyaStarted($session);
        }

        $completedSessions = DiyaSession::with(['user', 'diya', 'deity'])
            ->where('payment_status', 'paid')
            ->whereNotNull('end_at')
            ->where('end_at', '<=', $now)
            ->whereNotIn('status', [DiyaSession::STATUS_COMPLETED, 'cancelled'])
            ->get();

        foreach ($completedSessions as $session) {
            $session->update([
                'status' => DiyaSession::STATUS_COMPLETED,
                'completed_at' => $now,
                'updated_at' => $now,
            ]);

            $notifications->diyaCompleted($session);
        }

        $activated = $activatingSessions->count();
        $completed = $completedSessions->count();

        $this->info("Diya lifecycle synced. Scheduled: {$scheduled}, activated: {$activated}, completed: {$completed}.");

        return self::SUCCESS;
    }
}
