<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['admin_id', 'action', 'module', 'description', 'ip_address', 'user_agent', 'created_at'];
}
