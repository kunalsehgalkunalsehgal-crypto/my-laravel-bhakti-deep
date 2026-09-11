<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'booking_type',
        'booking_id',
        'user_id',
        'pandit_id',
        'review_by',
        'rating',
        'comment',
        'image_path',
    ];
}
