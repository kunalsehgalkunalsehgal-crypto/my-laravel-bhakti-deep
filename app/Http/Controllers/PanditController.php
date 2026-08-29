<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditQualification;
use App\Models\Pandit\PanditService;
use App\Models\Pandit\PanditBankDetail;
use App\Models\Pandit\PanditDocument;
use App\Models\Pandit\PanditLanguage;
use App\Models\Pandit\PanditAvailabilitySetting;
use App\Models\Pandit\PanditAvailabilitySlot;
use App\Models\Pandit\PanditOnlineSetup;
use App\Models\Pandit\PanditNotification;
use App\Models\Pandit\PanditMessage;
use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Services\VideoMeetingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


use App\Events\PanditMessageSent;

class PanditController extends Controller
{
    // Helper: get current pandit from session
    private function getPandit()
    {
        $pandit = Auth::guard('pandit')->user();

        if ($pandit) {
            Session::put('pandit_id', $pandit->id);
        }

        return $pandit;
    }

    // LOGIN
    public function loginPage() { return view('pandit.login'); }

    // public function login(Request $request)
    // {
    //     $pandit = Pandit::where('email', $request->email)->first();
    //     if (!$pandit) return back()->with('error', 'Email not found.');
    //     Session::put('pandit_id', $pandit->id);
    //     return redirect()->route('pandit.dashboard');
    // }
    public function login(Request $request)
{
    return redirect()->route('login')
        ->withErrors(['email' => 'Pandit login is OTP-only. Please verify with Gmail OTP.']);
}

    // public function logout()
    // {
    //     Session::forget('pandit_id');
    //     return redirect()->route('pandit.login');
    // }
    public function logout(Request $request)
{
    Auth::guard('pandit')->logout();

    Session::forget('pandit_id');

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('pandit.login');
}

    // REGISTER
    public function registerPage() { return view('pandit.register'); }

    public function register(Request $request)
    {
        return redirect()->route('pandit.register')
            ->withErrors(['email' => 'Pandit registration is OTP-only. Please verify with Gmail OTP.']);
    }

    // DASHBOARD
    public function dashboard()
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        $today = Carbon::today();
        $hawanSessions = HawanSession::with(['user', 'service', 'sankalp', 'videoMeeting'])
            ->where('pandit_id', $pandit->id)
            ->get()
            ->map(fn ($session) => $this->formatDashboardSession($session, 'hawan'));

        $poojaSessions = PoojaSession::with(['user', 'service', 'sankalp', 'videoMeeting'])
            ->where('pandit_id', $pandit->id)
            ->get()
            ->map(fn ($session) => $this->formatDashboardSession($session, 'pooja'));

        $sessions = $hawanSessions
            ->merge($poojaSessions)
            ->sortBy([
                ['booking_date', 'asc'],
                ['slot', 'asc'],
            ])
            ->values();

        $activeSessions = $sessions->reject(fn ($session) => in_array($session['status'], ['completed', 'cancelled'], true));
        $todaySessions = $activeSessions->filter(fn ($session) => $session['booking_date']?->isSameDay($today));
        $upcomingSessions = $activeSessions->filter(fn ($session) => !$session['booking_date'] || $session['booking_date']->greaterThanOrEqualTo($today));
        $completedSessions = $sessions->filter(fn ($session) => $session['status'] === 'completed' || $session['completed_at']);
        $earningSessions = $sessions->filter(fn ($session) => $session['payment_status'] === 'paid' && !in_array($session['status'], ['cancelled'], true));

        $stats = [
            ["Today's Sessions", $todaySessions->count(), 'bi-camera-video'],
            ['Upcoming Bookings', $upcomingSessions->count(), 'bi-calendar-event'],
            ['Completed Sessions', $completedSessions->count(), 'bi-check2-circle'],
            ['Total Dakshina', 'Rs '.number_format($earningSessions->sum('dakshina')), 'bi-currency-rupee'],
        ];

        $nextSession = $todaySessions->first() ?: $upcomingSessions->first();
        $recentSessions = $sessions
            ->sortByDesc(fn ($session) => $session['booking_date']?->timestamp ?? 0)
            ->take(6)
            ->values();

        $dashboardCounts = [
            'services' => $pandit->services()->count(),
            'unread_notifications' => PanditNotification::where('pandit_id', $pandit->id)->where('is_read', false)->count(),
            'unread_messages' => PanditMessage::where('pandit_id', $pandit->id)->where('sender', 'admin')->where('is_read', false)->count(),
        ];

