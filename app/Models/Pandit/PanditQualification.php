<?php

namespace App\Models\Pandit;

use Illuminate\Database\Eloquent\Model;

class PanditQualification extends Model
{
    protected $fillable = [
        'pandit_id',
        'highest_qualification',
        'course_name',
        'institute_name',
        'guru_name',
        'completion_year',
        'certificate_number',
        'certificate_file',
        'sampradaya',
        'ritual_tradition',
        'sanskrit_level',
        'knowledge_areas',
    ];

    protected $casts = [
        'knowledge_areas' => 'array',
    ];

    public function pandit()
    {
        return $this->belongsTo(Pandit::class);
    }
}