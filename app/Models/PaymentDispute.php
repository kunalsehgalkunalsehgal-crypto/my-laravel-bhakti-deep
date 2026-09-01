<?php

namespace App\Models;

use App\Models\Admin\Admin;
use App\Models\Admin\Donation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentDispute extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_PAYMENT = 'payment';
    public const TYPE_SERVICE = 'service';
    public const TYPE_NO_SHOW = 'no_show';
    public const TYPE_REFUND = 'refund';

    protected $fillable = [
        'uuid',
        'payment_attempt_id',
        'donation_id',
        'user_id',
        'session_type',
        'session_id',
        'dispute_type',
        'status',
        'amount_disputed',
        'reason',
        'resolution',
        'resolved_by_admin_id',
        'opened_at',
        'resolved_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount_disputed' => 'decimal:2',
            'opened_at' => 'datetime',
            'resolved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentDispute $dispute) {
            $dispute->uuid ??= (string) Str::uuid();
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

    public function session()
    {
        return $this->morphTo(__FUNCTION__, 'session_type', 'session_id');
    }

    public function resolvedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'resolved_by_admin_id');
    }
}
