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
        'razorpay_linked_account_status',
        'razorpay_payout_enabled',
        'razorpay_verified_at',
        'razorpay_last_error',
    ];

    protected function casts(): array
    {
        return [
            'razorpay_payout_enabled' => 'boolean',
            'razorpay_verified_at' => 'datetime',
        ];
    }

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }
}
