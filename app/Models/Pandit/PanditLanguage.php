<?php

namespace App\Models\Pandit;

use Illuminate\Database\Eloquent\Model;

class PanditLanguage extends Model
{
    protected $fillable = [
        'pandit_id',
        'language',
        'can_speak',
        'can_conduct_ritual',
    ];

    protected $casts = [
        'can_speak' => 'boolean',
        'can_conduct_ritual' => 'boolean',
    ];

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }
}