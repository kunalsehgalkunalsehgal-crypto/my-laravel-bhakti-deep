<?php

namespace App\Models\Pandit;

use Illuminate\Database\Eloquent\Model;

class PanditDocument extends Model
{
    protected $fillable = [
        'pandit_id',
        'government_id_type',
        'government_id_file',
        'pan_number',
        'pan_card_file',
        'address_proof',
        'qualification_certificate',
        'pooja_photos',
        'hawan_photos',
        'mantra_chanting_sample',
        'hawan_performance_video',
        'verification_status',
    ];

    protected $casts = [
        'pooja_photos' => 'array',
        'hawan_photos' => 'array',
    ];

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }
}