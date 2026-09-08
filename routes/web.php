<?php

use App\Http\Controllers\Admin\AdminAudioController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDeityController;
use App\Http\Controllers\Admin\AdminDiyaController;
use App\Http\Controllers\Admin\AdminDisputeController;
use App\Http\Controllers\Admin\AdminDonationController;
use App\Http\Controllers\Admin\AdminHawanController;
use App\Http\Controllers\Admin\AdminMessageController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminPanditController;
use App\Http\Controllers\Admin\AdminPayoutController;
use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\AdminPlaylistController;
use App\Http\Controllers\Admin\AdminPoojaController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminServiceCategoryController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\DiyaController;
use App\Http\Controllers\HawanController;
use App\Http\Controllers\LiveSessionController;
use App\Http\Controllers\PanditController;
use App\Http\Controllers\PanditLiveSessionController;
use App\Http\Controllers\PanditReportController;
use App\Http\Controllers\PanditSelectionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PoojaController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\VideoMeetingSdkController;
use App\Models\Admin\HawanSession;
use App\Models\Admin\DiyaSession;
use App\Models\Admin\PoojaSession;
use App\Models\Dispute;
use App\Models\VideoMeetingAttendance;
use App\Services\PanditBookingService;
use App\Services\VideoMeetingProviderManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;




use App\Http\Controllers\PanditZoomController;








Route::get('/check-pandit-auth', function () {

    return [
        'logged_in' => Auth::guard('pandit')->check(),
        'pandit_id' => Auth::guard('pandit')->id(),
        'pandit' => Auth::guard('pandit')->user(),
    ];

})->middleware('auth:pandit');

Route::get('/', function () {
    $liveDiyas = collect();
    $liveDiyaCount = 0;
    $diyaLitToday = 0;

    if (Schema::hasTable('diya_sessions')) {
        $liveDiyas = DiyaSession::with(['diya', 'deity'])
            ->where('payment_status', 'paid')
            ->currentlyGlowing()
            ->latest()
            ->limit(6)
            ->get();

        $liveDiyaCount = DiyaSession::where('payment_status', 'paid')
            ->currentlyGlowing()
            ->count();

        $diyaLitToday = DiyaSession::where('payment_status', 'paid')
            ->whereDate('start_at', today())
            ->count();
    }

    return view('welcome', compact('liveDiyas', 'liveDiyaCount', 'diyaLitToday'));
})->name('home');

