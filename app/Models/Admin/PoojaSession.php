<?php

namespace App\Models\Admin;

use App\Models\Admin\Concerns\SessionModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoojaSession extends Model
{
    use SessionModel, SoftDeletes;

    public function digitalAudio()
    {
        return $this->belongsTo(Audio::class, 'digital_audio_id');
    }
}
