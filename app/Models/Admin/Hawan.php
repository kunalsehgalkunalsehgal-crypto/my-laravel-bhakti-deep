<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hawan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'full_description',
        'featured_image',
        'base_price',
        'samuhik_hawan_enabled',
        'samuhik_hawan_title',
        'samuhik_hawan_description',
        'samuhik_hawan_price',
        'special_hawan_enabled',
        'special_hawan_title',
        'special_hawan_description',
        'special_hawan_price',
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
            'samuhik_hawan_enabled' => 'boolean',
            'samuhik_hawan_price' => 'decimal:2',
            'special_hawan_enabled' => 'boolean',
            'special_hawan_price' => 'decimal:2',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithEnabledHawanTypes($query)
    {
        return $query->where(function ($builder) {
            $builder->where('samuhik_hawan_enabled', true)
                ->orWhere('special_hawan_enabled', true);
        });
    }

    public function imageUrl(): string
    {
        return asset($this->imagePath());
    }

    public function imagePath(): string
    {
        if (!$this->featured_image) {
            return 'assets/havan-live.jpg';
        }

        if (str_starts_with($this->featured_image, 'assets/')) {
            return $this->featured_image;
        }

        return 'storage/'.$this->featured_image;
    }

    public function toBookingArray(): array
    {
        $enabledTypes = $this->enabledHawanTypes();
        $displayPrice = collect($enabledTypes)->min('price') ?? (float) $this->base_price;

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
            'donation_options' => $this->donation_options ?: [501, 1100, 2100, 5100],
            'available_slots' => $this->available_slots ?: [],
            'types' => $enabledTypes,
        ];
    }

    public function toCardArray(): array
    {
        $enabledTypes = $this->enabledHawanTypes();
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

    public function enabledHawanTypes(): array
    {
        return collect([
            $this->hawanTypeData('samuhik'),
            $this->hawanTypeData('special'),
        ])
            ->filter(fn (array $type) => $type['enabled'])
            ->map(fn (array $type) => collect($type)->except('enabled')->all())
            ->values()
            ->all();
    }

    public function enabledHawanType(string $type): ?array
    {
        return collect($this->enabledHawanTypes())->firstWhere('key', $type);
    }

    private function hawanTypeData(string $type): array
    {
        $prefix = $type === 'samuhik' ? 'samuhik_hawan' : 'special_hawan';
        $fallbackTitle = $type === 'samuhik' ? 'Samuhik Hawan' : 'Special Hawan';
        $fallbackDescription = $type === 'samuhik'
            ? 'Personalized sankalp, live access and digital receipt.'
            : 'Priority slot, extended ritual, family join and replay.';
        $fallbackIcon = $type === 'samuhik' ? 'bi-people' : 'bi-stars';
        $price = (float) ($this->{$prefix.'_price'} ?? $this->base_price ?? 0);

        return [
            'key' => $type,
            'title' => $this->{$prefix.'_title'} ?: $fallbackTitle,
            'description' => $this->{$prefix.'_description'} ?: $fallbackDescription,
            'price' => $price,
            'formatted_price' => number_format((int) $price),
            'icon' => $fallbackIcon,
            'enabled' => (bool) ($this->{$prefix.'_enabled'} ?? false),
        ];
    }
}
