<?php

namespace App\Models\Pandit;

use Illuminate\Database\Eloquent\Model;

class PanditOnlineSetup extends Model
{
    protected $fillable = [
        'pandit_id',
        'online_hawan',
        'online_pooja',
        'stable_internet',
        'platforms',
        'devices',
        'equipment',
    ];

    protected $casts = [
        'online_hawan' => 'boolean',
        'online_pooja' => 'boolean',
        'stable_internet' => 'boolean',
        'platforms' => 'array',
        'devices' => 'array',
        'equipment' => 'array',
    ];

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }
}