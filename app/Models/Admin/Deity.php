<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'featured_image',
        'temple_background_image',

        'aarti_video',

        'primary_color',
        'secondary_color',
        'glow_color',

        'mantra_audio_id',
        'aarti_audio_id',
        'ambient_audio_id',

        'particle_style',
        'flame_style',

        'seo_title',
        'seo_description',

        'status',
    ];

    public function fixedDiyas()
    {
        return $this->hasMany(Diya::class, 'fixed_deity_id');
    }

    public function ambientAudio()
    {
        return $this->belongsTo(
            Audio::class,
            'ambient_audio_id'
        );
    }

    public function mantraAudio()
    {
        return $this->belongsTo(
            Audio::class,
            'mantra_audio_id'
        );
    }

    public function aartiAudio()
    {
        return $this->belongsTo(
            Audio::class,
            'aarti_audio_id'
        );
    }
}