<?php

use App\Models\BookingUserConfirmation;
use App\Models\Dispute;
use App\Models\PanditPayout;
use App\Services\PanditPayoutAutomationService;
use App\Services\PanditPayoutLedgerService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('diyas:sync-lifecycle')->everyMinute()->withoutOverlapping();

Artisan::command('bookings:auto-confirm-completions', function () {
    BookingUserConfirmation::query()
        ->where('status', BookingUserConfirmation::STATUS_PENDING)
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->get()
        ->each(function (BookingUserConfirmation $confirmation) {
            $booking = $confirmation->session;

            if (! $booking || $booking->disputes()->whereIn('status', [Dispute::STATUS_OPEN, Dispute::STATUS_UNDER_REVIEW])->exists()) {
                return;
            }

            $confirmation->update([
                'status' => BookingUserConfirmation::STATUS_AUTO_CONFIRMED,
                'confirmed_at' => now(),
            ]);

            app(PanditPayoutLedgerService::class)->syncForBooking($booking, 'completion_auto_confirmed');
        });
})->purpose('Auto-confirm completed Hawan and Pooja bookings after 24 hours');

Schedule::command('bookings:auto-confirm-completions')->hourly()->withoutOverlapping();

Artisan::command('payouts:process-ready', function () {
    $automation = app(PanditPayoutAutomationService::class);

    if (! $automation->enabled()) {
        return;
    }

    PanditPayout::query()
        ->where('status', PanditPayout::STATUS_READY)
        ->whereNull('razorpay_transfer_id')
        ->eachById(fn (PanditPayout $payout) => $automation->payoutBecameReady($payout, null));
})->purpose('Queue Razorpay Route transfers for READY pandit payouts');

Schedule::command('payouts:process-ready')->everyFiveMinutes()->withoutOverlapping();
