<?php

namespace App\Models;

use App\Models\Admin\Donation;
use App\Models\Pandit\Pandit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PanditPayout extends Model
{
    public const STATUS_HOLD = 'hold';

    public const STATUS_READY = 'ready';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PENDING = 'hold';

    public const STATUS_ON_HOLD = 'hold';

    public const STATUS_APPROVED = 'ready';

    public const TYPE_BOOKING = 'booking';

    public const TYPE_QUICK_DAKSHINA = 'quick_dakshina';

    protected $fillable = [
        'uuid',
        'pandit_id',
        'payment_attempt_id',
        'donation_id',
        'session_type',
        'session_id',
        'payout_type',
        'service_amount',
        'booking_amount',
        'pandit_amount',
        'platform_amount',
        'commission_percent',
        'gross_amount',
        'platform_fee',
        'dakshina_amount',
        'payout_amount',
        'currency',
        'status',
        'payout_reference',
        'provider_payout_id',
        'razorpay_transfer_id',
        'gateway_status',
        'transfer_attempts',
        'processing_token',
        'processing_started_at',
        'last_error',
        'scheduled_at',
        'approved_at',
        'eligible_at',
        'paid_at',
        'failed_at',
        'cancelled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'service_amount' => 'decimal:2',
            'booking_amount' => 'decimal:2',
            'pandit_amount' => 'decimal:2',
            'platform_amount' => 'decimal:2',
            'commission_percent' => 'decimal:4',
            'gross_amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'dakshina_amount' => 'decimal:2',
            'payout_amount' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'approved_at' => 'datetime',
            'eligible_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'transfer_attempts' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PanditPayout $payout) {
            $payout->uuid ??= (string) Str::uuid();
        });
    }

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }

    public function paymentAttempt()
    {
        return $this->belongsTo(PaymentAttempt::class);
    }

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    public function session()
    {
        return $this->morphTo(__FUNCTION__, 'session_type', 'session_id');
    }
}
