<?php

namespace App\Models;

use App\Models\Pandit\Pandit;
use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REJECTED = 'rejected';

    public const RESOLUTION_REFUND_USER = 'refund_user';
    public const RESOLUTION_PANDIT_FAVOUR = 'pandit_favour';

    protected $fillable = [
        'disputable_type',
        'disputable_id',
        'user_id',
        'pandit_id',
        'reason',
        'description',
        'pandit_response',
        'admin_review_note',
        'status',
        'resolution',
        'resolved_by_admin_id',
        'resolved_at',
        'payment_refund_id',
        'opened_at',
        'pandit_responded_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'pandit_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
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

    public function resolvedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'resolved_by_admin_id');
    }

    public function paymentRefund()
    {
        return $this->belongsTo(PaymentRefund::class);
    }
}
