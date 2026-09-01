<?php

namespace App\Models;

use App\Models\Admin\Admin;
use App\Models\Pandit\Pandit;
use Illuminate\Database\Eloquent\Model;

class SessionCompletionProof extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_NEEDS_REVIEW = 'needs_review';

    protected $fillable = [
        'session_type',
        'session_id',
        'pandit_id',
        'user_id',
        'proof_type',
        'file_path',
        'external_url',
        'notes',
        'status',
        'reviewed_by_admin_id',
        'submitted_at',
        'reviewed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function session()
    {
        return $this->morphTo(__FUNCTION__, 'session_type', 'session_id');
    }

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }
}
