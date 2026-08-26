<?php

namespace App\Models\Admin;

use App\Models\Admin\Concerns\SessionModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiyaSession extends Model
{
    use SessionModel, SoftDeletes;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeCurrentlyGlowing($query)
    {
        $now = now();

        return $query
            ->where('status', self::STATUS_ACTIVE)
            ->where(function ($time) use ($now) {
                $time->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($time) use ($now) {
                $time->whereNull('end_at')->orWhere('end_at', '>', $now);
            });
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function diya()
    {
        return $this->belongsTo(Diya::class);
    }

    public function deity()
    {
        return $this->belongsTo(Deity::class);
    }
}
