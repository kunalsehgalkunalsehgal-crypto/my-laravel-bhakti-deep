<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Playlist extends Model
{
    use SoftDeletes;

    protected $fillable = ['service_id', 'deity_id', 'name', 'slug', 'description', 'status'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function deity()
    {
        return $this->belongsTo(Deity::class);
    }

    public function audio()
    {
        return $this->belongsToMany(Audio::class, 'audio_playlist', 'playlist_id', 'audio_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
