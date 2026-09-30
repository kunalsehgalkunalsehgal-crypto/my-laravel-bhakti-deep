<?php

namespace App\Models\Admin;

use App\Support\WeeklyBookingAvailability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pooja extends Model
{
    use SoftDeletes;

    protected $attributes = [
        'digital_pooja_access_minutes' => 120,
    ];

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'full_description',
        'featured_image',
        'base_price',
        'live_pooja_enabled',
        'live_pooja_title',
        'live_pooja_description',
        'live_pooja_price',
        'digital_pooja_enabled',
        'digital_pooja_title',
        'digital_pooja_description',
        'digital_pooja_price',
        'digital_pooja_video',
        'digital_pooja_audio_id',
        'digital_pooja_access_minutes',
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
            'live_pooja_enabled' => 'boolean',
            'live_pooja_price' => 'decimal:2',
            'digital_pooja_enabled' => 'boolean',
            'digital_pooja_price' => 'decimal:2',
            'digital_pooja_access_minutes' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithEnabledPoojaTypes($query)
    {
        return $query->where(function ($builder) {
            $builder->where('live_pooja_enabled', true)
                ->orWhere('digital_pooja_enabled', true);
        });
    }

    public function digitalPoojaAudio()
    {
        return $this->belongsTo(Audio::class, 'digital_pooja_audio_id');
    }

    public function imageUrl(): string
    {
        return asset($this->imagePath());
    }

    public function imagePath(): string
    {
        if (! $this->featured_image) {
            return 'assets/lakshmi-hero.jpg';
        }

        if (str_starts_with($this->featured_image, 'assets/')) {
            return $this->featured_image;
        }

        return 'storage/'.$this->featured_image;
    }

    public function toBookingArray(): array
    {
        $enabledTypes = $this->enabledPoojaTypes();
        $displayPrice = collect($enabledTypes)->min('price') ?? (float) $this->base_price;
        $availability = $this->available_slots ?: ['7:00 AM - 8:00 AM', '12:00 PM - 1:00 PM', '6:00 PM - 7:00 PM'];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'featured_image' => $this->imagePath(),
            'base_price' => (int) $this->base_price,
            'display_price' => (int) $displayPrice,
            'duration' => $this->duration,
            'mode' => $this->mode,
            'benefits' => $this->benefits ?: [],
            'included_items' => $this->included_items ?: [],
            'session_timeline' => $this->session_timeline ?: [],
            'donation_options' => $this->donation_options ?: [101, 251, 501, 1100],
            'available_slots' => WeeklyBookingAvailability::allLabels($availability),
            'weekly_availability' => WeeklyBookingAvailability::normalize($availability),
            'types' => $enabledTypes,
        ];
    }

    public function toCardArray(): array
    {
        $enabledTypes = $this->enabledPoojaTypes();
        $displayPrice = collect($enabledTypes)->min('price') ?? (float) $this->base_price;

        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'purpose' => $this->short_description,
            'price' => number_format((int) $displayPrice),
            'duration' => $this->duration,
            'featured' => $this->is_featured,
            'benefits' => $this->benefits ?: [],
            'types' => $enabledTypes,
        ];
    }

    public function enabledPoojaTypes(): array
    {
        return collect([
            $this->poojaTypeData('live'),
            $this->poojaTypeData('digital'),
        ])
            ->filter(fn (array $type) => $type['enabled'])
            ->map(fn (array $type) => collect($type)->except('enabled')->all())
            ->values()
            ->all();
    }

    public function enabledPoojaType(string $type): ?array
    {
        return collect($this->enabledPoojaTypes())->firstWhere('key', $type);
    }

    private function poojaTypeData(string $type): array
    {
        $prefix = $type === 'live' ? 'live_pooja' : 'digital_pooja';
        $fallbackTitle = $type === 'live' ? 'Live Pooja' : 'Digital Pooja';
        $fallbackDescription = $type === 'live'
            ? 'Join the pooja live with your personalized sankalp.'
            : 'Receive a digital video of the pooja performed for your sankalp.';
        $fallbackIcon = $type === 'live' ? 'bi-camera-video' : 'bi-play-circle';
        $price = (float) ($this->{$prefix.'_price'} ?? $this->base_price ?? 0);

        return [
            'key' => $type,
            'title' => $this->{$prefix.'_title'} ?: $fallbackTitle,
            'description' => $this->{$prefix.'_description'} ?: $fallbackDescription,
            'price' => $price,
            'formatted_price' => number_format((int) $price),
            'icon' => $fallbackIcon,
            'video' => $type === 'digital' ? $this->digital_pooja_video : null,
            'enabled' => (bool) ($this->{$prefix.'_enabled'} ?? false),
        ];
    }
}
