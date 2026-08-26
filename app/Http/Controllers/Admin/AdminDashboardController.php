<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\DiyaSession;
use App\Models\Admin\Donation;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\Admin\Service;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'Total users' => User::count(),
            'Total donations' => Donation::count(),
            'Total revenue' => 'Rs '.number_format((float) Donation::where('payment_status', 'paid')->sum('amount'), 2),
            'Total services' => Service::count(),
            'Total pooja bookings' => PoojaSession::count(),
            'Total hawan bookings' => HawanSession::count(),
            'Total diya sessions' => DiyaSession::count(),
            'Scheduled Diyas' => DiyaSession::scheduled()->count(),
            'Currently Glowing Diyas' => DiyaSession::currentlyGlowing()->count(),
            'Completed Diyas' => DiyaSession::completed()->count(),
            'Active sessions' => DiyaSession::currentlyGlowing()->count() + PoojaSession::where('status', 'active')->count() + HawanSession::where('status', 'active')->count(),
            'Completed sessions' => DiyaSession::completed()->count() + PoojaSession::where('status', 'completed')->count() + HawanSession::where('status', 'completed')->count(),
            'Failed payments' => Donation::where('payment_status', 'failed')->count(),
        ];

        $latestHawan = HawanSession::with(['service', 'sankalp'])->latest()->limit(6)->get();
        $latestDonations = Donation::with(['user', 'service'])->latest()->limit(6)->get();

        return view('admin.dashboard.index', compact('stats', 'latestHawan', 'latestDonations'));
    }
}
