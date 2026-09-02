<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisputeEvidence extends Model
{
    protected $table = 'dispute_evidences';

    protected $fillable = [
        'dispute_id',
        'uploaded_by_type',
        'uploaded_by_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }

    public function uploadedBy()
    {
        return $this->morphTo(__FUNCTION__, 'uploaded_by_type', 'uploaded_by_id');
    }
}
