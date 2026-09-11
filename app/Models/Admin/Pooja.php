<?php

namespace App\Models\Admin;

use App\Support\WeeklyBookingAvailability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pooja extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'full_description',
        'featured_image',
        'base_price',
        'duration',
        'mode',
        'benefits',
        'included_items',
        'session_timeline',
        'donation_options',
        'available_slots',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'included_items' => 'array',
            'session_timeline' => 'array',
            'donation_options' => 'array',
            'available_slots' => 'array',
            'is_featured' => 'boolean',
            'base_price' => 'decimal:2',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function imageUrl(): string
    {
        return asset($this->imagePath());
    }

    public function imagePath(): string
    {
        if (!$this->featured_image) {
            return 'assets/lakshmi-hero.jpg';
        }

        if (str_starts_with($this->featured_image, 'assets/')) {
            return $this->featured_image;
        }

        return 'storage/'.$this->featured_image;
    }

    public function toBookingArray(): array
    {
        $availability = $this->available_slots ?: ['7:00 AM - 8:00 AM', '12:00 PM - 1:00 PM', '6:00 PM - 7:00 PM'];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'featured_image' => $this->imagePath(),
            'base_price' => (int) $this->base_price,
            'duration' => $this->duration,
            'mode' => $this->mode,
            'benefits' => $this->benefits ?: [],
            'included_items' => $this->included_items ?: [],
            'session_timeline' => $this->session_timeline ?: [],
            'donation_options' => $this->donation_options ?: [101, 251, 501, 1100],
            'available_slots' => WeeklyBookingAvailability::allLabels($availability),
            'weekly_availability' => WeeklyBookingAvailability::normalize($availability),
        ];
    }

    public function toCardArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'purpose' => $this->short_description,
            'price' => number_format((int) $this->base_price),
            'duration' => $this->duration,
            'featured' => $this->is_featured,
            'benefits' => $this->benefits ?: [],
        ];
    }
}
