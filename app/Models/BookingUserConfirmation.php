<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingUserConfirmation extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_DISPUTED = 'disputed';
    public const STATUS_AUTO_CONFIRMED = 'auto_confirmed';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'session_type',
        'session_id',
        'user_id',
        'status',
        'rating',
        'feedback',
        'confirmed_at',
        'disputed_at',
        'expires_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'confirmed_at' => 'datetime',
            'disputed_at' => 'datetime',
            'expires_at' => 'datetime',
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
}
