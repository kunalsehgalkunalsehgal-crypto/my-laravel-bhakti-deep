<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class WeeklyBookingAvailability
{
    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public static function normalize($value, bool $strict = false): array
    {
        $value = is_array($value) ? $value : [];
        $structured = array_key_exists('days', $value) || array_key_exists('accept_new_bookings', $value);
        $legacySlots = $structured ? [] : self::legacySlots($value);

        $schedule = [
            'accept_new_bookings' => self::bool($value['accept_new_bookings'] ?? true),
            'days' => [],
        ];

        foreach (self::DAYS as $day) {
            $data = $structured ? ($value['days'][$day] ?? []) : [];
            $available = $structured ? self::bool($data['available'] ?? false) : count($legacySlots) > 0;
            $slots = $structured ? self::daySlots($day, $data['slots'] ?? [], $available && $schedule['accept_new_bookings'], $strict) : $legacySlots;
            $schedule['days'][$day] = ['available' => $available, 'slots' => $available ? $slots : []];
        }

        return $schedule;
    }

    public static function labelsForDate($value, ?string $date): array
    {
        $schedule = self::normalize($value);

        if (!$schedule['accept_new_bookings'] || !$date) {
            return [];
        }

        $day = Carbon::parse($date)->format('l');
        $data = $schedule['days'][$day] ?? ['available' => false, 'slots' => []];

        return $data['available'] ? self::labels($data['slots']) : [];
    }

    public static function allLabels($value): array
    {
        return collect(self::normalize($value)['days'])
            ->flatMap(fn ($day) => $day['available'] ? self::labels($day['slots']) : [])
            ->unique()
            ->values()
            ->all();
    }

    public static function allows($value, string $slot, ?string $date = null): bool
    {
        $schedule = self::normalize($value);

        if (!$schedule['accept_new_bookings']) {
            return false;
        }

        $labels = $date ? self::labelsForDate($schedule, $date) : self::allLabels($schedule);

        return in_array($slot, $labels, true);
    }

    private static function daySlots(string $day, array $slots, bool $required, bool $strict): array
    {
        $normalized = collect($slots)->map(function ($slot) use ($day, $strict) {
            $from = $slot['from'] ?? null;
            $to = $slot['to'] ?? null;

            if (!$from && !$to) {
                return null;
            }

            if (!$from || !$to || $from >= $to) {
                if ($strict) {
                    throw ValidationException::withMessages(['available_slots' => "Each slot on {$day} must have a valid From and To time."]);
                }

                return null;
            }

            return ['from' => $from, 'to' => $to];
        })->filter()->sortBy('from')->values();

        for ($i = 1; $i < $normalized->count(); $i++) {
            if ($normalized[$i - 1]['to'] > $normalized[$i]['from']) {
                throw ValidationException::withMessages(['available_slots' => "Booking availability slots overlap on {$day}."]);
            }
        }

        if ($strict && $required && $normalized->isEmpty()) {
            throw ValidationException::withMessages(['available_slots' => "Add at least one time slot for {$day}, or mark it unavailable."]);
        }

        return $normalized->all();
    }

    private static function legacySlots(array $slots): array
    {
        return collect($slots)->filter(fn ($slot) => is_string($slot) && str_contains($slot, ' - '))
            ->map(function ($slot) {
                [$from, $to] = array_map('trim', explode(' - ', $slot, 2));
                return [
                    'from' => Carbon::parse($from)->format('H:i'),
                    'to' => Carbon::parse($to)->format('H:i'),
                ];
            })->values()->all();
    }

    private static function labels(array $slots): array
    {
        return collect($slots)->map(fn ($slot) => Carbon::createFromFormat('H:i', $slot['from'])->format('g:i A').' - '.Carbon::createFromFormat('H:i', $slot['to'])->format('g:i A'))->all();
    }

    private static function bool($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
