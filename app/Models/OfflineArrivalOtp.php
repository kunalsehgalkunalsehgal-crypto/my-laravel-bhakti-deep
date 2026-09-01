<?php

namespace App\Models;

use App\Models\Pandit\Pandit;
use Illuminate\Database\Eloquent\Model;

class OfflineArrivalOtp extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'session_type',
        'session_id',
        'user_id',
        'pandit_id',
        'otp_hash',
        'otp_hint',
        'status',
        'verification_attempts',
        'sent_at',
        'expires_at',
        'verified_at',
        'cancelled_at',
        'metadata',
    ];

    protected $hidden = [
        'otp_hash',
    ];

    protected function casts(): array
    {
        return [
            'verification_attempts' => 'integer',
            'sent_at' => 'datetime',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function session()
    {
        return $this->morphTo(__FUNCTION__, 'session_type', 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }
}
