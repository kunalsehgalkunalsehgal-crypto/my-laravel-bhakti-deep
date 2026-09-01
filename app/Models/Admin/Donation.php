<?php

namespace App\Models\Admin;

use App\Models\PaymentAttempt;
use App\Models\PaymentDispute;
use App\Models\PaymentRefund;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_id',
        'payment_purpose',
        'session_type',
        'session_id',
        'amount',
        'refunded_amount',
        'disputed_amount',
        'currency',
        'donor_name',
        'donor_email',
        'donor_mobile',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_status',
        'latest_payment_attempt_id',
        'receipt_number',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'disputed_amount' => 'decimal:2',
            'paid_at' => 'datetime',
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

    public function session()
    {
        return $this->morphTo(__FUNCTION__, 'session_type', 'session_id');
    }

    public function latestPaymentAttempt()
    {
        return $this->belongsTo(PaymentAttempt::class, 'latest_payment_attempt_id');
    }

    public function paymentAttempts()
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function disputes()
    {
        return $this->hasMany(PaymentDispute::class);
    }
}