Route::get('/light-diya', [DiyaController::class, 'index'])->middleware('auth')->name('light-diya');
// Route::post('/light-diya', [DiyaController::class, 'store'])->name('diya.store');
Route::get('/diya-session/{session}', [DiyaController::class, 'session'])->name('diya.session');
Route::get('/personalized-pooja', [PoojaController::class, 'index'])->name('personalized-pooja');
Route::get('/hawan', [HawanController::class, 'index'])->name('hawan');
Route::view('/lakshmi-pooja', 'pages.lakshmi-pooja')->name('lakshmi-pooja');
Route::view('/how-it-works', 'pages.how-it-works')->name('how.works');
Route::redirect('/live', '/live-sessions')->name('live');
Route::get('/live-family/{token}', [LiveSessionController::class, 'joinInvite'])->name('live.family.join');
Route::post('/live-family/{token}/meeting-sdk', [LiveSessionController::class, 'inviteSdkConfig'])->name('live.family.sdk');
Route::post('/live-family/{token}/leave', [LiveSessionController::class, 'leaveInvite'])->name('live.family.leave');
Route::get('/live-sessions', function () {
    $poojaBookings = PoojaSession::with(['sankalp', 'videoMeeting'])
        ->where('payment_status', 'paid')
        ->where('status', 'confirmed')
        ->whereHas('videoMeeting');

    $hawanBookings = HawanSession::with(['sankalp', 'videoMeeting'])
        ->where('payment_status', 'paid')
        ->where('status', 'confirmed')
        ->whereHas('videoMeeting');

    if (!Auth::guard('admin')->check()) {
        if (Auth::guard('pandit')->check()) {
            $poojaBookings->where('pandit_id', Auth::guard('pandit')->id());
            $hawanBookings->where('pandit_id', Auth::guard('pandit')->id());
        } elseif (Auth::check()) {
            $poojaBookings->where('user_id', Auth::id());
            $hawanBookings->where('user_id', Auth::id());
        } else {
            $poojaBookings->whereRaw('1 = 0');
            $hawanBookings->whereRaw('1 = 0');
        }
    }

    $poojaBookings = $poojaBookings->latest()->limit(6)->get();
    $hawanBookings = $hawanBookings->latest()->limit(6)->get();

    return view('pages.live-sessions', compact('poojaBookings', 'hawanBookings'));
})->name('live.sessions');
Route::get('/live-sessions/pooja', function () {
    $poojaBookings = PoojaSession::with(['sankalp', 'videoMeeting'])
        ->where('payment_status', 'paid')
        ->where('status', 'confirmed')
        ->whereHas('videoMeeting');

    if (!Auth::guard('admin')->check()) {
        if (Auth::guard('pandit')->check()) {
            $poojaBookings->where('pandit_id', Auth::guard('pandit')->id());
        } elseif (Auth::check()) {
            $poojaBookings->where('user_id', Auth::id());
        } else {
            $poojaBookings->whereRaw('1 = 0');
        }
    }

    $poojaBookings = $poojaBookings->latest()->paginate(12);

    return view('pages.live-pooja-sessions', compact('poojaBookings'));
})->name('live.sessions.pooja');
Route::get('/live-sessions/hawan', function () {
    $hawanBookings = HawanSession::with(['sankalp', 'videoMeeting'])
        ->where('payment_status', 'paid')
        ->where('status', 'confirmed')
        ->whereHas('videoMeeting');

    if (!Auth::guard('admin')->check()) {
        if (Auth::guard('pandit')->check()) {
            $hawanBookings->where('pandit_id', Auth::guard('pandit')->id());
        } elseif (Auth::check()) {
            $hawanBookings->where('user_id', Auth::id());
        } else {
            $hawanBookings->whereRaw('1 = 0');
        }
    }

    $hawanBookings = $hawanBookings->latest()->paginate(12);

    return view('pages.live-hawan-sessions', compact('hawanBookings'));
})->name('live.sessions.hawan');
Route::redirect('/live/session/hawan', '/live-sessions/hawan')->name('live.session.hawan.index');
Route::get('/live-sessions/{type}/{id}/join', function (string $type, string $id) {
    $bookingRecord = $type === 'pooja'
        ? PoojaSession::with('videoMeeting')->findOrFail($id)
        : HawanSession::with('videoMeeting')->findOrFail($id);

    abort_unless(
        app(PanditBookingService::class)->canAccessPrivateSession($bookingRecord, request()),
        403
    );

    abort_unless(
        $bookingRecord->payment_status === 'paid'
        && $bookingRecord->status === 'confirmed'
        && filled($bookingRecord->videoMeeting?->join_url),
        403
    );

    $participantType = VideoMeetingAttendance::PARTICIPANT_UNKNOWN;
    $participantId = null;

    if (Auth::check() && (int) Auth::id() === (int) $bookingRecord->user_id) {
        $participantType = VideoMeetingAttendance::PARTICIPANT_USER;
        $participantId = Auth::id();
    } elseif (Auth::guard('pandit')->check() && (int) Auth::guard('pandit')->id() === (int) $bookingRecord->pandit_id) {
        $participantType = VideoMeetingAttendance::PARTICIPANT_PANDIT;
        $participantId = Auth::guard('pandit')->id();
    }

    VideoMeetingAttendance::recordJoinAttempt($bookingRecord, $participantType, $participantId, [
        'source' => 'external_join_route',
        'route' => 'live.session.join',
    ]);

    return redirect()->away($bookingRecord->videoMeeting->join_url);
})->whereIn('type', ['pooja', 'hawan'])->name('live.session.join');
Route::get('/live-sessions/{type}/{id}/start', function (string $type, string $id) {
    $bookingRecord = $type === 'pooja'
        ? PoojaSession::with('videoMeeting')->findOrFail($id)
        : HawanSession::with('videoMeeting')->findOrFail($id);

    abort_unless(
        app(PanditBookingService::class)->canAccessPrivateSession($bookingRecord, request()),
        403
    );

    abort_unless(
        Auth::guard('pandit')->check()
        && (int) Auth::guard('pandit')->id() === (int) $bookingRecord->pandit_id
        && $bookingRecord->payment_status === 'paid'
        && $bookingRecord->status === 'confirmed'
        && filled($bookingRecord->videoMeeting?->external_meeting_id),
        403
    );

    VideoMeetingAttendance::recordJoinAttempt(
        $bookingRecord,
        VideoMeetingAttendance::PARTICIPANT_PANDIT,
        Auth::guard('pandit')->id(),
        [
            'source' => 'external_start_route',
            'route' => 'live.session.start',
        ]
    );

    return redirect()->away(
        app(VideoMeetingProviderManager::class)
            ->for($bookingRecord->videoMeeting->provider)
            ->hostUrl($bookingRecord->videoMeeting)
    );
})->whereIn('type', ['pooja', 'hawan'])->name('live.session.start');
Route::post('/live-sessions/{type}/{id}/meeting-sdk', [VideoMeetingSdkController::class, 'config'])
    ->whereIn('type', ['pooja', 'hawan'])
    ->name('live.session.sdk');
