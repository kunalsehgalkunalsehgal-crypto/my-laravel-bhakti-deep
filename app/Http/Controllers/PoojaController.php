<?php

namespace App\Http\Controllers;

use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Models\Admin\SankalpForm;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditLanguage;
use App\Models\Pandit\PanditQualification;
use App\Services\PanditBookingService;
use App\Services\RazorpayPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PoojaController extends Controller
{
    public function index()
    {
        $poojaServices = Pooja::active()
            ->latest()
            ->get()
            ->map(fn (Pooja $pooja) => $pooja->toCardArray())
            ->all();

        if (empty($poojaServices)) {
            $poojaServices = collect($this->defaultPoojas())
                ->map(fn (array $pooja) => [
                    'name' => $pooja['name'],
                    'slug' => $pooja['slug'],
                    'purpose' => $pooja['short_description'],
                    'price' => number_format($pooja['base_price']),
                    'duration' => $pooja['duration'],
                    'featured' => $pooja['slug'] === 'lakshmi-pooja',
                    'benefits' => $pooja['benefits'],
                ])
                ->values()
                ->all();
        }

        return view('pages.personalized-pooja', compact('poojaServices'));
    }

    public function show(string $slug)
    {
        $pooja = Pooja::active()->where('slug', $slug)->first()?->toBookingArray();

        if (!$pooja) {
            $pooja = $this->defaultPoojas()[$slug] ?? null;
        }

        if (!$pooja) {
            abort(404, 'Pooja not found');
        }

        return view('pages.pooja-booking-show', [
            'pooja' => $pooja,
            'selectedPandit' => null,
            'openReviewStep' => false,
        ]);
    }

    public function review(string $slug)
    {
        $pooja = Pooja::active()->where('slug', $slug)->first()?->toBookingArray();

        if (!$pooja) {
            $pooja = $this->defaultPoojas()[$slug] ?? null;
        }

        if (!$pooja) {
            abort(404, 'Pooja not found');
        }

        $selectedPandit = null;
        if (
            session('pooja_booking.pandit_id')
            && session('pooja_booking.service_type') === 'pooja'
            && session('pooja_booking.service_slug') === $slug
        ) {
            $selectedPandit = Pandit::with('languages')->find(session('pooja_booking.pandit_id'));
        }

        return view('pages.pooja-booking-show', [
            'pooja' => $pooja,
            'selectedPandit' => $selectedPandit,
            'openReviewStep' => true,
        ]);
    }

    private function defaultPoojas(): array
    {
        $defaults = [
            'lakshmi-pooja' => [
                'name' => 'Lakshmi Pooja',
                'slug' => 'lakshmi-pooja',
                'short_description' => 'Prosperity, abundance and financial blessings ke liye personalized pooja.',
                'full_description' => 'Lakshmi Pooja Maa Lakshmi ko samarpit hai. Isme aapke naam, gotra aur mannokamna ke saath sankalp, mantra jaap aur aarti ki jati hai.',
                'featured_image' => 'assets/lakshmi-hero.jpg',
                'base_price' => 1501,
                'duration' => '45 min',
                'mode' => 'Live + Replay',
                'benefits' => ['Prosperity blessings', 'Business growth', 'Positive home energy'],
            ],
            'shiv-pooja' => [
                'name' => 'Shiv Pooja',
                'slug' => 'shiv-pooja',
                'short_description' => 'Health, peace and protection ke liye Lord Shiva ki pooja.',
                'full_description' => 'Shiv Pooja inner peace, healing aur protection ke liye ki jati hai. Sankalp ke saath Shiv mantra, abhishek bhaav aur aarti include hoti hai.',
                'featured_image' => 'assets/shiv.jpg',
                'base_price' => 1101,
                'duration' => '45 min',
                'mode' => 'Live + Replay',
                'benefits' => ['Mental peace', 'Health support', 'Protection energy'],
            ],
            'hanuman-pooja' => [
                'name' => 'Hanuman Pooja',
                'slug' => 'hanuman-pooja',
                'short_description' => 'Strength, courage and obstacle removal ke liye Hanuman ji ki pooja.',
                'full_description' => 'Hanuman Pooja fear removal, confidence aur protection ke liye hoti hai. Isme Hanuman mantra, sankalp aur aarti ki jati hai.',
                'featured_image' => 'assets/hanuman.jpg',
                'base_price' => 1101,
                'duration' => '40 min',
                'mode' => 'Live + Replay',
                'benefits' => ['Strength', 'Obstacle removal', 'Fear removal'],
            ],
            'ganesh-pooja' => [
                'name' => 'Ganesh Pooja',
                'slug' => 'ganesh-pooja',
                'short_description' => 'New beginnings, success and obstacle removal ke liye Ganesh pooja.',
                'full_description' => 'Ganesh Pooja naye kaam, career, business ya grih shubh aarambh ke liye ki jati hai. Isme sankalp, Ganesh mantra aur aarti hoti hai.',
                'featured_image' => 'assets/temple-hero.jpg',
                'base_price' => 1001,
                'duration' => '40 min',
                'mode' => 'Live + Replay',
                'benefits' => ['New beginnings', 'Success blessings', 'Obstacle removal'],
            ],
            'durga-pooja' => [
                'name' => 'Durga Pooja',
                'slug' => 'durga-pooja',
                'short_description' => 'Protection, power and positivity ke liye Maa Durga pooja.',
                'full_description' => 'Durga Pooja shakti, protection aur negative energy removal ke liye ki jati hai. Isme Maa Durga mantra aur aarti include hoti hai.',
                'featured_image' => 'assets/temple-background-image.jpeg',
                'base_price' => 2101,
                'duration' => '60 min',
                'mode' => 'Live + Replay',
                'benefits' => ['Protection', 'Power', 'Positive energy'],
            ],
            'satyanarayan-pooja' => [
                'name' => 'Satyanarayan Pooja',
                'slug' => 'satyanarayan-pooja',
                'short_description' => 'Family welfare, peace and auspicious blessings ke liye Satyanarayan pooja.',
                'full_description' => 'Satyanarayan Pooja parivarik sukh, shanti aur mangal kaamna ke liye ki jati hai. Isme sankalp, katha bhaav aur aarti hoti hai.',
                'featured_image' => 'assets/lakshmi-hero.jpg',
                'base_price' => 2501,
                'duration' => '75 min',
                'mode' => 'Live + Replay',
                'benefits' => ['Family welfare', 'Peace', 'Auspicious blessings'],
            ],
        ];

        return collect($defaults)
            ->map(fn (array $pooja) => array_merge([
                'donation_options' => [101, 251, 501, 1100],
                'available_slots' => ['7:00 AM - 8:00 AM', '12:00 PM - 1:00 PM', '6:00 PM - 7:00 PM'],
            ], $pooja))
            ->all();
    }

    public function pandits(string $slug, Request $request)
    {
        $pooja = Pooja::active()->where('slug', $slug)->first()?->toBookingArray();
        if (!$pooja) $pooja = $this->defaultPoojas()[$slug] ?? null;
        if (!$pooja) abort(404);

        $query = Pandit::with(['services', 'languages', 'availabilitySlots'])
            ->where('status', 'verified')
            ->whereHas('services', fn($q) => $q->where('service_type', 'pooja'));

        if ($request->filled('language')) {
            $query->whereHas('languages', fn($q) => $q->where('language', $request->language));
        }
        if ($request->filled('experience')) {
            $query->whereHas('services', fn($q) => $q->where('service_type', 'pooja')->where('experience_years', '>=', $request->experience));
        }
        if ($request->filled('qualification')) {
            $query->whereHas('qualification', fn($q) => $q->where('highest_qualification', $request->qualification));
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }

        $sort = $request->get('sort', 'recommended');
        if ($sort === 'experience') {
            $query->withMax(['services as max_exp' => fn($q) => $q->where('service_type', 'pooja')], 'experience_years')->orderByDesc('max_exp');
        } elseif ($sort === 'performed') {
            $query->withMax(['services as max_performed' => fn($q) => $q->where('service_type', 'pooja')], 'approx_performed')->orderByDesc('max_performed');
        }

        $recommendedPandits = (clone $query)->limit(4)->get();
        $pandits = $request->get('view') === 'all' ? $query->paginate(12)->withQueryString() : null;

        $languages = \App\Models\Pandit\PanditLanguage::distinct()->pluck('language');
        $qualifications = \App\Models\Pandit\PanditQualification::distinct()->pluck('highest_qualification');

        return view('pages.hawan-pandits', compact('pooja', 'recommendedPandits', 'pandits', 'languages', 'qualifications'))
            ->with('hawan', $pooja)
            ->with('serviceType', 'pooja');
    }

    public function panditShow(string $slug, int $pandit, Request $request)
    {
        $pooja = Pooja::active()->where('slug', $slug)->first()?->toBookingArray();
        if (!$pooja) $pooja = $this->defaultPoojas()[$slug] ?? null;
        if (!$pooja) abort(404);

        $panditModel = Pandit::with(['services', 'languages', 'availabilitySlots', 'qualification'])->findOrFail($pandit);

        return view('pages.pandit-public-profile', [
            'pandit'      => $panditModel,
            'hawan'       => $pooja,
            'serviceType' => 'pooja',
            'date'        => $request->date,
            'slot'        => $request->slot,
            'mode'        => $request->mode,
            'bookingMode' => $request->booking_mode,
        ]);
    }

    public function panditSelect(string $slug, int $pandit, Request $request)
    {
        $pooja = Pooja::active()->where('slug', $slug)->first()?->toBookingArray();
        if (!$pooja) $pooja = $this->defaultPoojas()[$slug] ?? null;
        if (!$pooja) abort(404);

        $panditModel = Pandit::findOrFail($pandit);

        session(['pooja_booking' => [
            'pandit_id'   => $panditModel->id,
            'pandit_name' => $panditModel->pandit_name ?: $panditModel->full_name,
            'date'        => $request->date,
            'slot'        => $request->slot,
            'mode'        => $request->mode,
        ]]);

        return redirect()->route('pooja.review', $slug);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pooja_slug' => 'required|string',
            'package_name' => 'required|string',
            'full_name' => 'required|string|max:255',
            'gotra' => 'nullable|string',
            'dob' => 'nullable|date',
            'birth_time' => 'nullable|string',
            'birth_place' => 'nullable|string',
            'father_name' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'spouse_name' => 'nullable|string',
            'family_names' => 'nullable|string',
            'mobile' => 'required|string|max:20',
            'purpose' => 'required|string',
            'mannokamna' => 'nullable|string',
            'donation_amount' => 'nullable|numeric|min:0',
            'booking_date' => 'required|date',
            'slot' => 'required|string',
            'otp' => 'nullable|string',
        ]);

        $booking = session('pooja_booking', []);
        $selectedPanditId = $booking['pandit_id'] ?? null;
        $bookingMode = $booking['booking_mode'] ?? 'online';

        if (!$selectedPanditId) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a pandit before payment.',
            ], 422);
        }

        $pooja = Pooja::active()->where('slug', $validated['pooja_slug'] ?? null)->first();

        if (!$pooja) {
            throw ValidationException::withMessages(['pooja_slug' => 'Selected Pooja is no longer active.']);
        }

        $bookingService = app(PanditBookingService::class);
        $bookingService->ensureRitualSlot($pooja, $validated['slot'], $validated['booking_date']);

        if (
            ($booking['service_type'] ?? null) !== 'pooja'
            || (int) ($booking['service_id'] ?? 0) !== (int) $pooja->id
            || ($booking['service_slug'] ?? null) !== $pooja->slug
            || (int) ($booking['pandit_id'] ?? 0) !== (int) $selectedPanditId
            || ($booking['date'] ?? null) !== $validated['booking_date']
            || ($booking['slot'] ?? null) !== $validated['slot']
            || ($booking['mode'] ?? null) !== $validated['package_name']
        ) {
            throw ValidationException::withMessages(['booking' => 'Pandit selection does not match this Pooja booking. Please select pandit again.']);
        }

        $packageAmount = $bookingService->packageAmount($pooja, $validated['package_name'], 'pooja');
        $dakshina = (float) ($validated['donation_amount'] ?? 0);
        $totalAmount = $packageAmount + $dakshina;
        [$holdStart, $holdEnd] = $bookingService->holdTimes();

        $session = DB::transaction(function () use ($bookingService, $pooja, $validated, $selectedPanditId, $booking, $bookingMode, $packageAmount, $dakshina, $totalAmount, $holdStart, $holdEnd) {
            Pandit::whereKey($selectedPanditId)->lockForUpdate()->firstOrFail();

            [$pandit, $panditService, $slotTimes] = $bookingService->ensurePanditCanServe(
                $selectedPanditId,
                'pooja',
                $bookingService->serviceNames($pooja->name, 'pooja'),
                $validated['booking_date'],
                $validated['slot'],
                $bookingMode,
                (int) ($booking['pandit_service_id'] ?? 0),
                $pooja->id,
                $booking['state'] ?? null,
                $booking['city'] ?? null
            );

            if ($bookingService->hasOverlappingBooking($selectedPanditId, $validated['booking_date'], $slotTimes['start'], $slotTimes['end'])) {
                throw ValidationException::withMessages(['slot' => 'Selected pandit is already booked for this time. Please choose another pandit.']);
            }

            $sankalp = SankalpForm::create([
                'user_id' => auth()->id(),
                'full_name' => $validated['full_name'],
                'mobile' => $validated['mobile'],
                'gotra' => $validated['gotra'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'birth_time' => $validated['birth_time'] ?? null,
                'birth_place' => $validated['birth_place'] ?? null,
                'father_name' => $validated['father_name'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'spouse_name' => $validated['spouse_name'] ?? null,
                'family_names' => $validated['family_names'] ?? null,
                'purpose' => $validated['purpose'],
                'mannokamna' => $validated['mannokamna'] ?? null,
                'metadata' => [
                    'pooja_id' => $pooja->id,
                    'pooja_slug' => $pooja->slug,
                    'pooja_name' => $pooja->name,
                    'package_name' => $validated['package_name'],
                    'package_amount' => $packageAmount,
                    'demo_otp' => $validated['otp'] ?? null,
                    'server_amount_source' => 'poojas.base_price',
                ],
            ]);

            $session = PoojaSession::create([
                'user_id' => auth()->id(),
                'service_type' => 'pooja',
                'booking_mode' => $bookingMode,
                'state' => $bookingMode === 'offline' ? ($booking['state'] ?? null) : null,
                'city' => $bookingMode === 'offline' ? ($booking['city'] ?? null) : null,
                'ritual_id' => $pooja->id,
                'ritual_slug' => $pooja->slug,
                'sankalp_form_id' => $sankalp->id,
                'pandit_id' => $pandit->id,
                'pandit_service_id' => $panditService->id,
                'booking_date' => $validated['booking_date'],
                'slot' => $validated['slot'],
                'slot_start_time' => $slotTimes['start'],
                'slot_end_time' => $slotTimes['end'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_hold_started_at' => $holdStart,
                'payment_hold_expires_at' => $holdEnd,
                'admin_note' => json_encode([
                    'pooja_id' => $pooja->id,
                    'pooja_name' => $pooja->name,
                    'pooja_slug' => $pooja->slug,
                    'pandit_id' => $pandit->id,
                    'pandit_service_id' => $panditService->id,
                    'pandit_name' => $pandit->pandit_name ?: $pandit->full_name,
                    'booking_mode' => $bookingMode,
                    'state' => $bookingMode === 'offline' ? ($booking['state'] ?? null) : null,
                    'city' => $bookingMode === 'offline' ? ($booking['city'] ?? null) : null,
                    'package_name' => $validated['package_name'],
                    'package_amount' => $packageAmount,
                    'dakshina' => $dakshina,
                    'total_amount' => $totalAmount,
                ]),
            ]);

            $bookingService->createPendingPayment($session, $totalAmount, [
                    'booking_type' => 'pooja',
                    'pooja_session_id' => $session->id,
                    'pooja_id' => $pooja->id,
                    'pooja_slug' => $pooja->slug,
                    'pooja_name' => $pooja->name,
                    'package_name' => $validated['package_name'],
                    'package_amount' => $packageAmount,
                    'booking_mode' => $bookingMode,
                    'state' => $bookingMode === 'offline' ? ($booking['state'] ?? null) : null,
                    'city' => $bookingMode === 'offline' ? ($booking['city'] ?? null) : null,
                    'sankalp_form_id' => $sankalp->id,
                    'donor_name' => $validated['full_name'],
                    'donor_mobile' => $validated['mobile'],
                    'dakshina' => $dakshina,
                    'server_amount_source' => 'poojas.base_price',
            ]);

            return $session->fresh('latestPaymentAttempt');
        });

        session()->forget('pooja_booking');
        $payment = app(RazorpayPaymentService::class)->createOrder($session->latestPaymentAttempt, $session, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Booking held for 10 minutes. Complete payment to confirm.',
            'session_id' => $session->id,
            'payment_status' => $session->payment_status,
            'hold_expires_at' => $session->payment_hold_expires_at?->toISOString(),
            'payment' => $payment,
            'redirect_url' => route('user.profile'),
        ]);
    }
}
