<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diya extends Model
{
    use SoftDeletes;

    public const MODE_FIXED = 'fixed';
    public const MODE_USER_SELECT = 'user_select';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'short_description',
        'full_description',
        'seva_amount',
        'duration',
        'category',
        'deity_selection_mode',
        'fixed_deity_id',
        'mantra_audio_id',
        'mantra_ambience',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'seva_amount' => 'decimal:2',
        ];
    }

    public function fixedDeity()
    {
        return $this->belongsTo(Deity::class, 'fixed_deity_id');
    }

    public function mantraAudio()
    {
        return $this->belongsTo(Audio::class, 'mantra_audio_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isFixedDeity(): bool
    {
        return $this->deity_selection_mode === self::MODE_FIXED;
    }

    public function allowsUserDeitySelection(): bool
    {
        return $this->deity_selection_mode === self::MODE_USER_SELECT;
    }

    public function imageUrl(): ?string
    {
        if (!$this->image) {
            return asset('assets/diya.jpg');
        }

        if (str_starts_with($this->image, 'assets/')) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }

    public function imagePath(): string
    {
        if (!$this->image) {
            return 'assets/diya.jpg';
        }

        if (str_starts_with($this->image, 'assets/')) {
            return $this->image;
        }

        return 'storage/'.$this->image;
    }

    public function toOfferingArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->imagePath(),
            'image_url' => $this->imageUrl(),
            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'seva_amount' => (float) $this->seva_amount,
            'duration' => $this->duration,
            'category' => $this->category,
            'deity_selection_mode' => $this->deity_selection_mode,
            'fixed_deity_id' => $this->fixed_deity_id,
            'fixed_deity_name' => $this->fixedDeity?->name,
            'mantra_audio_id' => $this->mantra_audio_id,
            'mantra_ambience' => $this->mantra_ambience,
        ];
    }
}
