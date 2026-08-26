<?php

namespace App\Models\Pandit;

use Illuminate\Database\Eloquent\Model;

class PanditService extends Model
{
    protected $fillable = [
        'pandit_id',
        'service_type',
        'service_name',
        'hawan_id',
        'pooja_id',
        'experience_years',
        'approx_performed',
        'duration_minutes',
        'status',
    ];

    public function setServiceTypeAttribute($value): void
    {
        $this->attributes['service_type'] = strtolower(trim((string) $value));
    }

    public function setServiceNameAttribute($value): void
    {
        $this->attributes['service_name'] = trim((string) $value);
    }

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }

    public function hawan()
    {
        return $this->belongsTo(\App\Models\Admin\Hawan::class);
    }

    public function pooja()
    {
        return $this->belongsTo(\App\Models\Admin\Pooja::class);
    }
}
