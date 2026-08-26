<?php

namespace App\Models\Pandit;

use Illuminate\Database\Eloquent\Model;

class PanditNotification extends Model
{
    protected $fillable = ['pandit_id', 'title', 'message', 'is_read'];
}
