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
    ];

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }
}