Route::get('/live-sessions/{type}/{id}', function (string $type, string $id) {
    $bookingRecord = null;

    if ($type === 'diya') {
        return redirect()->route('diya.session', ['session' => $id, 'token' => request('token')]);
    }

    if ($type === 'aarti') {
        return view('pages.live', [
            'sessionType' => $type,
            'sessionId' => $id,
            'bookingRecord' => null,
        ]);
    }

    if ($type === 'hawan') {
        $bookingRecord = HawanSession::with(['sankalp', 'user', 'pandit', 'videoMeeting'])->findOrFail($id);
    }

    if ($type === 'pooja') {
        $bookingRecord = PoojaSession::with(['sankalp', 'user', 'pandit', 'videoMeeting'])->findOrFail($id);
    }

    abort_unless(
        $bookingRecord && app(PanditBookingService::class)->canAccessPrivateSession($bookingRecord, request()),
        403
    );

    $activeDispute = null;

    if (Auth::check() && (int) $bookingRecord->user_id === (int) Auth::id()) {
        $activeDispute = $bookingRecord->disputes()
            ->where('user_id', Auth::id())
            ->whereIn('status', [Dispute::STATUS_OPEN, Dispute::STATUS_UNDER_REVIEW])
            ->latest()
            ->first();
    }

    return view('pages.live', [
        'sessionType' => $type,
        'sessionId' => $id,
        'bookingRecord' => $bookingRecord,
        'embeddedMeetingView' => $bookingRecord?->videoMeeting?->provider === 'zoom' ? 'video-meetings.zoom-sdk' : null,
        'familyInvites' => app(LiveSessionController::class)->familyInvites($bookingRecord),
        'canManageFamily' => Auth::check() && (int) $bookingRecord->user_id === (int) Auth::id(),
        'activeDispute' => $activeDispute,
    ]);
})->whereIn('type', ['aarti', 'pooja', 'hawan', 'diya'])->name('live.session');
Route::get('/book-pooja', fn () => redirect('/personalized-pooja'))->name('pooja.booking');
Route::get('/hawan-booking', fn () => redirect('/hawan'))->name('hawan.booking');
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs');
Route::get('/blogs/category/{slug}', [BlogController::class, 'category'])->name('blogs.category');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');
Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

