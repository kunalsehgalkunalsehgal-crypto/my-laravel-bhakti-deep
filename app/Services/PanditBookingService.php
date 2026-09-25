<?php

namespace App\Services;

use App\Models\Admin\HawanSession;
use App\Models\Admin\Donation;
use App\Models\Admin\PaymentLog;
use App\Models\Admin\PoojaSession;
use App\Models\PaymentAttempt;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditService;
use App\Support\WeeklyBookingAvailability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PanditBookingService
{
    public const SAMUHIK_HAWAN_MAX_PRIMARY_BOOKINGS = 5;
    public const PAYMENT_HOLD_MINUTES = 10;

    public const BOOKING_STATUSES = ['pending', 'scheduled', 'confirmed', 'active', 'completed', 'cancelled', 'cancelled_by_pandit'];

    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    public function serviceNames(string $name, string $type): array
    {
        $suffix = $type === 'pooja' ? ' Pooja' : ' Hawan';
        $withoutSuffix = trim(Str::replaceLast($suffix, '', $name));

        return collect([$name, $withoutSuffix])
            ->filter()
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->values()
            ->all();
    }

    public function slotTimes(string $slot): array
    {
        $parts = preg_split('/\s*-\s*/', trim($slot));
        $start = $parts[0] ?? null;
        $end = $parts[1] ?? null;

        if (!$start || !$end) {
            throw ValidationException::withMessages(['slot' => 'Please select a valid time slot.']);
        }

        $startTime = Carbon::parse($start)->format('H:i:s');
        $endTime = Carbon::parse($end)->format('H:i:s');

        if ($startTime >= $endTime) {
            throw ValidationException::withMessages(['slot' => 'Slot end time must be after start time.']);
        }

        return ['start' => $startTime, 'end' => $endTime];
    }

    public function ensureRitualSlot(Model $ritual, string $slot, ?string $bookingDate = null): void
    {
        $slots = $ritual->available_slots ?: ($ritual instanceof \App\Models\Admin\Pooja ? ['7:00 AM - 8:00 AM', '12:00 PM - 1:00 PM', '6:00 PM - 7:00 PM'] : []);

        if (!$slots) {
            return;
        }

        if (!WeeklyBookingAvailability::allows($slots, $slot, $bookingDate)) {
            throw ValidationException::withMessages(['slot' => 'Selected slot is no longer available for this service.']);
        }
    }

    public function approvedService(Pandit $pandit, string $serviceType, iterable $serviceNames, ?int $serviceId = null, ?int $ritualId = null): ?PanditService
    {
        $services = $pandit->relationLoaded('services') ? $pandit->services : $pandit->services()->get();
        $names = collect($serviceNames)->filter()->values();
        $ritualColumn = $serviceType === 'pooja' ? 'pooja_id' : 'hawan_id';

        $query = $services
            ->where('service_type', $serviceType)
            ->where('status', 'approved');

        if ($serviceId) {
            $service = $query->firstWhere('id', $serviceId);

            if ($service && (!$ritualId || (int) $service->{$ritualColumn} === (int) $ritualId || $names->contains($service->service_name))) {
                return $service;
            }
        }

        if ($ritualId) {
            $service = $query->first(fn (PanditService $service) => (int) $service->{$ritualColumn} === (int) $ritualId);

            if ($service) {
                return $service;
            }
        }

        return $query->first(fn (PanditService $service) => $names->contains($service->service_name));
    }

    public function ensurePanditCanServe(
        int $panditId,
        string $serviceType,
        array $serviceNames,
        string $bookingDate,
        string $slot,
        string $bookingMode = 'online',
        ?int $panditServiceId = null,
        ?int $ritualId = null,
        ?string $state = null,
        ?string $city = null
    ): array {
        $slotTimes = $this->slotTimes($slot);
        if (Carbon::parse($bookingDate)->startOfDay()->lt(today())) {
            throw ValidationException::withMessages(['booking_date' => 'Booking date cannot be in the past.']);
        }

        $day = Carbon::parse($bookingDate)->format('l');
        $onlineColumn = $serviceType === 'pooja' ? 'online_pooja' : 'online_hawan';

        $pandit = Pandit::with(['services', 'availabilitySlots', 'availabilitySetting', 'onlineSetup'])
            ->where('status', 'verified')
            ->find($panditId);

        if (!$pandit) {
            throw ValidationException::withMessages(['pandit_id' => 'Selected pandit is no longer available.']);
        }

        $service = $this->approvedService($pandit, $serviceType, $serviceNames, $panditServiceId, $ritualId);

        if (!$service) {
            throw ValidationException::withMessages(['pandit_id' => 'Selected pandit is not approved for this service.']);
        }

        $acceptsBookings = !$pandit->availabilitySetting || $pandit->availabilitySetting->accept_new_bookings;

        if (!$acceptsBookings) {
            throw ValidationException::withMessages(['pandit_id' => 'Selected pandit is not accepting new bookings.']);
        }

        if ($bookingMode === 'online' && !$pandit->onlineSetup?->{$onlineColumn}) {
            throw ValidationException::withMessages(['pandit_id' => 'Selected pandit is not available online for this service.']);
        }

        if ($bookingMode === 'offline') {
            $offlineColumn = $serviceType === 'pooja' ? 'offline_pooja' : 'offline_hawan';
            $setting = $pandit->availabilitySetting;

            if (blank($state) || blank($city)) {
                throw ValidationException::withMessages(['city' => 'Please select state and city for offline booking.']);
            }

            if (!$setting?->{$offlineColumn}) {
                throw ValidationException::withMessages(['pandit_id' => 'Selected pandit is not available offline for this service.']);
            }

            if (filled($state) && $setting->service_state !== $state) {
                throw ValidationException::withMessages(['state' => 'Selected pandit does not provide service in this state.']);
            }

            if (filled($city) && $setting->service_city !== $city && !in_array($city, $setting->other_service_cities ?? [], true)) {
                throw ValidationException::withMessages(['city' => 'Selected pandit does not provide service in this city.']);
            }
        }

        $hasAvailability = $pandit->availabilitySlots
            ->where('day', $day)
            ->where('is_available', true)
            ->contains(fn ($availability) => $availability->start_time <= $slotTimes['start'] && $availability->end_time >= $slotTimes['end']);

        if (!$hasAvailability) {
            throw ValidationException::withMessages(['slot' => 'Selected pandit is not available for this date and time.']);
        }

        return [$pandit, $service, $slotTimes];
    }

    public function hasOverlappingBooking(int $panditId, string $bookingDate, string $startTime, string $endTime, ?int $ignoreId = null, ?string $ignoreType = null): bool
    {
        foreach (['hawan' => HawanSession::class, 'pooja' => PoojaSession::class] as $type => $model) {
            $query = $model::query()
                ->where('pandit_id', $panditId)
                ->whereDate('booking_date', $bookingDate)
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->where(fn ($active) => $this->activePaymentOrHold($active))
                ->where(function ($overlap) use ($startTime, $endTime) {
                    $overlap
                        ->where(function ($time) use ($startTime, $endTime) {
                            $time->whereNotNull('slot_start_time')
                                ->whereNotNull('slot_end_time')
                                ->where('slot_start_time', '<', $endTime)
                                ->where('slot_end_time', '>', $startTime);
                        })
                        ->orWhere(function ($legacy) use ($startTime, $endTime) {
                            $legacy->whereNull('slot_start_time')
                                ->where('slot', $this->slotLabel($startTime, $endTime));
                        });
                });

            if ($ignoreId && $ignoreType === $type) {
                $query->whereKeyNot($ignoreId);
            }

            if ($query->exists()) {
                return true;
            }
        }

        return false;
    }

    public function hasBlockingHawanBooking(
        int $panditId,
        int $hawanId,
        string $hawanType,
        string $bookingDate,
        string $startTime,
        string $endTime
    ): bool {
        if ($this->hasOverlappingPoojaBooking($panditId, $bookingDate, $startTime, $endTime)) {
            return true;
        }

        $query = $this->overlappingHawanQuery($panditId, $bookingDate, $startTime, $endTime);

        if ($hawanType !== 'samuhik') {
            return $query->exists();
        }

        return $query
            ->where(function ($blocking) use ($hawanId, $startTime, $endTime) {
                $blocking
                    ->where('hawan_type', '!=', 'samuhik')
                    ->orWhereNull('hawan_type')
                    ->orWhere('ritual_id', '!=', $hawanId)
                    ->orWhereNull('ritual_id')
                    ->orWhere('slot_start_time', '!=', $startTime)
                    ->orWhereNull('slot_start_time')
                    ->orWhere('slot_end_time', '!=', $endTime)
                    ->orWhereNull('slot_end_time');
            })
            ->exists();
    }

    public function samuhikHawanPrimaryBookingCount(int $panditId, int $hawanId, string $bookingDate, string $startTime, string $endTime): int
    {
        return HawanSession::query()
            ->where('pandit_id', $panditId)
            ->where('ritual_id', $hawanId)
            ->where('hawan_type', 'samuhik')
            ->whereDate('booking_date', $bookingDate)
            ->where('slot_start_time', $startTime)
            ->where('slot_end_time', $endTime)
            ->where('status', '!=', 'cancelled')
            ->where(fn ($active) => $this->activePaymentOrHold($active))
            ->count();
    }

    public function samuhikHawanSessionHasCapacity(int $panditId, int $hawanId, string $bookingDate, string $startTime, string $endTime): bool
    {
        return $this->samuhikHawanPrimaryBookingCount($panditId, $hawanId, $bookingDate, $startTime, $endTime)
            < self::SAMUHIK_HAWAN_MAX_PRIMARY_BOOKINGS;
    }

    public function packageAmount(Model $ritual, string $packageName, string $serviceType): float
    {
        $base = (float) $ritual->base_price;
        $normalized = Str::lower($packageName);

        return match (true) {
            str_contains($normalized, 'special') => $base + 2500,
            str_contains($normalized, 'premium') => $base + 1000,
            default => $base,
        };
    }

    public function canAccessPrivateSession(Model $session, Request $request): bool
    {
        if (auth('admin')->check()) {
            return true;
        }

        if ($session->user_id && auth()->check() && (int) auth()->id() === (int) $session->user_id) {
            return true;
        }

        if ($session->pandit_id && auth('pandit')->check() && (int) auth('pandit')->id() === (int) $session->pandit_id) {
            return true;
        }

        $token = (string) $request->query('token', '');

        return $token !== ''
            && $session->live_session_token
            && hash_equals((string) $session->live_session_token, $token);
    }

    public function holdTimes(): array
    {
        $start = now();

        return [$start, $start->copy()->addMinutes(self::PAYMENT_HOLD_MINUTES)];
    }

    public function createPendingPayment(Model $session, float $amount, array $meta): PaymentAttempt
    {
        $holdStart = $session->payment_hold_started_at ?: now();
        $holdEnd = $session->payment_hold_expires_at ?: $holdStart->copy()->addMinutes(self::PAYMENT_HOLD_MINUTES);
        $meta = app(PayoutAmountCalculator::class)->freeze($amount, $meta);

        $donation = Donation::create([
            'user_id' => $session->user_id,
            'payment_purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'session_type' => get_class($session),
            'session_id' => $session->id,
            'amount' => $amount,
            'currency' => 'INR',
            'donor_name' => $meta['donor_name'] ?? null,
            'donor_mobile' => $meta['donor_mobile'] ?? null,
            'payment_status' => 'pending',
        ]);

        $attempt = PaymentAttempt::create([
            'user_id' => $session->user_id,
            'donation_id' => $donation->id,
            'payable_type' => get_class($session),
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'amount' => $amount,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PENDING,
            'hold_started_at' => $holdStart,
            'hold_expires_at' => $holdEnd,
            'metadata' => $meta,
        ]);

        $donation->update(['latest_payment_attempt_id' => $attempt->id]);
        $session->update(['latest_payment_attempt_id' => $attempt->id]);

        PaymentLog::create([
            'donation_id' => $donation->id,
            'payment_attempt_id' => $attempt->id,
            'loggable_type' => get_class($session),
            'loggable_id' => $session->id,
            'user_id' => $session->user_id,
            'gateway' => 'none',
            'event_type' => 'payment_hold_created',
            'status' => 'pending',
            'occurred_at' => $holdStart,
            'amount' => $amount,
            'payload' => $meta,
        ]);

        return $attempt;
    }

    public function token(): string
    {
        return Str::random(48);
    }

    public function routeWithToken(string $routeName, array $parameters, ?string $token): string
    {
        if ($token) {
            $parameters['token'] = $token;
        }

        return route($routeName, $parameters);
    }

    private function slotLabel(string $startTime, string $endTime): string
    {
        return Carbon::createFromFormat('H:i:s', $startTime)->format('g:i A')
            .' - '
            .Carbon::createFromFormat('H:i:s', $endTime)->format('g:i A');
    }

    private function overlappingHawanQuery(int $panditId, string $bookingDate, string $startTime, string $endTime)
    {
        return HawanSession::query()
            ->where('pandit_id', $panditId)
            ->whereDate('booking_date', $bookingDate)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(fn ($active) => $this->activePaymentOrHold($active))
            ->where(function ($overlap) use ($startTime, $endTime) {
                $overlap
                    ->where(function ($time) use ($startTime, $endTime) {
                        $time->whereNotNull('slot_start_time')
                            ->whereNotNull('slot_end_time')
                            ->where('slot_start_time', '<', $endTime)
                            ->where('slot_end_time', '>', $startTime);
                    })
                    ->orWhere(function ($legacy) use ($startTime, $endTime) {
                        $legacy->whereNull('slot_start_time')
                            ->where('slot', $this->slotLabel($startTime, $endTime));
                    });
            });
    }

    private function hasOverlappingPoojaBooking(int $panditId, string $bookingDate, string $startTime, string $endTime): bool
    {
        return PoojaSession::query()
            ->where('pandit_id', $panditId)
            ->whereDate('booking_date', $bookingDate)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(fn ($active) => $this->activePaymentOrHold($active))
            ->where(function ($overlap) use ($startTime, $endTime) {
                $overlap
                    ->where(function ($time) use ($startTime, $endTime) {
                        $time->whereNotNull('slot_start_time')
                            ->whereNotNull('slot_end_time')
                            ->where('slot_start_time', '<', $endTime)
                            ->where('slot_end_time', '>', $startTime);
                    })
                    ->orWhere(function ($legacy) use ($startTime, $endTime) {
                        $legacy->whereNull('slot_start_time')
                            ->where('slot', $this->slotLabel($startTime, $endTime));
                    });
            })
            ->exists();
    }

    private function activePaymentOrHold($query)
    {
        return $query
            ->where('payment_status', 'paid')
            ->orWhere(function ($hold) {
                $hold->where('payment_status', 'pending')
                    ->where('payment_hold_expires_at', '>', now());
            })
            ->orWhere(function ($cancelled) {
                $cancelled->where('status', 'cancelled_by_pandit')
                    ->where(function ($slot) {
                        $slot->whereDate('booking_date', '>', today())
                            ->orWhere(function ($today) {
                                $today->whereDate('booking_date', today())
                                    ->where(function ($time) {
                                        $time->whereNull('slot_end_time')
                                            ->orWhere('slot_end_time', '>', now()->format('H:i:s'));
                                    });
                            });
                    });
            });
    }
}
