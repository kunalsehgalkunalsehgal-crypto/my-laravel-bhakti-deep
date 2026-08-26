<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'channel', 'message_type', 'recipient', 'subject', 'message', 'delivery_status', 'failure_reason', 'sent_at'];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }
}
