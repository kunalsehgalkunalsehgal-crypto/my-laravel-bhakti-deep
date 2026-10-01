<?php

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\BookingUserConfirmation;
use App\Models\Dispute;
use App\Models\PanditPayout;
use App\Services\BookingTodayReminderService;
use App\Services\PanditPayoutAutomationService;
use App\Services\PanditPayoutLedgerService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
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

Artisan::command('bookings:send-today-reminders', function () {
    $today = Carbon::now('Asia/Kolkata')->toDateString();

    $service = app(BookingTodayReminderService::class);

    $sent = 0;

    PoojaSession::query()
        ->with([
            'user',
            'pandit',
            'sankalp',
            'videoMeeting',
        ])
        ->where(function ($query) {
            $query
                ->where('pooja_type', 'live')
                ->orWhereNull('pooja_type');
        })
        ->whereDate('booking_date', $today)
        ->where('payment_status', 'paid')
        ->where('status', 'confirmed')
        ->whereNotNull('pandit_id')
        ->where(function ($query) {
            $query
                ->where('booking_mode', 'offline')
                ->orWhere(function ($onlineQuery) {
                    $onlineQuery
                        ->where(function ($modeQuery) {
                            $modeQuery
                                ->where('booking_mode', 'online')
                                ->orWhereNull('booking_mode');
                        })
                        ->whereHas('videoMeeting');
                });
        })
        ->eachById(function (PoojaSession $session) use ($service, &$sent) {
            $sent += $service->send($session, 'pooja');
        });

    HawanSession::query()
        ->with([
            'user',
            'pandit',
            'sankalp',
            'videoMeeting',
        ])
        ->whereDate('booking_date', $today)
        ->where('payment_status', 'paid')
        ->where('status', 'confirmed')
        ->whereNotNull('pandit_id')
        ->where(function ($query) {
            $query
                ->where('booking_mode', 'offline')
                ->orWhere(function ($onlineQuery) {
                    $onlineQuery
                        ->where(function ($modeQuery) {
                            $modeQuery
                                ->where('booking_mode', 'online')
                                ->orWhereNull('booking_mode');
                        })
                        ->whereHas('videoMeeting');
                });
        })
        ->eachById(function (HawanSession $session) use ($service, &$sent) {
            $sent += $service->send($session, 'hawan');
        });

    $this->info(
        'Today booking reminder emails sent: '.$sent
    );
})->purpose(
    'Send same-day Pooja/Hawan reminder emails to the user and assigned pandit'
);
Schedule::command('bookings:send-today-reminders')
    ->hourly()
    ->between('07:00', '22:00')
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping();
