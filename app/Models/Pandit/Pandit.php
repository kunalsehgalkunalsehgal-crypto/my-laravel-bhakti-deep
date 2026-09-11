<?php

namespace App\Models\Pandit;

// use Illuminate\Database\Eloquent\Model;

// class Pandit extends Model
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pandit extends Authenticatable
{
    protected $fillable = [
        'user_id',
        'full_name',
        'pandit_name',
        'profile_photo',
        'mobile',
        'whatsapp_number',
        'email',
        'date_of_birth',
        'gender',
        'full_address',
        'city',
        'state',
        'country',
        'total_experience_years',
        'about',
        'associated_with_institution',
        'institution_name',
        'position_role',
        'institution_city',
        'is_independent',
        'status',
        'admin_remark',
        'reviewed_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'associated_with_institution' => 'boolean',
        'is_independent' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function qualification()
    {
        return $this->hasOne(PanditQualification::class);
    }

    public function services()
    {
        return $this->hasMany(PanditService::class);
    }

    public function matchingService(string $serviceType, $serviceNames = []): ?PanditService
    {
        $names = collect($serviceNames)
            ->filter(fn ($name) => filled($name))
            ->map(fn ($name) => trim((string) $name))
            ->unique()
            ->values();

        $services = $this->relationLoaded('services') ? $this->services : $this->services()->get();
        $typedServices = $services->filter(fn (PanditService $service) => $service->service_type === $serviceType);

        if ($names->isNotEmpty()) {
            $matchedService = $typedServices->first(fn (PanditService $service) => $names->contains($service->service_name));

            if ($matchedService) {
                return $matchedService;
            }
        }

        return $typedServices->first();
    }

    public function experienceYearsFor(?PanditService $service = null): int
    {
        if ($service?->experience_years !== null && (int) $service->experience_years > 0) {
            return (int) $service->experience_years;
        }

        return (int) ($this->total_experience_years ?: 0);
    }

    public function availabilitySlots()
    {
        return $this->hasMany(PanditAvailabilitySlot::class);
    }

    public function availabilitySetting()
    {
        return $this->hasOne(PanditAvailabilitySetting::class);
    }

    public function languages()
    {
        return $this->hasMany(PanditLanguage::class);
    }

    public function onlineSetup()
    {
        return $this->hasOne(PanditOnlineSetup::class);
    }

    public function document()
    {
        return $this->hasOne(PanditDocument::class);
    }

    public function references()
    {
        return $this->hasMany(PanditReference::class);
    }

    public function bankDetail()
    {
        return $this->hasOne(PanditBankDetail::class);
    }

    public function messages()
    {
        return $this->hasMany(\App\Models\Pandit\PanditMessage::class);
    }

    public function hawanBookings()
    {
        return $this->hasMany(\App\Models\Admin\HawanSession::class);
    }

    public function poojaBookings()
    {
        return $this->hasMany(\App\Models\Admin\PoojaSession::class);
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }
}
