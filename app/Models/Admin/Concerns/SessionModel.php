<?php

namespace App\Models\Admin\Concerns;

use App\Models\Admin\SankalpForm;
use App\Models\Admin\Service;
use App\Models\VideoMeeting;
use App\Models\User;

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
            'admin_note',
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
}
