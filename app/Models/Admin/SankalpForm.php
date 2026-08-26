<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SankalpForm extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'mobile',
        'gotra',
        'dob',
        'birth_time',
        'birth_place',
        'father_name',
        'mother_name',
        'spouse_name',
        'family_names',
        'purpose',
        'mannokamna',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['dob' => 'date', 'metadata' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
