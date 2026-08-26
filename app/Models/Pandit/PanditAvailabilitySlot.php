<?php

namespace App\Models\Pandit;

use Illuminate\Database\Eloquent\Model;

class PanditAvailabilitySlot extends Model
{
    protected $fillable = [
        'pandit_id',
        'day',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }
}