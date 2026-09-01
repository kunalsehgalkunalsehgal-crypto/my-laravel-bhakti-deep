<?php

namespace App\Models\Admin;

use App\Models\PaymentAttempt;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = [
        'donation_id',
        'payment_attempt_id',
        'loggable_type',
        'loggable_id',
        'user_id',
        'gateway',
        'event_type',
        'order_id',
        'payment_id',
        'status',
        'occurred_at',
        'amount',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'amount' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    public function paymentAttempt()
    {
        return $this->belongsTo(PaymentAttempt::class);
    }

    public function loggable()
    {
        return $this->morphTo(__FUNCTION__, 'loggable_type', 'loggable_id');
    }
}
