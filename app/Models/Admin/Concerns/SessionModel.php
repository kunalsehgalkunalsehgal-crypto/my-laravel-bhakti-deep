<?php

namespace App\Models\Admin\Concerns;

use App\Models\Admin\SankalpForm;
use App\Models\Admin\Service;
use App\Models\BookingUserConfirmation;
use App\Models\Dispute;
use App\Models\OfflineArrivalOtp;
use App\Models\PanditNoShowReport;
use App\Models\PanditPayout;
use App\Models\PaymentAttempt;
use App\Models\PaymentDispute;
use App\Models\Review;
use App\Models\SessionCompletionProof;
use App\Models\VideoMeetingAttendance;
use App\Models\VideoMeeting;
use App\Models\User;
use App\Models\Admin\HawanSession;

trait SessionModel
{
    public function initializeSessionModel(): void
    {
        $this->fillable = [
            'user_id',
            'service_id',
            'service_type',
            'ritual_id',
            'ritual_slug',
            'hawan_type',
            'hawan_type_title',
            'hawan_type_price',
            'diya_id',
            'deity_id',
            'sankalp_form_id',
            'pandit_id',
            'pandit_service_id',
            'booking_date',
            'start_at',
            'end_at',
            'slot',
            'slot_start_time',
            'slot_end_time',
            'live_session_link',
            'live_session_token',
            'status',
            'payment_status',
            'payment_hold_started_at',
            'payment_hold_expires_at',
            'latest_payment_attempt_id',
            'admin_note',
            'pandit_cancel_reason',
            'pandit_cancelled_at',
            'cancelled_by_pandit_id',
            'completed_at',
            'expires_at',
        ];
    }

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'payment_hold_started_at' => 'datetime',
            'payment_hold_expires_at' => 'datetime',
            'pandit_cancelled_at' => 'datetime',
            'hawan_type_price' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function sankalp()
    {
        return $this->belongsTo(SankalpForm::class, 'sankalp_form_id');
    }

    public function pandit()
    {
        return $this->belongsTo(\App\Models\Pandit\Pandit::class);
    }

    public function panditService()
    {
        return $this->belongsTo(\App\Models\Pandit\PanditService::class);
    }

    public function videoMeeting()
    {
        return $this->morphOne(VideoMeeting::class, 'session', 'session_type', 'session_id');
    }

    public function videoMeetingAttendances()
    {
        return $this->morphMany(VideoMeetingAttendance::class, 'session', 'session_type', 'session_id');
    }

    public function latestPaymentAttempt()
    {
        return $this->belongsTo(PaymentAttempt::class, 'latest_payment_attempt_id');
    }

    public function paymentAttempts()
    {
        return $this->morphMany(PaymentAttempt::class, 'payable', 'payable_type', 'payable_id');
    }

    public function paymentDisputes()
    {
        return $this->morphMany(PaymentDispute::class, 'session', 'session_type', 'session_id');
    }

    public function panditPayouts()
    {
        return $this->morphMany(PanditPayout::class, 'session', 'session_type', 'session_id');
    }

    public function completionProofs()
    {
        return $this->morphMany(SessionCompletionProof::class, 'session', 'session_type', 'session_id');
    }

    public function userConfirmations()
    {
        return $this->morphMany(BookingUserConfirmation::class, 'session', 'session_type', 'session_id');
    }

    public function noShowReports()
    {
        return $this->morphMany(PanditNoShowReport::class, 'session', 'session_type', 'session_id');
    }

    public function disputes()
    {
        return $this->morphMany(Dispute::class, 'disputable');
    }

    public function offlineArrivalOtps()
    {
        return $this->morphMany(OfflineArrivalOtp::class, 'session', 'session_type', 'session_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'booking_id')
            ->where('booking_type', $this instanceof HawanSession ? 'hawan' : 'pooja');
    }
}
