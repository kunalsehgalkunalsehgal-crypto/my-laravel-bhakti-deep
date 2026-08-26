<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Audio extends Model
{
    use SoftDeletes;

    protected $table = 'audio_library';

    protected $fillable = ['deity_id', 'title', 'slug', 'category', 'audio_file', 'status'];

    public function deity()
    {
        return $this->belongsTo(Deity::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function fileUrl(): ?string
    {
        if (!$this->audio_file) {
            return null;
        }

        return asset('storage/'.$this->audio_file);
    }
}
