<?php

namespace App\Models;

use App\Models\Admin\Admin;
use App\Models\Admin\Donation;
use App\Services\PanditDisputeNotificationService;
use App\Services\UserBookingNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentRefund extends Model
{
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'uuid',
        'payment_attempt_id',
        'donation_id',
        'user_id',
        'requested_by_admin_id',
        'processed_by_admin_id',
        'amount',
        'currency',
        'reason',
        'status',
        'gateway_refund_id',
        'gateway_status',
        'requested_at',
        'approved_at',
        'processed_at',
        'failed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'processed_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentRefund $refund) {
            $refund->uuid ??= (string) Str::uuid();
        });

        static::updated(function (PaymentRefund $refund) {
            if ($refund->wasChanged('status') && $refund->status === self::STATUS_REFUNDED) {
                app(UserBookingNotificationService::class)->refundProcessed($refund);
                app(PanditDisputeNotificationService::class)->refundProcessed($refund);
            }
        });
    }

    public function paymentAttempt()
    {
        return $this->belongsTo(PaymentAttempt::class);
    }

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'requested_by_admin_id');
    }

    public function processedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'processed_by_admin_id');
    }
}
