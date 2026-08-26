<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deity extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'short_description', 'description', 'featured_image', 'seo_title', 'seo_description', 'status'];

    public function fixedDiyas()
    {
        return $this->hasMany(Diya::class, 'fixed_deity_id');
    }
}