Route::post('/contact', function () {
    // Handle form submission
    return redirect()->back()->with('success', 'Message sent!');
})->name('contact.submit');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::redirect('/registration', '/signup')->name('registration');
Route::post('/payments/razorpay/webhook', [PaymentController::class, 'webhook'])->name('payments.razorpay.webhook');

Route::redirect('/pandit/login', '/login')->name('pandit.login');
Route::get('/pandit/register', [AuthController::class, 'showPanditRegister'])->name('pandit.register');

Route::prefix('pandit')->name('pandit.')->middleware(['auth:pandit', 'pandit.nocache'])->group(function () {
    Route::post('/logout', [PanditController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [PanditController::class, 'dashboard'])->name('dashboard');
    Route::get('/bookings', [PanditController::class, 'bookings'])->name('bookings.index');
    Route::get('/bookings/{type}/{id}', [PanditController::class, 'showBooking'])
        ->whereIn('type', ['pooja', 'hawan'])
        ->name('bookings.show');
    Route::get('/live-sessions', [PanditLiveSessionController::class, 'index'])->name('live-sessions.index');
    Route::get('/live-sessions/{type}/{id}', [PanditLiveSessionController::class, 'show'])
        ->whereIn('type', ['pooja', 'hawan'])
        ->name('live-sessions.show');
    Route::post('/resubmit', [PanditController::class, 'resubmit'])->name('resubmit');
    Route::get('/profile', [PanditController::class, 'profile'])->name('profile');
    Route::post('/profile', [PanditController::class, 'updateProfile'])->name('profile.update');
    Route::get('/qualification', [PanditController::class, 'qualification'])->name('qualification');
    Route::post('/qualification', [PanditController::class, 'updateQualification'])->name('qualification.update');
    Route::get('/services', [PanditController::class, 'services'])->name('services');
    Route::post('/services', [PanditController::class, 'addService'])->name('services.add');
    Route::delete('/services/{id}', [PanditController::class, 'deleteService'])->name('services.delete');
    Route::get('/availability', [PanditController::class, 'availability'])->name('availability');
    Route::post('/availability', [PanditController::class, 'saveAvailability'])->name('availability.save');
    Route::post('/online-setup', [PanditController::class, 'saveOnlineSetup'])->name('online-setup.save');
    Route::get('/documents', [PanditController::class, 'documents'])->name('documents');
    Route::post('/documents', [PanditController::class, 'updateDocuments'])->name('documents.update');
    Route::get('/bank-details', [PanditController::class, 'bankDetails'])->name('bank-details');
    Route::post('/bank-details', [PanditController::class, 'updateBankDetails'])->name('bank-details.update');
    Route::get('/notifications', [PanditController::class, 'notifications'])->name('notifications');
    Route::get('/messages', [PanditController::class, 'messages'])->name('messages');
    Route::post('/messages', [PanditController::class, 'sendMessage'])->name('messages.send');
    Route::delete('/messages/{id}', [PanditController::class, 'deleteMessage'])->name('messages.delete');
    Route::post('/bookings/{type}/{id}/accept', [PanditController::class, 'acceptBooking'])
        ->whereIn('type', ['pooja', 'hawan'])
        ->name('bookings.accept');
    Route::post('/bookings/{type}/{id}/cancel', [PanditController::class, 'cancelBooking'])
        ->whereIn('type', ['pooja', 'hawan'])
        ->name('bookings.cancel');
    Route::get('/reports', [PanditReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{dispute}', [PanditReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{dispute}/response', [PanditReportController::class, 'respond'])->name('reports.respond');
    Route::get('/reports/{dispute}/evidences/{evidence}', [PanditReportController::class, 'evidence'])->name('reports.evidence');










    Route::get('/zoom/connect', [PanditZoomController::class, 'connect'])
    ->name('zoom.connect');

Route::get('/zoom/callback', [PanditZoomController::class, 'callback'])
    ->name('zoom.callback');
});

Route::post('/login/send-otp', [AuthController::class, 'sendOTP'])->name('login.send-otp');
Route::post('/login/verify-otp', [AuthController::class, 'verifyOTP'])->name('login.verify-otp');
Route::get('/signup', [AuthController::class, 'showUserRegister'])->name('signup');
Route::post('/register/{type}/send-otp', [AuthController::class, 'sendRegistrationOtp'])
    ->whereIn('type', ['user', 'pandit'])
    ->name('register.send-otp');
Route::post('/register/{type}/verify-otp', [AuthController::class, 'verifyRegistrationOtp'])
    ->whereIn('type', ['user', 'pandit'])
    ->name('register.verify-otp');

// Route::get('/book-hawan/{slug}', [HawanController::class, 'show'])->name('hawan.show');
// Route::get('/book-hawan/{slug}/pandits', [PanditSelectionController::class, 'index'])->name('hawan.pandits');
// Route::get('/book-hawan/{slug}/pandits/{pandit}', [PanditSelectionController::class, 'show'])->name('hawan.pandits.show');
// Route::post('/book-hawan/{slug}/pandits/{pandit}/select', [PanditSelectionController::class, 'select'])->name('hawan.pandits.select');
// Route::get('/book-hawan/{slug}/review', [HawanController::class, 'review'])->name('hawan.review');
// Route::post('/book-hawan', [HawanController::class, 'store'])->name('hawan.store');
// Route::get('/book-pooja/{slug}', [PoojaController::class, 'show'])->name('pooja.show');
// Route::get('/book-pooja/{slug}/pandits', [PanditSelectionController::class, 'poojaIndex'])->name('pooja.pandits');
// Route::get('/book-pooja/{slug}/pandits/{pandit}', [PanditSelectionController::class, 'poojaShow'])->name('pooja.pandits.show');
// Route::post('/book-pooja/{slug}/pandits/{pandit}/select', [PanditSelectionController::class, 'poojaSelect'])->name('pooja.pandits.select');
// Route::get('/book-pooja/{slug}/review', [PoojaController::class, 'review'])->name('pooja.review');
// Route::post('/book-pooja', [PoojaController::class, 'store'])->name('pooja.store');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.store');
    });

    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->middleware('admin.auth')
        ->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::get('/pandits', [AdminPanditController::class, 'index'])->name('pandits.index');
        Route::get('/pandits/{id}', [AdminPanditController::class, 'show'])->name('pandits.show');
        Route::post('/pandits/{id}/status', [AdminPanditController::class, 'updateStatus'])->name('pandits.status');
        Route::post('/pandits/{id}/services/{serviceId}/status', [AdminPanditController::class, 'updateServiceStatus'])->name('pandits.services.status');

        Route::resource('/admins', AdminUserController::class)
            ->except(['show'])
            ->middleware('admin.permission:manage-admins');

        Route::get('/users', [AdminUserController::class, 'index'])
            ->middleware('admin.permission:view-users')
            ->name('users.index');

        Route::resource('/roles', AdminRoleController::class)
            ->parameters(['roles' => 'role'])
            ->except(['show'])
            ->middleware('admin.permission:manage-roles');

        Route::resource('/permissions', AdminPermissionController::class)
            ->middleware('admin.permission:manage-permissions');

        Route::resource('/service-categories', AdminServiceCategoryController::class)
            ->middleware('admin.permission:manage-service-categories');

        Route::resource('/deities', AdminDeityController::class)
            ->middleware('admin.permission:manage-deities');

        Route::post('/diyas/{diya}/toggle-status', [AdminDiyaController::class, 'toggleStatus'])
            ->middleware('admin.permission:manage-diyas')
            ->name('diyas.toggle-status');
        Route::get('/diyas/mantra-audios', [AdminDiyaController::class, 'mantraAudios'])
            ->middleware('admin.permission:manage-diyas')
            ->name('diyas.mantra-audios');
        Route::resource('/diyas', AdminDiyaController::class)
            ->middleware('admin.permission:manage-diyas');

        Route::resource('/services', AdminServiceController::class)
            ->middleware('admin.permission:manage-services');

        Route::get('/bookings', [AdminBookingController::class, 'index'])
            ->middleware('admin.permission:view-bookings')
            ->name('bookings.index');
        Route::get('/bookings/diya', [AdminBookingController::class, 'diya'])
            ->middleware('admin.permission:view-bookings')
            ->name('bookings.diya');
        Route::get('/bookings/pooja', [AdminBookingController::class, 'pooja'])
            ->middleware('admin.permission:view-bookings')
            ->name('bookings.pooja');
        Route::get('/bookings/hawan', [AdminBookingController::class, 'hawan'])
            ->middleware('admin.permission:view-bookings')
            ->name('bookings.hawan');
        Route::get('/bookings/{type}/{id}', [AdminBookingController::class, 'show'])
            ->whereIn('type', ['diya', 'pooja', 'hawan'])
            ->middleware('admin.permission:view-bookings')
            ->name('bookings.show');
        Route::put('/bookings/{type}/{id}', [AdminBookingController::class, 'update'])
            ->whereIn('type', ['diya', 'pooja', 'hawan'])
            ->middleware('admin.permission:update-bookings')
            ->name('bookings.update');

        Route::get('/donations/export', [AdminDonationController::class, 'export'])
            ->middleware('admin.permission:export-donations')
            ->name('donations.export');
        Route::get('/donations', [AdminDonationController::class, 'index'])
            ->middleware('admin.permission:view-donations')
            ->name('donations.index');

        Route::resource('/audio', AdminAudioController::class)
            ->middleware('admin.permission:manage-audio');
        Route::resource('/playlists', AdminPlaylistController::class)
            ->middleware('admin.permission:manage-playlists');
        Route::resource('/blogs', AdminBlogController::class)
            ->middleware('admin.permission:manage-blogs');

        Route::resource('/hawans', AdminHawanController::class)
            ->middleware('admin.permission:manage-services');

        Route::resource('/poojas', AdminPoojaController::class)
            ->middleware('admin.permission:manage-services');

        Route::get('/notifications', [AdminNotificationController::class, 'index'])
            ->middleware('admin.permission:view-notifications')
            ->name('notifications.index');

        Route::get('/pandit-messages', [AdminMessageController::class, 'index'])->name('pandit-messages.index');
        Route::get('/pandit-messages/{panditId}', [AdminMessageController::class, 'show'])->name('pandit-messages.show');
        Route::post('/pandit-messages/{panditId}/reply', [AdminMessageController::class, 'reply'])->name('pandit-messages.reply');
        Route::delete('/pandit-messages/{id}/delete', [AdminMessageController::class, 'delete'])->name('pandit-messages.delete');

        Route::get('/reports', [AdminReportController::class, 'index'])
            ->middleware('admin.permission:view-reports')
            ->name('reports.index');

        Route::get('/payouts', [AdminPayoutController::class, 'index'])
            ->middleware('admin.permission:view-reports')
            ->name('payouts.index');

        Route::get('/disputes', [AdminDisputeController::class, 'index'])
            ->middleware('admin.permission:view-reports')
            ->name('disputes.index');
        Route::get('/disputes/{dispute}', [AdminDisputeController::class, 'show'])
            ->middleware('admin.permission:view-reports')
            ->name('disputes.show');
        Route::patch('/disputes/{dispute}', [AdminDisputeController::class, 'update'])
            ->middleware('admin.permission:view-reports')
            ->name('disputes.update');
        Route::post('/disputes/{dispute}/refund-user', [AdminDisputeController::class, 'refundUser'])
            ->middleware('admin.permission:view-reports')
            ->name('disputes.refund-user');
        Route::post('/disputes/{dispute}/resolve-pandit-favour', [AdminDisputeController::class, 'resolvePanditFavour'])
            ->middleware('admin.permission:view-reports')
            ->name('disputes.resolve-pandit-favour');
        Route::get('/disputes/{dispute}/evidences/{evidence}', [AdminDisputeController::class, 'evidence'])
            ->middleware('admin.permission:view-reports')
            ->name('disputes.evidence');

        Route::resource('/settings', AdminSettingController::class)
            ->middleware('admin.permission:manage-settings');
    });
});




