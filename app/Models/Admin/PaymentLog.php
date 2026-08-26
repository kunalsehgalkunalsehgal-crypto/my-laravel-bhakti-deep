<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = ['donation_id', 'user_id', 'gateway', 'order_id', 'payment_id', 'status', 'amount', 'payload'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'amount' => 'decimal:2'];
    }
}
