<?php

namespace App\Models;

use App\Models\Admin\Donation;
use App\Models\Admin\PaymentLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentAttempt extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const PURPOSE_BOOKING = 'booking';
    public const PURPOSE_QUICK_DAKSHINA = 'quick_dakshina';
    public const PURPOSE_DONATION = 'donation';

    protected $fillable = [
        'uuid',
        'user_id',
        'donation_id',
        'payable_type',
        'payable_id',
        'purpose',
        'gateway',
        'gateway_order_id',
        'gateway_payment_id',
        'amount',
        'currency',
        'status',
        'hold_started_at',
        'hold_expires_at',
        'paid_at',
        'failed_at',
        'expired_at',
        'cancelled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'hold_started_at' => 'datetime',
            'hold_expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'expired_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentAttempt $attempt) {
            $attempt->uuid ??= (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    public function payable()
    {
        return $this->morphTo(__FUNCTION__, 'payable_type', 'payable_id');
    }

    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function disputes()
    {
        return $this->hasMany(PaymentDispute::class);
    }

    public function payouts()
    {
        return $this->hasMany(PanditPayout::class);
    }

    public function logs()
    {
        return $this->hasMany(PaymentLog::class);
    }
}
