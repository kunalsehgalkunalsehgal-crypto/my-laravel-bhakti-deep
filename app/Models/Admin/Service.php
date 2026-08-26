<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_category_id',
        'deity_id',
        'name',
        'slug',
        'short_description',
        'full_description',
        'featured_image',
        'price',
        'donation_type',
        'service_theme',
        'seo_title',
        'seo_description',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function deity()
    {
        return $this->belongsTo(Deity::class);
    }
}
