<?php

namespace App\Http\Controllers;

use App\Models\Admin\DiyaSession;
use App\Models\Admin\HawanSession;
use App\Models\Admin\NotificationLog;
use App\Models\Admin\PoojaSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('pages.user-profile', [
            'user' => $user,
            'diyaBookings' => DiyaSession::with(['diya', 'deity', 'sankalp'])
                ->where('user_id', $user->id)
                ->latest()
                ->get(),
            'poojaBookings' => PoojaSession::with(['service', 'sankalp', 'latestPaymentAttempt'])
                ->where('user_id', $user->id)
                ->latest()
                ->get(),
            'hawanBookings' => HawanSession::with(['service', 'sankalp', 'latestPaymentAttempt'])
                ->where('user_id', $user->id)
                ->latest()
                ->get(),
            'bookingNotifications' => NotificationLog::where('user_id', $user->id)
                ->where('channel', 'my_bookings')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20', Rule::unique('users', 'mobile')->ignore($request->user()->id)],
            'dob' => ['nullable', 'date'],
            'gotra' => ['nullable', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}