Route::middleware('auth')->group(function () {
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/profile', [UserProfileController::class, 'show'])->name('user.profile');
Route::put('/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
Route::get('/notifications', [UserNotificationController::class, 'index'])->name('user.notifications.index');
Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('user.notifications.read-all');
Route::post('/notifications/{notification}/read', [UserNotificationController::class, 'markRead'])->name('user.notifications.read');
Route::post('/payments/bookings/{type}/{id}/retry', [PaymentController::class, 'retry'])
    ->whereIn('type', ['pooja', 'hawan', 'diya'])
    ->name('payments.bookings.retry');
Route::post('/payments/razorpay/verify', [PaymentController::class, 'verify'])->name('payments.razorpay.verify');
Route::post('/payments/razorpay/failure', [PaymentController::class, 'failure'])->name('payments.razorpay.failure');
Route::post('/live-sessions/{type}/{id}/family-invites', [LiveSessionController::class, 'storeInvite'])
    ->whereIn('type', ['pooja', 'hawan'])
    ->name('live.family.store');
Route::post('/live-sessions/{type}/{id}/family-invites/{invite}/revoke', [LiveSessionController::class, 'revokeInvite'])
    ->whereIn('type', ['pooja', 'hawan'])
    ->name('live.family.revoke');
Route::post('/live-sessions/{type}/{id}/dakshina', [LiveSessionController::class, 'payDakshina'])
    ->whereIn('type', ['pooja', 'hawan'])
    ->name('live.dakshina.pay');
Route::post('/live-sessions/{type}/{id}/issue-report', [LiveSessionController::class, 'storeIssueReport'])
    ->whereIn('type', ['pooja', 'hawan'])
    ->name('live.issue-report.store');
Route::get('/reports/{dispute}', [UserReportController::class, 'show'])->name('user.reports.show');
Route::get('/reports/{dispute}/evidences/{evidence}', [UserReportController::class, 'evidence'])->name('user.reports.evidence');
Route::post('/light-diya', [DiyaController::class, 'store'])->name('diya.store');

Route::get('/book-hawan/{slug}', [HawanController::class, 'show'])->name('hawan.show');
Route::get('/book-hawan/{slug}/pandits', [PanditSelectionController::class, 'index'])->name('hawan.pandits');
Route::get('/book-hawan/{slug}/pandits/{pandit}', [PanditSelectionController::class, 'show'])->name('hawan.pandits.show');
Route::post('/book-hawan/{slug}/pandits/{pandit}/select', [PanditSelectionController::class, 'select'])->name('hawan.pandits.select');
Route::get('/book-hawan/{slug}/review', [HawanController::class, 'review'])->name('hawan.review');
Route::post('/book-hawan', [HawanController::class, 'store'])->name('hawan.store');
Route::get('/book-pooja/{slug}', [PoojaController::class, 'show'])->name('pooja.show');
Route::get('/book-pooja/{slug}/pandits', [PanditSelectionController::class, 'poojaIndex'])->name('pooja.pandits');
Route::get('/book-pooja/{slug}/pandits/{pandit}', [PanditSelectionController::class, 'poojaShow'])->name('pooja.pandits.show');
Route::post('/book-pooja/{slug}/pandits/{pandit}/select', [PanditSelectionController::class, 'poojaSelect'])->name('pooja.pandits.select');
Route::get('/book-pooja/{slug}/review', [PoojaController::class, 'review'])->name('pooja.review');
Route::post('/book-pooja', [PoojaController::class, 'store'])->name('pooja.store');


});





// Route::get('/pandit/zoom/callback', function () {
//     return 'Zoom callback working';
// })->name('pandit.zoom.callback');







use App\Http\Controllers\ZoomWebhookController;

Route::post('/zoom/webhook', [ZoomWebhookController::class, 'handle'])
    ->name('zoom.webhook');
