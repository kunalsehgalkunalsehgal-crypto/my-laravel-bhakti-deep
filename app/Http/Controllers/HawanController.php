<?php

namespace App\Http\Controllers;

use App\Models\Admin\Donation;
use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PaymentLog;
use App\Models\Admin\SankalpForm;
use App\Models\Pandit\Pandit;
use App\Services\PanditBookingService;
use App\Services\VideoMeetingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HawanController extends Controller
{
    public function index()
    {
        $hawanServices = Hawan::active()
            ->withEnabledHawanTypes()
            ->latest()
            ->get()
            ->map(fn (Hawan $hawan) => $hawan->toCardArray())
            ->all();

        return view('pages.hawan', compact('hawanServices'));
    }

    /**
     * Display the selected Hawan booking page
     */
    public function show($slug)
    {
        $hawan = Hawan::active()->withEnabledHawanTypes()->where('slug', $slug)->first()?->toBookingArray();

        if (!$hawan || empty($hawan['types'])) {
            abort(404, 'Hawan not found');
        }

        return view('pages.hawan-booking-show', [
            'hawan' => $hawan,
            'selectedPandit' => null,
            'openReviewStep' => false,
        ]);
    }

    public function review($slug)
    {
        $hawan = Hawan::active()->withEnabledHawanTypes()->where('slug', $slug)->first()?->toBookingArray();

        if (!$hawan || empty($hawan['types'])) {
            abort(404, 'Hawan not found');
        }

        $selectedPandit = null;
        if (
            session('hawan_booking.pandit_id')
            && session('hawan_booking.service_type') === 'hawan'
            && session('hawan_booking.service_slug') === $slug
        ) {
            $selectedPandit = Pandit::with('languages')->find(session('hawan_booking.pandit_id'));
        }

        return view('pages.hawan-booking-show', [
            'hawan' => $hawan,
            'selectedPandit' => $selectedPandit,
            'openReviewStep' => true,
        ]);
    }

    /**
     * Store hawan booking and create session
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hawan_slug' => 'required|string',
            'hawan_type' => 'required|string|in:samuhik,special',
            'package_name' => 'nullable|string',
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

        $booking = session('hawan_booking', []);
        $selectedPanditId = $booking['pandit_id'] ?? null;

        if (!$selectedPanditId) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a pandit before payment.',
            ], 422);
        }

        $hawan = Hawan::active()->where('slug', $validated['hawan_slug'])->first();

        if (!$hawan) {
            throw ValidationException::withMessages(['hawan_slug' => 'Selected Hawan is no longer active.']);
        }

        $selectedHawanType = $hawan->enabledHawanType($validated['hawan_type']);

        if (!$selectedHawanType) {
            throw ValidationException::withMessages(['hawan_type' => 'Selected Hawan type is no longer available.']);
        }

        if (
            ($booking['service_type'] ?? null) !== 'hawan'
            || (int) ($booking['service_id'] ?? 0) !== (int) $hawan->id
            || ($booking['service_slug'] ?? null) !== $hawan->slug
            || ($booking['hawan_type'] ?? null) !== $selectedHawanType['key']
            || (int) ($booking['pandit_id'] ?? 0) !== (int) $selectedPanditId
            || ($booking['date'] ?? null) !== $validated['booking_date']
            || ($booking['slot'] ?? null) !== $validated['slot']
            || ($booking['mode'] ?? null) !== $selectedHawanType['title']
        ) {
            throw ValidationException::withMessages(['booking' => 'Pandit selection does not match this Hawan booking. Please select pandit again.']);
        }

        $bookingService = app(PanditBookingService::class);
        $packageName = $selectedHawanType['title'];
        $packageAmount = (float) $selectedHawanType['price'];
        $dakshina = (float) ($validated['donation_amount'] ?? 0);
        $totalAmount = $packageAmount + $dakshina;

        $session = DB::transaction(function () use ($bookingService, $hawan, $validated, $selectedPanditId, $booking, $selectedHawanType, $packageName, $packageAmount, $dakshina, $totalAmount) {
            Pandit::whereKey($selectedPanditId)->lockForUpdate()->firstOrFail();

            [$pandit, $panditService, $slotTimes] = $bookingService->ensurePanditCanServe(
                $selectedPanditId,
                'hawan',
                $bookingService->serviceNames($hawan->name, 'hawan'),
                $validated['booking_date'],
                $validated['slot'],
                'online',
                (int) ($booking['pandit_service_id'] ?? 0),
                $hawan->id
            );

            if ($bookingService->hasBlockingHawanBooking(
                $selectedPanditId,
                $hawan->id,
                $selectedHawanType['key'],
                $validated['booking_date'],
                $slotTimes['start'],
                $slotTimes['end']
            )) {
                throw ValidationException::withMessages(['slot' => 'Selected pandit is already booked for this time. Please choose another pandit.']);
            }

            if (
                $selectedHawanType['key'] === 'samuhik'
                && !$bookingService->samuhikHawanSessionHasCapacity($selectedPanditId, $hawan->id, $validated['booking_date'], $slotTimes['start'], $slotTimes['end'])
            ) {
                throw ValidationException::withMessages(['slot' => 'This Samuhik Hawan session already has 5 bookings. Please choose another slot.']);
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
                    'hawan_id' => $hawan->id,
                    'hawan_slug' => $hawan->slug,
                    'hawan_name' => $hawan->name,
                    'hawan_type' => $selectedHawanType['key'],
                    'hawan_type_title' => $packageName,
                    'hawan_type_price' => $packageAmount,
                    'package_name' => $packageName,
                    'package_amount' => $packageAmount,
                    'demo_otp' => $validated['otp'] ?? null,
                    'server_amount_source' => 'hawans.hawan_type_price',
                ],
            ]);

            $token = $bookingService->token();

            $session = HawanSession::create([
                'user_id' => auth()->id(),
                'service_type' => 'hawan',
                'ritual_id' => $hawan->id,
                'ritual_slug' => $hawan->slug,
                'hawan_type' => $selectedHawanType['key'],
                'hawan_type_title' => $packageName,
                'hawan_type_price' => $packageAmount,
                'sankalp_form_id' => $sankalp->id,
                'pandit_id' => $pandit->id,
                'pandit_service_id' => $panditService->id,
                'booking_date' => $validated['booking_date'],
                'slot' => $validated['slot'],
                'slot_start_time' => $slotTimes['start'],
                'slot_end_time' => $slotTimes['end'],
                'live_session_token' => $token,
                'status' => 'scheduled',
                'payment_status' => 'paid',
                'admin_note' => json_encode([
                    'hawan_id' => $hawan->id,
                    'hawan_slug' => $hawan->slug,
                    'hawan_name' => $hawan->name,
                    'pandit_id' => $pandit->id,
                    'pandit_service_id' => $panditService->id,
                    'pandit_name' => $pandit->pandit_name ?: $pandit->full_name,
                    'hawan_type' => $selectedHawanType['key'],
                    'hawan_type_title' => $packageName,
                    'hawan_type_price' => $packageAmount,
                    'package_name' => $packageName,
                    'package_amount' => $packageAmount,
                    'dakshina' => $dakshina,
                    'total_amount' => $totalAmount,
                ]),
            ]);

            $session->update([
                'live_session_link' => $bookingService->routeWithToken('live.session', ['type' => 'hawan', 'id' => $session->id], $token),
            ]);

            $donation = Donation::create([
                'user_id' => auth()->id(),
                'amount' => $totalAmount,
                'currency' => 'INR',
                'donor_name' => $validated['full_name'],
                'donor_mobile' => $validated['mobile'],
                'razorpay_order_id' => 'demo_order_'.$session->id,
                'razorpay_payment_id' => 'demo_payment_'.$session->id,
                'payment_status' => 'paid',
                'receipt_number' => 'BDH-'.now()->format('Ymd').'-'.$session->id,
                'paid_at' => now(),
            ]);

            PaymentLog::create([
                'donation_id' => $donation->id,
                'user_id' => auth()->id(),
                'gateway' => 'demo',
                'order_id' => $donation->razorpay_order_id,
                'payment_id' => $donation->razorpay_payment_id,
                'status' => 'paid',
                'amount' => $totalAmount,
                'payload' => [
                    'booking_type' => 'hawan',
                    'hawan_session_id' => $session->id,
                    'hawan_type' => $selectedHawanType['key'],
                    'hawan_type_price' => $packageAmount,
                    'sankalp_form_id' => $sankalp->id,
                    'server_amount_source' => 'hawans.hawan_type_price',
                ],
            ]);

            return $session;
        });

        session()->forget('hawan_booking');

        return response()->json([
            'success' => true,
            'message' => 'Booking successful',
            'session_id' => $session->id,
            'redirect_url' => $session->live_session_link,
        ]);
    }
}
