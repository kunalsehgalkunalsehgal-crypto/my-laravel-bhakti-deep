<?php

namespace App\Models;

use App\Models\Pandit\Pandit;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'disputable_type',
        'disputable_id',
        'user_id',
        'pandit_id',
        'reason',
        'description',
        'pandit_response',
        'status',
        'opened_at',
        'pandit_responded_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'pandit_responded_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }

    public function disputable()
    {
        return $this->morphTo();
    }

    public function evidences()
    {
        return $this->hasMany(DisputeEvidence::class);
    }
}
