<?php

namespace App\Models;

use App\Models\Admin\Admin;
use App\Models\Pandit\Pandit;
use Illuminate\Database\Eloquent\Model;

class PanditNoShowReport extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_VALID = 'valid';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'session_type',
        'session_id',
        'user_id',
        'pandit_id',
        'reported_by_type',
        'reported_by_id',
        'status',
        'reason',
        'resolution',
        'resolved_by_admin_id',
        'reported_at',
        'resolved_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function session()
    {
        return $this->morphTo(__FUNCTION__, 'session_type', 'session_id');
    }

    public function reportedBy()
    {
        return $this->morphTo(__FUNCTION__, 'reported_by_type', 'reported_by_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }

    public function resolvedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'resolved_by_admin_id');
    }
}