        return view('pandit.dashboard', compact('pandit', 'stats', 'nextSession', 'recentSessions', 'dashboardCounts'));
    }

    private function formatDashboardSession($session, string $type): array
    {
        $meta = $this->decodeBookingMeta($session->admin_note);
        $serviceKey = $type === 'pooja' ? 'pooja_name' : 'hawan_name';
        $serviceName = $meta[$serviceKey] ?? $session->service?->name ?? ucfirst($type).' Booking';
        $canStartMeeting = $session->payment_status === 'paid'
            && $session->status === 'confirmed'
            && $session->videoMeeting;

        return [
            'id' => $session->id,
            'type' => $type,
            'label' => ucfirst($type),
            'service_name' => $serviceName,
            'package_name' => $meta['package_name'] ?? null,
            'booking_date' => $session->booking_date,
            'slot' => $session->slot,
            'status' => $session->status,
            'payment_status' => $session->payment_status,
            'completed_at' => $session->completed_at,
            'live_session_link' => $session->live_session_link,
            'meeting_start_url' => $canStartMeeting ? route('live.session', ['type' => $type, 'id' => $session->id]) : null,
            // 'meeting_start_url' => $canStartMeeting ? route('live.session.start', ['type' => $type, 'id' => $session->id]) : null,
            'can_start_meeting' => $canStartMeeting,
            'yajman' => $session->sankalp?->full_name ?? $session->user?->name ?? 'Not added',
            'purpose' => $session->sankalp?->purpose ?? $session->sankalp?->mannokamna ?? 'Not added',
            'gotra' => $session->sankalp?->gotra,
            'mobile' => $session->sankalp?->mobile,
            'dakshina' => (float) ($meta['dakshina'] ?? 0),
            'total_amount' => (float) ($meta['total_amount'] ?? $meta['package_amount'] ?? 0),
            'accept_url' => in_array($session->status, ['pending', 'scheduled'], true)
                ? route('pandit.bookings.accept', ['type' => $type, 'id' => $session->id])
                : null,
            'can_accept' => in_array($session->status, ['pending', 'scheduled'], true),
        ];
    }

    public function acceptBooking(Request $request, string $type, string $id)
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        $model = match ($type) {
            'pooja' => PoojaSession::class,
            'hawan' => HawanSession::class,
            default => abort(404),
        };

        $session = $model::with('videoMeeting')
            ->whereKey($id)
            ->where('pandit_id', $pandit->id)
            ->firstOrFail();

        if (!in_array($session->status, ['pending', 'scheduled'], true)) {
            return back()->with('info', ucfirst($type).' booking is already '.str_replace('_', ' ', $session->status).'.');
        }

        $session->update(['status' => 'confirmed']);

        $meeting = null;
        if ($session->payment_status === 'paid') {
            $meeting = app(VideoMeetingService::class)->createForSessionIfReady($session->fresh());
        }

        if ($session->payment_status === 'paid' && !$meeting) {
            return back()->with('warning', ucfirst($type).' booking accepted, but the video meeting could not be created yet.');
        }

        return back()->with('success', ucfirst($type).' booking accepted successfully.');
    }

    private function decodeBookingMeta(?string $adminNote): array
    {
        if (!$adminNote) {
            return [];
        }

        $meta = json_decode($adminNote, true);

        return is_array($meta) ? $meta : [];
    }

    public function resubmit()
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');
        $pandit->update(['status' => 'under_review']);
        return back()->with('success', 'Profile resubmitted for review!');
    }

    // PROFILE
    public function profile()
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');
        return view('pandit.profile', compact('pandit'));
    }

    public function updateProfile(Request $request)
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        $data = $request->only([
            'full_name','pandit_name','mobile','whatsapp_number','email',
            'date_of_birth','gender','full_address','city','state','country',
            'total_experience_years','institution_name','position_role',
            'institution_city','about',
        ]);
        $data['is_independent']              = $request->independent_pandit === 'Yes' ? 1 : 0;
        $data['associated_with_institution'] = $request->temple_association === 'Yes' ? 1 : 0;

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('pandits/photos', 'public');
        }

        $pandit->update($data);
        return back()->with('success', 'Profile updated!');
    }

    // QUALIFICATION
    public function qualification()
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');
        $qualification = $pandit->qualification;
        return view('pandit.qualification', compact('pandit', 'qualification'));
    }

    public function updateQualification(Request $request)
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        $data = $request->only([
            'highest_qualification','course_name','institute_name','guru_name',
            'completion_year','certificate_number','sampradaya','ritual_tradition','sanskrit_level',
        ]);
        $data['knowledge_areas'] = json_encode($request->knowledge_areas ?? []);

        if ($request->hasFile('certificate_file')) {
            $data['certificate_file'] = $request->file('certificate_file')->store('pandits/certificates', 'public');
        }

        PanditQualification::updateOrCreate(['pandit_id' => $pandit->id], $data);
        return back()->with('success', 'Qualification updated!');
    }

    // SERVICES
    public function services()
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');
        $services = $pandit->services;
        $hawanServices = Hawan::active()->orderBy('name')->get(['id', 'name']);
        $poojaServices = Pooja::active()->orderBy('name')->get(['id', 'name']);

        return view('pandit.services', compact('pandit', 'services', 'hawanServices', 'poojaServices'));
    }

    public function addService(Request $request)
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        $data = $request->validate([
            'service_type' => ['required', 'in:hawan,pooja'],
            'service_id' => ['required', 'integer'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'approx_performed' => ['nullable', 'integer', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $ritual = $data['service_type'] === 'hawan'
            ? Hawan::active()->findOrFail($data['service_id'])
            : Pooja::active()->findOrFail($data['service_id']);

        $serviceName = trim($ritual->name);
        $hawanId = $data['service_type'] === 'hawan' ? $ritual->id : null;
        $poojaId = $data['service_type'] === 'pooja' ? $ritual->id : null;

        $service = PanditService::query()
            ->where('pandit_id', $pandit->id)
            ->where('service_type', $data['service_type'])
            ->where(function ($query) use ($data, $serviceName, $hawanId, $poojaId) {
                if ($data['service_type'] === 'hawan') {
                    $query->where('hawan_id', $hawanId);
                } else {
                    $query->where('pooja_id', $poojaId);
                }

                $query->orWhere('service_name', $serviceName);
            })
            ->first() ?: new PanditService([
                'pandit_id' => $pandit->id,
                'service_type' => $data['service_type'],
            ]);

        $service->fill([
            'service_name' => $serviceName,
            'hawan_id' => $hawanId,
            'pooja_id' => $poojaId,
            'experience_years' => $data['experience_years'] ?? null,
            'approx_performed' => $data['approx_performed'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
        ])->save();

        return back()->with('success', 'Service saved!');
    }

    public function deleteService($id)
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');
        PanditService::where('id', $id)->where('pandit_id', $pandit->id)->delete();
        return back()->with('success', 'Service removed!');
    }

    // BANK DETAILS
    public function bankDetails()
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');
        $bank = $pandit->bankDetail;
        return view('pandit.bank-details', compact('pandit', 'bank'));
    }

    public function updateBankDetails(Request $request)
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        PanditBankDetail::updateOrCreate(['pandit_id' => $pandit->id], $request->only([
            'account_holder_name','bank_name','account_number','ifsc_code','upi_id','pan_number',
        ]));
        return back()->with('success', 'Bank details saved!');
    }

    // DOCUMENTS
    public function documents()
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');
        $document = $pandit->document;
        return view('pandit.documents', compact('pandit', 'document'));
    }

    public function updateDocuments(Request $request)
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        $data = ['pandit_id' => $pandit->id, 'government_id_type' => $request->government_id_type, 'pan_number' => $request->pan_number];

        foreach (['government_id_file','pan_card_file','address_proof','qualification_certificate','mantra_chanting_sample','hawan_performance_video'] as $file) {
            if ($request->hasFile($file)) {
                $data[$file] = $request->file($file)->store('pandits/documents', 'public');
            }
        }

        PanditDocument::updateOrCreate(['pandit_id' => $pandit->id], $data);
        return back()->with('success', 'Documents saved!');
    }

    // AVAILABILITY
    public function availability()
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');
        $slots = PanditAvailabilitySlot::where('pandit_id', $pandit->id)->get()->groupBy('day');
        $availabilitySetting = $pandit->availabilitySetting;
        $onlineSetup = $pandit->onlineSetup;
        return view('pandit.availability', compact('pandit', 'slots', 'onlineSetup', 'availabilitySetting'));
    }

    public function saveOnlineSetup(Request $request)
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        PanditOnlineSetup::updateOrCreate(['pandit_id' => $pandit->id], [
            'pandit_id'       => $pandit->id,
            'online_hawan'    => $request->online_hawan === 'Yes',
            'online_pooja'    => $request->online_pooja === 'Yes',
            'stable_internet' => $request->stable_internet === 'Yes',
            'platforms'       => $request->input('platforms', []),
            'devices'         => $request->input('devices', []),
            'equipment'       => $request->input('equipment', []),
        ]);

        return back()->with('success', 'Online setup saved!');
    }

    // MESSAGES
    public function messages()
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        $messages = PanditMessage::where('pandit_id', $pandit->id)->oldest()->get();

        // mark admin messages as read
        PanditMessage::where('pandit_id', $pandit->id)
            ->where('sender', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('pandit.messages', compact('pandit', 'messages'));
    }

    // public function sendMessage(Request $request)
    // {
    //     $pandit = $this->getPandit();
    //     if (!$pandit) return redirect()->route('pandit.login');

    //     $request->validate(['message' => 'required|string|max:1000']);

    //     PanditMessage::create([
    //         'pandit_id' => $pandit->id,
    //         'sender'    => 'pandit',
    //         'message'   => $request->message,
    //     ]);

    //     return back()->with('success', 'Message sent!');
    // }
    public function sendMessage(Request $request)
{
    $pandit = $this->getPandit();

    if (!$pandit) {
        return redirect()->route('pandit.login');
    }

    $request->validate([
        'message' => 'required|string|max:1000'
    ]);

    // Database me message save
    $message = PanditMessage::create([
        'pandit_id' => $pandit->id,
        'sender'    => 'pandit',
        'message'   => $request->message,
    ]);

    // WebSocket ke through live event
    broadcast(new PanditMessageSent($message));

    return back()->with('success', 'Message sent!');
}

    public function deleteMessage($id)
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        PanditMessage::where('id', $id)->where('pandit_id', $pandit->id)->delete();

        return back()->with('success', 'Message deleted!');
    }

    // NOTIFICATIONS
    public function notifications()
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        $notifications = PanditNotification::where('pandit_id', $pandit->id)->latest()->get();
        PanditNotification::where('pandit_id', $pandit->id)->update(['is_read' => true]);

        return view('pandit.notifications', compact('pandit', 'notifications'));
    }

    public function saveAvailability(Request $request)
    {
        $pandit = $this->getPandit();
        if (!$pandit) return redirect()->route('pandit.login');

        $request->validate([
            'accept_new_bookings' => ['nullable', 'boolean'],
            'advance_booking_days' => ['required', 'integer', 'min:0', 'max:365'],
            'days' => ['nullable', 'array'],
            'days.*.status' => ['nullable', 'in:Available,Unavailable'],
            'days.*.from' => ['nullable', 'array'],
            'days.*.to' => ['nullable', 'array'],
            'days.*.from.*' => ['nullable', 'date_format:H:i'],
            'days.*.to.*' => ['nullable', 'date_format:H:i'],
        ]);

        $dayNames = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $days = $request->input('days', []);
        $normalizedSlots = [];
        $dayStatuses = [];

        foreach ($dayNames as $day) {
            $data = $days[$day] ?? [];
            $isAvailable = isset($data['status']) && $data['status'] === 'Available';
            $dayStatuses[$day] = $isAvailable;
            $froms = $data['from'] ?? [];
            $tos   = $data['to'] ?? [];

            foreach ($froms as $i => $from) {
                $to = $tos[$i] ?? null;

                if (empty($from) && empty($to)) {
                    continue;
                }

                if (empty($from) || empty($to) || $from >= $to) {
                    return back()
                        ->withErrors(['days' => "Each availability slot on {$day} must have a valid start and end time."])
                        ->withInput();
                }

                $normalizedSlots[$day][] = [
                    'pandit_id' => $pandit->id,
                    'day' => $day,
                    'start_time' => $from,
                    'end_time' => $to,
                    'is_available' => $isAvailable,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach ($normalizedSlots as $day => $slots) {
            $daySlots = collect($slots)
                ->sortBy('start_time')
                ->values();

            for ($i = 1; $i < $daySlots->count(); $i++) {
                if ($daySlots[$i - 1]['end_time'] > $daySlots[$i]['start_time']) {
                    return back()
                        ->withErrors(['days' => "Availability slots overlap on {$day}."])
                        ->withInput();
                }
            }
        }

        DB::transaction(function () use ($pandit, $request, $dayStatuses, $normalizedSlots) {
            PanditAvailabilitySetting::updateOrCreate(
                ['pandit_id' => $pandit->id],
                [
                    'accept_new_bookings' => $request->boolean('accept_new_bookings'),
                    'advance_booking_days' => (int) $request->input('advance_booking_days', 0),
                    'day_statuses' => $dayStatuses,
                ]
            );

            PanditAvailabilitySlot::where('pandit_id', $pandit->id)->delete();

            foreach ($normalizedSlots as $slots) {
                PanditAvailabilitySlot::insert($slots);
            }
        });

        return back()->with('success', 'Availability saved!');
    }
}
