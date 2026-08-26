<?php

namespace App\Models\Pandit;

use Illuminate\Database\Eloquent\Model;

class PanditMessage extends Model
{
    protected $fillable = ['pandit_id', 'sender', 'message', 'is_read'];
}
