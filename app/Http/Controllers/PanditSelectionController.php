<?php

namespace App\Http\Controllers;

use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditService;
use App\Services\PanditBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PanditSelectionController extends Controller
{
    public function index(Request $request, string $slug)
    {
        return $this->selectionIndex($request, $slug, 'hawan');
    }

    public function poojaIndex(Request $request, string $slug)
    {
        return $this->selectionIndex($request, $slug, 'pooja');
    }

    public function show(Request $request, string $slug, int $pandit)
    {
        return $this->profileShow($request, $slug, $pandit, 'hawan');
    }

    public function poojaShow(Request $request, string $slug, int $pandit)
    {
        return $this->profileShow($request, $slug, $pandit, 'pooja');
    }

    public function select(Request $request, string $slug, int $pandit)
    {
        return $this->selectPandit($request, $slug, $pandit, 'hawan');
    }

    public function poojaSelect(Request $request, string $slug, int $pandit)
    {
        return $this->selectPandit($request, $slug, $pandit, 'pooja');
    }

    private function selectionIndex(Request $request, string $slug, string $serviceType)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'slot' => ['required', 'string'],
            'mode' => ['nullable', 'string'],
            'hawan_type' => ['nullable', 'in:samuhik,special'],
            'language' => ['nullable', 'string'],
            'experience' => ['nullable', 'integer'],
            'qualification' => ['nullable', 'string'],
            'booking_mode' => ['nullable', 'in:online,offline'],
            'city' => ['nullable', 'string'],
            'sort' => ['nullable', 'in:recommended,experience,performed'],
            'view' => ['nullable', 'in:all'],
        ]);

        $hawan = $this->findRitual($slug, $serviceType);
        $slotTimes = app(PanditBookingService::class)->slotTimes($request->slot);
        $day = Carbon::parse($request->date)->format('l');

        $serviceNames = $this->serviceNames($hawan['name'], $serviceType);
        $query = $this->matchingPanditsQuery($request, $serviceNames, $day, $slotTimes, $serviceType, $hawan);
        $query = $this->applyFilters($query, $request, $serviceNames, $serviceType, $hawan);
        $query = $this->applySort($query, $request->input('sort', 'recommended'), $serviceNames, $serviceType, $hawan);

        $recommendedPandits = (clone $query)->limit(4)->get();
        $pandits = $request->view === 'all'
            ? $query->paginate(12)->withQueryString()
            : null;

        $languages = \App\Models\Pandit\PanditLanguage::orderBy('language')->pluck('language')->unique()->values();
        $qualifications = \App\Models\Pandit\PanditQualification::whereNotNull('highest_qualification')->orderBy('highest_qualification')->pluck('highest_qualification')->unique()->values();

        return view('pages.hawan-pandits', compact(
            'hawan',
            'recommendedPandits',
            'pandits',
            'languages',
            'qualifications',
            'serviceType'
        ));
    }

    private function profileShow(Request $request, string $slug, int $pandit, string $serviceType)
    {
        $hawan = $this->findRitual($slug, $serviceType);

        $pandit = Pandit::with([
            'qualification',
            'services',
            'languages',
            'availabilitySlots',
            'availabilitySetting',
            'onlineSetup',
            'document',
        ])
            ->where('status', 'verified')
            ->findOrFail($pandit);

        $serviceNames = $this->serviceNames($hawan['name'], $serviceType);
        $selectedService = app(PanditBookingService::class)->approvedService(
            $pandit,
            $serviceType,
            $serviceNames,
            $request->integer('service_id') ?: null,
            (int) ($hawan['id'] ?? 0) ?: null
        );

        abort_unless($selectedService, 404);

        return view('pages.pandit-public-profile', compact('hawan', 'pandit', 'selectedService', 'serviceType'));
    }

    private function selectPandit(Request $request, string $slug, int $pandit, string $serviceType)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'slot' => ['required', 'string'],
            'mode' => ['nullable', 'string'],
            'hawan_type' => ['nullable', 'in:samuhik,special'],
            'booking_mode' => ['nullable', 'in:online,offline'],
            'pandit_service_id' => ['nullable', 'integer'],
        ]);

        $hawan = $this->findRitual($slug, $serviceType);
        $selectedHawanType = $serviceType === 'hawan'
            ? $this->selectedHawanType($hawan, $request->input('hawan_type'))
            : null;
        $bookingService = app(PanditBookingService::class);
        $serviceNames = $this->serviceNames($hawan['name'], $serviceType);

        [$matchedPandit, $panditService] = $bookingService->ensurePanditCanServe(
            $pandit,
            $serviceType,
            $serviceNames,
            $request->date,
            $request->slot,
            $request->input('booking_mode', 'online'),
            $request->integer('pandit_service_id') ?: null,
            (int) ($hawan['id'] ?? 0) ?: null
        );

        $matchedPandit = $this->matchingPanditsQuery($request, $serviceNames, Carbon::parse($request->date)->format('l'), $bookingService->slotTimes($request->slot), $serviceType, $hawan)
            ->where('pandits.id', $pandit)
            ->first();

        if (!$matchedPandit) {
            return back()->with('error', 'Selected Pandit is not available for this date and time.');
        }

        $sessionKey = $serviceType.'_booking';
        session([
            $sessionKey.'.service_type' => $serviceType,
            $sessionKey.'.service_id' => $hawan['id'] ?? null,
            $sessionKey.'.service_slug' => $hawan['slug'],
            $sessionKey.'.pandit_service_id' => $panditService->id,
            $sessionKey.'.pandit_id' => $matchedPandit->id,
            $sessionKey.'.pandit_name' => $matchedPandit->pandit_name ?: $matchedPandit->full_name,
            $sessionKey.'.date' => $request->date,
            $sessionKey.'.slot' => $request->slot,
            $sessionKey.'.mode' => $selectedHawanType['title'] ?? ($request->mode ?: 'Live + Replay'),
        ]);

        if ($selectedHawanType) {
            session([
                $sessionKey.'.hawan_type' => $selectedHawanType['key'],
                $sessionKey.'.hawan_type_title' => $selectedHawanType['title'],
                $sessionKey.'.hawan_type_price' => $selectedHawanType['price'],
            ]);
        }

        return redirect()->route($serviceType.'.review', ['slug' => $slug]);
    }

    private function matchingPanditsQuery(Request $request, array $serviceNames, string $day, array $slotTimes, string $serviceType, ?array $ritual = null)
    {
        $onlineColumn = $serviceType === 'pooja' ? 'online_pooja' : 'online_hawan';
        $selectedHawanType = $serviceType === 'hawan'
            ? ($request->input('hawan_type') ?: ($ritual['types'][0]['key'] ?? null))
            : null;
        $selectedHawanId = (int) ($ritual['id'] ?? 0);
        $ritualColumn = $serviceType === 'pooja' ? 'pooja_id' : 'hawan_id';
        $ritualId = (int) ($ritual['id'] ?? 0);

        $query = Pandit::query()
            ->with([
                'qualification',
                'services' => function ($q) use ($serviceType, $serviceNames, $ritualColumn, $ritualId) {
                    $q->where('service_type', $serviceType)
                        ->where('status', 'approved')
                        ->where(function ($service) use ($serviceNames, $ritualColumn, $ritualId) {
                            if ($ritualId > 0) {
                                $service->where($ritualColumn, $ritualId)
                                    ->orWhereIn('service_name', $serviceNames);
                            } else {
                                $service->whereIn('service_name', $serviceNames);
                            }
                        });
                },
                'languages',
                'availabilitySlots' => fn ($q) => $q->where('day', $day),
                'availabilitySetting',
                'onlineSetup',
            ])
            ->where('status', 'verified')
            ->whereHas('services', function ($q) use ($serviceNames, $serviceType, $ritualColumn, $ritualId) {
                $q->where('service_type', $serviceType)
                    ->where('status', 'approved')
                    ->where(function ($service) use ($serviceNames, $ritualColumn, $ritualId) {
                        if ($ritualId > 0) {
                            $service->where($ritualColumn, $ritualId)
                                ->orWhereIn('service_name', $serviceNames);
                        } else {
                            $service->whereIn('service_name', $serviceNames);
                        }
                    });
            })
            ->where(function ($q) {
                $q->whereHas('availabilitySetting', function ($setting) {
                    $setting->where('accept_new_bookings', true);
                })->orDoesntHave('availabilitySetting');
            })
            ->where(function ($q) use ($day, $slotTimes) {
                $q->whereHas('availabilitySlots', function ($q) use ($day, $slotTimes) {
                    $q->where('day', $day)
                        ->where('is_available', true)
                        ->where('start_time', '<=', $slotTimes['start'])
                        ->where('end_time', '>=', $slotTimes['end']);
                });
            })
            ->when($request->input('booking_mode', 'online') === 'online', function ($q) use ($onlineColumn) {
                $q->whereHas('onlineSetup', fn ($online) => $online->where($onlineColumn, true));
            })
            ->when($request->input('booking_mode') === 'offline', function ($q) use ($request) {
                $q->whereHas('availabilitySetting', function ($setting) use ($request) {
                    $setting->where('offline_service_available', true);

                    if ($request->filled('city')) {
                        $setting->where('service_city', $request->city);
                    }
                });
            });

        if ($serviceType === 'hawan' && $selectedHawanType === 'samuhik' && $selectedHawanId > 0) {
            $query
                ->whereDoesntHave('hawanBookings', function ($q) use ($request, $slotTimes, $selectedHawanId) {
                    $q->whereDate('booking_date', $request->date)
                        ->whereNotIn('status', ['cancelled', 'completed'])
                        ->where(function ($overlap) use ($request, $slotTimes) {
                            $overlap->where(function ($time) use ($slotTimes) {
                                $time->whereNotNull('slot_start_time')
                                    ->whereNotNull('slot_end_time')
                                    ->where('slot_start_time', '<', $slotTimes['end'])
                                    ->where('slot_end_time', '>', $slotTimes['start']);
                            })->orWhere(function ($legacy) use ($request) {
                                $legacy->whereNull('slot_start_time')->where('slot', $request->slot);
                            });
                        })
                        ->where(function ($blocking) use ($selectedHawanId, $slotTimes) {
                            $blocking
                                ->where('hawan_type', '!=', 'samuhik')
                                ->orWhereNull('hawan_type')
                                ->orWhere('ritual_id', '!=', $selectedHawanId)
                                ->orWhereNull('ritual_id')
                                ->orWhere('slot_start_time', '!=', $slotTimes['start'])
                                ->orWhereNull('slot_start_time')
                                ->orWhere('slot_end_time', '!=', $slotTimes['end'])
                                ->orWhereNull('slot_end_time');
                        });
                })
                ->whereRaw(
                    "(select count(*) from hawan_sessions
                        where hawan_sessions.pandit_id = pandits.id
                        and hawan_sessions.deleted_at is null
                        and hawan_sessions.ritual_id = ?
                        and hawan_sessions.hawan_type = 'samuhik'
                        and hawan_sessions.booking_date = ?
                        and hawan_sessions.slot_start_time = ?
                        and hawan_sessions.slot_end_time = ?
                        and hawan_sessions.status != 'cancelled'
                        and hawan_sessions.payment_status not in ('failed', 'refunded')
                    ) < ?",
                    [
                        $selectedHawanId,
                        $request->date,
                        $slotTimes['start'],
                        $slotTimes['end'],
                        PanditBookingService::SAMUHIK_HAWAN_MAX_PRIMARY_BOOKINGS,
                    ]
                );
        } else {
            $query->whereDoesntHave('hawanBookings', function ($q) use ($request, $slotTimes) {
                $q->whereDate('booking_date', $request->date)
                    ->whereNotIn('status', ['cancelled', 'completed'])
                    ->where(function ($overlap) use ($request, $slotTimes) {
                        $overlap->where(function ($time) use ($slotTimes) {
                            $time->whereNotNull('slot_start_time')
                                ->whereNotNull('slot_end_time')
                                ->where('slot_start_time', '<', $slotTimes['end'])
                                ->where('slot_end_time', '>', $slotTimes['start']);
                        })->orWhere(function ($legacy) use ($request) {
                            $legacy->whereNull('slot_start_time')->where('slot', $request->slot);
                        });
                    });
            });
        }

        return $query
            ->whereDoesntHave('poojaBookings', function ($q) use ($request, $slotTimes) {
                $q->whereDate('booking_date', $request->date)
                    ->whereNotIn('status', ['cancelled', 'completed'])
                    ->where(function ($overlap) use ($request, $slotTimes) {
                        $overlap->where(function ($time) use ($slotTimes) {
                            $time->whereNotNull('slot_start_time')
                                ->whereNotNull('slot_end_time')
                                ->where('slot_start_time', '<', $slotTimes['end'])
                                ->where('slot_end_time', '>', $slotTimes['start']);
                        })->orWhere(function ($legacy) use ($request) {
                            $legacy->whereNull('slot_start_time')->where('slot', $request->slot);
                        });
                    });
            });
    }

    private function applyFilters($query, Request $request, array $serviceNames, string $serviceType, ?array $ritual = null)
    {
        $ritualColumn = $serviceType === 'pooja' ? 'pooja_id' : 'hawan_id';
        $ritualId = (int) ($ritual['id'] ?? 0);

        return $query
            ->when($request->filled('language'), function ($q) use ($request) {
                $q->whereHas('languages', fn ($lang) => $lang->where('language', $request->language));
            })
            ->when($request->filled('experience'), function ($q) use ($request, $serviceNames, $serviceType, $ritualColumn, $ritualId) {
                $q->whereHas('services', function ($service) use ($request, $serviceNames, $serviceType, $ritualColumn, $ritualId) {
                    $service->where('service_type', $serviceType)
                        ->where('status', 'approved')
                        ->where(function ($match) use ($serviceNames, $ritualColumn, $ritualId) {
                            if ($ritualId > 0) {
                                $match->where($ritualColumn, $ritualId)
                                    ->orWhereIn('service_name', $serviceNames);
                            } else {
                                $match->whereIn('service_name', $serviceNames);
                            }
                        })
                        ->where('experience_years', '>=', $request->experience);
                });
            })
            ->when($request->filled('qualification'), function ($q) use ($request) {
                $q->whereHas('qualification', fn ($qualification) => $qualification->where('highest_qualification', $request->qualification));
            });
    }

    private function applySort($query, string $sort, array $serviceNames, string $serviceType, ?array $ritual = null)
    {
        $ritualColumn = $serviceType === 'pooja' ? 'pooja_id' : 'hawan_id';
        $ritualId = (int) ($ritual['id'] ?? 0);

        $experience = PanditService::select('experience_years')
            ->whereColumn('pandit_services.pandit_id', 'pandits.id')
            ->where('service_type', $serviceType)
            ->where('status', 'approved')
            ->where(function ($service) use ($serviceNames, $ritualColumn, $ritualId) {
                if ($ritualId > 0) {
                    $service->where($ritualColumn, $ritualId)
                        ->orWhereIn('service_name', $serviceNames);
                } else {
                    $service->whereIn('service_name', $serviceNames);
                }
            })
            ->limit(1);

        $performed = PanditService::select('approx_performed')
            ->whereColumn('pandit_services.pandit_id', 'pandits.id')
            ->where('service_type', $serviceType)
            ->where('status', 'approved')
            ->where(function ($service) use ($serviceNames, $ritualColumn, $ritualId) {
                if ($ritualId > 0) {
                    $service->where($ritualColumn, $ritualId)
                        ->orWhereIn('service_name', $serviceNames);
                } else {
                    $service->whereIn('service_name', $serviceNames);
                }
            })
            ->limit(1);

        if ($sort === 'experience') {
            return $query->orderByDesc($experience);
        }

        if ($sort === 'performed') {
            return $query->orderByDesc($performed);
        }

        return $query->orderByDesc($performed)->orderByDesc($experience);
    }

    private function findRitual(string $slug, string $serviceType): array
    {
        return $serviceType === 'pooja'
            ? $this->findPooja($slug)
            : $this->findHawan($slug);
    }

    private function findHawan(string $slug): array
    {
        $hawan = Hawan::active()->withEnabledHawanTypes()->where('slug', $slug)->first();

        if ($hawan) {
            return $hawan->toBookingArray();
        }

        return [
            'id' => null,
            'slug' => $slug,
            'name' => str($slug)->replace('-', ' ')->title()->toString(),
        ];
    }

    private function findPooja(string $slug): array
    {
        $pooja = Pooja::active()->where('slug', $slug)->first();

        if ($pooja) {
            return $pooja->toBookingArray();
        }

        return [
            'id' => null,
            'slug' => $slug,
            'name' => str($slug)->replace('-', ' ')->title()->toString(),
        ];
    }

    private function slotTimes(string $slot): array
    {
        $parts = preg_split('/\s*-\s*/', $slot);
        $start = $parts[0] ?? $slot;
        $end = $parts[1] ?? $start;

        return [
            'start' => Carbon::parse($start)->format('H:i:s'),
            'end' => Carbon::parse($end)->format('H:i:s'),
        ];
    }

    private function serviceNames(string $hawanName, string $serviceType): array
    {
        $suffix = $serviceType === 'pooja' ? ' Pooja' : ' Hawan';
        $withoutSuffix = trim(Str::replaceLast($suffix, '', $hawanName));

        return collect([$hawanName, $withoutSuffix])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function selectedHawanType(array $hawan, ?string $type): array
    {
        $enabledTypes = collect($hawan['types'] ?? []);

        $selectedType = $type
            ? $enabledTypes->firstWhere('key', $type)
            : $enabledTypes->first();

        abort_unless($selectedType, 404);

        return $selectedType;
    }
}
