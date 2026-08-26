<?php

namespace App\Models\Pandit;

use Illuminate\Database\Eloquent\Model;

class PanditAvailabilitySetting extends Model
{
    protected $fillable = [
        'pandit_id',
        'accept_new_bookings',
        'advance_booking_days',
        'day_statuses',
        'offline_service_available',
        'service_city',
        'travel_radius_km',
        'other_service_cities',
    ];

    protected $casts = [
        'accept_new_bookings' => 'boolean',
        'day_statuses' => 'array',
        'offline_service_available' => 'boolean',
        'other_service_cities' => 'array',
    ];

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }
}
