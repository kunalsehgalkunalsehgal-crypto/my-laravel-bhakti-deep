<?php

namespace App\Models;

use App\Models\Pandit\Pandit;
use Illuminate\Database\Eloquent\Model;

class VideoMeeting extends Model
{
    protected $fillable = [
        'provider',
        'external_meeting_id',
        'join_url',
        'host_url',
        'passcode',
        'status',
        'starts_at',
        'duration_minutes',
        'pandit_id',
        'session_type',
        'session_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }

    public function session()
    {
        return $this->morphTo(__FUNCTION__, 'session_type', 'session_id');
    }
}
