<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewImageOtp extends Model
{
    protected $fillable = [
        'user_type',
        'user_id',
        'email',
        'otp',
        'purpose',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
}
