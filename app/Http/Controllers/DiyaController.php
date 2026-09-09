<?php

namespace App\Http\Controllers;

use App\Models\Admin\Audio;
use App\Models\Admin\Deity;
use App\Models\Admin\Diya;
use App\Models\Admin\DiyaSession;
use App\Models\Admin\Donation;
use App\Models\Admin\PaymentLog;
use App\Models\Admin\SankalpForm;
use App\Models\PaymentAttempt;
use App\Services\PanditBookingService;
use App\Services\RazorpayPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DiyaController extends Controller
{
    public function index()
    {
        $diyas = collect();
        $activeDeities = collect();

        if (Schema::hasTable('diyas')) {
            $diyas = Diya::with('fixedDeity')
                ->active()
                ->latest()
                ->get();
        }

        if (Schema::hasTable('deities')) {
            $activeDeities = Deity::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'featured_image']);
        }

        $liveDiyas = Schema::hasTable('diya_sessions')
            ? DiyaSession::with(['diya', 'deity'])
                ->where('payment_status', 'paid')
                ->currentlyGlowing()
                ->latest()
                ->limit(48)
                ->get()
            : collect();
        $liveDiyaCount = Schema::hasTable('diya_sessions')
            ? DiyaSession::where('payment_status', 'paid')->currentlyGlowing()->count()
            : 0;

        $deityFallbackImage = asset('assets/temple-hero.jpg');
        $diyaStats = [
            'scheduled' => Schema::hasTable('diya_sessions') ? DiyaSession::where('payment_status', 'paid')->scheduled()->count() : 0,
            'glowing' => $liveDiyaCount,
            'completed' => Schema::hasTable('diya_sessions') ? DiyaSession::where('payment_status', 'paid')->completed()->count() : 0,
            'available' => $diyas->count(),
        ];

        return view('pages.light-diya', [
            'diyas' => $diyas,
            'activeDeities' => $activeDeities,
            'deityFallbackImage' => $deityFallbackImage,
            'deityOptions' => $activeDeities->map(fn ($deity) => [
                'id' => $deity->id,
                'name' => $deity->name,
                'image_url' => $deity->featured_image ? asset(str_starts_with($deity->featured_image, 'assets/') ? $deity->featured_image : 'storage/'.$deity->featured_image) : $deityFallbackImage,
            ])->values(),
            'diyaOptions' => $diyas->map(fn (Diya $diya) => $diya->toOfferingArray())->values(),
            'diyaStats' => $diyaStats,
            'liveDiyas' => $liveDiyas,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'diya_id' => [
                'required',
                Rule::exists('diyas', 'id')->where(fn ($query) => $query->where('status', 'active')->whereNull('deleted_at')),
            ],
            'deity_id' => ['nullable', 'integer'],
            'full_name' => ['required', 'string', 'max:255'],
            'gotra' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
            'birth_time' => ['nullable', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'spouse_name' => ['nullable', 'string', 'max:255'],
            'family_names' => ['nullable', 'string'],
            'mobile' => ['required', 'string', 'max:20'],
            'purpose' => ['required', 'string', 'max:255'],
            'mannokamna' => ['nullable', 'string'],
            'selected_amount' => ['required', Rule::in(['1', '11', '51', '101', '501', 'custom'])],
            'custom_amount' => ['nullable', 'required_if:selected_amount,custom', 'integer', 'min:1', 'max:100000'],
            'consent' => ['accepted'],
        ]);

        $diya = null;
        $deity = null;

        $validator->after(function ($validator) use ($request, &$diya, &$deity) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $diya = Diya::with('fixedDeity')->active()->find($request->input('diya_id'));

            if (!$diya) {
                $validator->errors()->add('diya_id', 'Please select an active diya.');
                return;
            }

            if ($diya->isFixedDeity()) {
                $deity = $diya->fixedDeity;

                if (!$deity || $deity->status !== 'active') {
                    $validator->errors()->add('diya_id', 'This diya is not available because its fixed deity is inactive.');
                }

                return;
            }

            $deity = Deity::where('status', 'active')->find($request->input('deity_id'));

            if (!$deity) {
                $validator->errors()->add('deity_id', 'Please select an active deity for this diya.');
            }
        });

        $validated = $validator->validate();
        $amount = $validated['selected_amount'] === 'custom'
            ? (float) $validated['custom_amount']
            : (float) $validated['selected_amount'];
        $mantraAudio = $this->deityAudio($deity, 'mantraAudio', ['mantra', 'aarti']);
        $ambientAudio = $this->deityAudio($deity, 'ambientAudio', ['temple_ambience', 'hawan_ambience']);

        if (!str_starts_with((string) config('services.razorpay.key_id'), 'rzp_test_')) {
            throw ValidationException::withMessages(['payment' => 'Razorpay test key is not configured.']);
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
                'diya_id' => $diya->id,
                'diya_name' => $diya->name,
                'deity_id' => $deity->id,
                'deity_name' => $deity->name,
                'deity_selection_mode' => $diya->deity_selection_mode,
                'seva_amount' => (float) $diya->seva_amount,
                'donation_amount' => $amount,
            ],
        ]);

        $bookingService = app(PanditBookingService::class);
        $token = $bookingService->token();

        $session = DiyaSession::create([
            'user_id' => auth()->id(),
            'diya_id' => $diya->id,
            'deity_id' => $deity->id,
            'sankalp_form_id' => $sankalp->id,
            'booking_date' => now()->toDateString(),
            'slot' => 'Instant Diya Offering',
            'status' => 'pending',
            'payment_status' => 'pending',
            'live_session_token' => $token,
            'admin_note' => json_encode([
                'diya_name' => $diya->name,
                'diya_slug' => $diya->slug,
                'deity_name' => $deity->name,
                'deity_selection_mode' => $diya->deity_selection_mode,
                'duration' => $diya->duration,
                'seva_amount' => (float) $diya->seva_amount,
                'selected_amount' => $validated['selected_amount'],
                'donation_amount' => $amount,
                'total_amount' => $amount,
                'mantra_audio_id' => $mantraAudio?->id,
                'mantra_audio_title' => $mantraAudio?->title,
                'ambient_audio_id' => $ambientAudio?->id,
                'ambient_audio_title' => $ambientAudio?->title,
            ]),
        ]);

        $session->update([
            'live_session_link' => $bookingService->routeWithToken('diya.session', ['session' => $session], $token),
        ]);

        $donation = Donation::create([
            'user_id' => auth()->id(),
            'payment_purpose' => 'diya',
            'session_type' => DiyaSession::class,
            'session_id' => $session->id,
            'amount' => $amount,
            'currency' => 'INR',
            'donor_name' => $validated['full_name'],
            'donor_mobile' => $validated['mobile'],
            'payment_status' => 'pending',
        ]);

        $attempt = PaymentAttempt::create([
            'user_id' => auth()->id(),
            'donation_id' => $donation->id,
            'payable_type' => DiyaSession::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_DONATION,
            'gateway' => 'none',
            'amount' => $amount,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PENDING,
            'metadata' => [
                'booking_type' => 'diya',
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'diya_session_id' => $session->id,
                'sankalp_form_id' => $sankalp->id,
                'selected_amount' => $validated['selected_amount'],
                'donation_amount' => $amount,
            ],
        ]);

        $donation->update(['latest_payment_attempt_id' => $attempt->id]);
        $session->update(['latest_payment_attempt_id' => $attempt->id]);

        PaymentLog::create([
            'donation_id' => $donation->id,
            'payment_attempt_id' => $attempt->id,
            'loggable_type' => DiyaSession::class,
            'loggable_id' => $session->id,
            'user_id' => auth()->id(),
            'gateway' => 'none',
            'event_type' => 'payment_hold_created',
            'status' => 'pending',
            'occurred_at' => now(),
            'amount' => $amount,
            'payload' => $attempt->metadata,
        ]);

        $payment = app(RazorpayPaymentService::class)->createOrder($attempt, $session, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Diya details saved. Complete Razorpay test payment to light your diya.',
            'session_id' => $session->id,
            'donation_id' => $donation->id,
            'payment_attempt_id' => $attempt->id,
            'payment' => $payment,
            'redirect_url' => route('user.profile'),
        ]);
    }

    public function session(Request $request, DiyaSession $session)
    {
        $session->load(['diya', 'deity.mantraAudio', 'deity.ambientAudio', 'sankalp', 'latestPaymentAttempt.donation']);

        abort_unless(app(PanditBookingService::class)->canAccessPrivateSession($session, $request), 403);

        $mantraAudio = $this->deityAudio($session->deity, 'mantraAudio', ['mantra', 'aarti']);
        $ambientAudio = $this->deityAudio($session->deity, 'ambientAudio', ['temple_ambience', 'hawan_ambience']);

        return view('pages.diya-session', compact('session', 'mantraAudio', 'ambientAudio'));
    }

    private function deityAudio(?Deity $deity, string $relation, array $categories): ?Audio
    {
        $audio = $deity?->{$relation};

        if ($audio?->audio_file && $audio->status === 'active' && in_array($audio->category, $categories, true)) {
            return $audio;
        }

        return $deity ? Audio::active()
            ->where('deity_id', $deity->id)
            ->whereIn('category', $categories)
            ->whereNotNull('audio_file')
            ->orderBy('title')
            ->first() : null;
    }
}
