<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'blog_category_id',
        'category_id',
        'author_admin_id',
        'title',
        'slug',
        'focus_keyword',
        'excerpt',
        'content',
        'featured_image',
        'image_alt',
        'tags',
        'author_name',
        'canonical_url',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'og_image',
        'schema_markup',
        'faqs',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'schema_markup' => 'array',
            'tags' => 'array',
            'faqs' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'author_admin_id');
    }
}
