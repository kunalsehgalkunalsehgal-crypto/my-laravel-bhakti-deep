<?php

namespace App\Models\Pandit;

use Illuminate\Database\Eloquent\Model;

class PanditBankDetail extends Model
{
    protected $fillable = [
        'pandit_id',
        'account_holder_name',
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi_id',
        'pan_number',
        'verification_status',
        'razorpay_linked_account_id',
        'razorpay_product_id',
        'razorpay_settlement_account_id',
        'razorpay_linked_account_status',
        'razorpay_bank_verification_status',
        'razorpay_payout_enabled',
        'razorpay_onboarding_idempotency_key',
        'razorpay_verified_at',
        'razorpay_synced_at',
        'razorpay_last_error',
    ];

    protected function casts(): array
    {
        return [
            'razorpay_payout_enabled' => 'boolean',
            'razorpay_verified_at' => 'datetime',
            'razorpay_synced_at' => 'datetime',
        ];
    }

    public function isRazorpayPayoutReady(): bool
    {
        return filled($this->razorpay_linked_account_id)
            && $this->razorpay_linked_account_status === 'activated'
            && $this->razorpay_payout_enabled
            && (! $this->razorpay_bank_verification_status || $this->razorpay_bank_verification_status === 'verified');
    }

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }
}
