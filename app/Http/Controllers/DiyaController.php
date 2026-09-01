<?php

namespace App\Http\Controllers;

use App\Models\Admin\Deity;
use App\Models\Admin\Diya;
use App\Models\Admin\DiyaSession;
use App\Models\Admin\Donation;
use App\Models\Admin\PaymentLog;
use App\Models\Admin\SankalpForm;
use App\Services\PanditBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DiyaController extends Controller
{
    private const BOOKING_DRAFT_SESSION_KEY = 'diya_booking_draft';

    public function index()
    {
        $diyas = collect();
        $activeDeities = collect();

        if (Schema::hasTable('diyas')) {
            $diyas = Diya::with(['fixedDeity', 'mantraAudio'])
                ->active()
                ->latest()
                ->get();
        }

        if (Schema::hasTable('deities')) {
            $activeDeities = Deity::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $diyaStats = [
            'scheduled' => Schema::hasTable('diya_sessions') ? DiyaSession::scheduled()->count() : 0,
            'glowing' => Schema::hasTable('diya_sessions') ? DiyaSession::currentlyGlowing()->count() : 0,
            'completed' => Schema::hasTable('diya_sessions') ? DiyaSession::completed()->count() : 0,
            'available' => $diyas->count(),
        ];

        return view('pages.light-diya', [
            'diyas' => $diyas,
            'activeDeities' => $activeDeities,
            'diyaOptions' => $diyas->map(fn (Diya $diya) => $diya->toOfferingArray())->values(),
            'diyaStats' => $diyaStats,
            'diyaBookingDraft' => session(self::BOOKING_DRAFT_SESSION_KEY, []),
        ]);
    }

    public function saveDraft(Request $request)
    {
        $validated = Validator::make($request->only($this->draftFields()), $this->draftRules())->validate();

        session()->put(self::BOOKING_DRAFT_SESSION_KEY, $validated);

        return response()->json([
            'success' => true,
            'redirect_url' => route('diya.continue'),
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
            'start_at' => ['nullable', 'date'],
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
        $amount = (float) $diya->seva_amount;

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
                'seva_amount' => $amount,
            ],
        ]);

        $bookingService = app(PanditBookingService::class);
        $token = $bookingService->token();
        $startAt = isset($validated['start_at']) ? Carbon::parse($validated['start_at']) : now();
        $endAt = $this->endAt($startAt, $diya->duration);
        $sessionStatus = $startAt->isFuture() ? DiyaSession::STATUS_SCHEDULED : DiyaSession::STATUS_ACTIVE;

        $session = DiyaSession::create([
            'user_id' => auth()->id(),
            'diya_id' => $diya->id,
            'deity_id' => $deity->id,
            'sankalp_form_id' => $sankalp->id,
            'booking_date' => $startAt->toDateString(),
            'slot' => 'Instant Diya Offering',
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => $sessionStatus,
            'payment_status' => 'paid',
            'expires_at' => $endAt,
            'live_session_token' => $token,
            'admin_note' => json_encode([
                'diya_name' => $diya->name,
                'diya_slug' => $diya->slug,
                'deity_name' => $deity->name,
                'deity_selection_mode' => $diya->deity_selection_mode,
                'duration' => $diya->duration,
                'start_at' => $startAt->toIso8601String(),
                'end_at' => $endAt?->toIso8601String(),
                'seva_amount' => $amount,
                'total_amount' => $amount,
                'mantra_audio_id' => $diya->mantra_audio_id,
                'mantra_audio_title' => $diya->mantraAudio?->title,
                'mantra_ambience' => $diya->mantra_ambience,
            ]),
        ]);

        $session->update([
            'live_session_link' => $bookingService->routeWithToken('diya.session', ['session' => $session], $token),
        ]);

        $donation = Donation::create([
            'user_id' => auth()->id(),
            'amount' => $amount,
            'currency' => 'INR',
            'donor_name' => $validated['full_name'],
            'donor_mobile' => $validated['mobile'],
            'razorpay_order_id' => 'demo_diya_order_'.$session->id,
            'razorpay_payment_id' => 'demo_diya_payment_'.$session->id,
            'payment_status' => 'paid',
            'receipt_number' => 'BDD-'.now()->format('Ymd').'-'.$session->id,
            'paid_at' => now(),
        ]);

        PaymentLog::create([
            'donation_id' => $donation->id,
            'user_id' => auth()->id(),
            'gateway' => 'demo',
            'order_id' => $donation->razorpay_order_id,
            'payment_id' => $donation->razorpay_payment_id,
            'status' => 'paid',
            'amount' => $amount,
            'payload' => [
                'booking_type' => 'diya',
                'diya_session_id' => $session->id,
                'sankalp_form_id' => $sankalp->id,
                'server_amount_source' => 'diyas.seva_amount',
            ],
        ]);

        session()->forget(self::BOOKING_DRAFT_SESSION_KEY);

        return response()->json([
            'success' => true,
            'message' => 'Diya offering created successfully.',
            'session_id' => $session->id,
            'redirect_url' => $session->live_session_link,
        ]);
    }

    public function session(Request $request, DiyaSession $session)
    {
        $session->load(['diya.mantraAudio.deity', 'deity', 'sankalp']);

        abort_unless(app(PanditBookingService::class)->canAccessPrivateSession($session, $request), 403);

        $mantraAudio = $session->diya?->mantraAudio;

        if (!$mantraAudio?->audio_file || $mantraAudio->status !== 'active') {
            $mantraAudio = null;
        }

        return view('pages.diya-session', compact('session', 'mantraAudio'));
    }

    private function endAt(Carbon $startAt, ?string $duration): Carbon
    {
        $duration = strtolower(trim((string) $duration));

        if (preg_match('/(\d+)\s*(hour|hours|hr|hrs)/', $duration, $matches)) {
            return $startAt->copy()->addHours((int) $matches[1]);
        }

        if (preg_match('/(\d+)\s*(day|days)/', $duration, $matches)) {
            return $startAt->copy()->addDays((int) $matches[1]);
        }

        if (preg_match('/(\d+)\s*(minute|minutes|min|mins)/', $duration, $matches)) {
            return $startAt->copy()->addMinutes((int) $matches[1]);
        }

        return $startAt->copy()->addDay();
    }

    private function draftFields(): array
    {
        return [
            'diya_id',
            'deity_id',
            'full_name',
            'mobile',
            'gotra',
            'dob',
            'birth_time',
            'birth_place',
            'father_name',
            'mother_name',
            'spouse_name',
            'family_names',
            'purpose',
            'mannokamna',
        ];
    }

    private function draftRules(): array
    {
        return [
            'diya_id' => [
                'required',
                Rule::exists('diyas', 'id')->where(fn ($query) => $query->where('status', 'active')->whereNull('deleted_at')),
            ],
            'deity_id' => ['nullable', 'integer', Rule::exists('deities', 'id')->where(fn ($query) => $query->where('status', 'active'))],
            'full_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'gotra' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
            'birth_time' => ['nullable', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'spouse_name' => ['nullable', 'string', 'max:255'],
            'family_names' => ['nullable', 'string'],
            'purpose' => ['required', 'string', 'max:255'],
            'mannokamna' => ['nullable', 'string'],
        ];
    }
}
