<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanditZoomConnection extends Model
{
    protected $fillable = [
        'pandit_id',
        'zoom_user_id',
        'zoom_email',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'connected_at',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'connected_at' => 'datetime',
    ];
}