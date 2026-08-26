<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Donation;
use App\Models\Admin\HawanSession;
use App\Models\Admin\Service;
use App\Models\User;

class AdminReportController extends Controller
{
    public function index()
    {
        $reports = [
            'Users' => User::count(),
            'Active services' => Service::where('status', 'active')->count(),
            'Paid donations' => Donation::where('payment_status', 'paid')->count(),
            'Revenue' => 'Rs '.number_format((float) Donation::where('payment_status', 'paid')->sum('amount'), 2),
            'Pending hawan bookings' => HawanSession::where('status', 'pending')->count(),
            'Completed hawan bookings' => HawanSession::where('status', 'completed')->count(),
        ];

        return view('admin.reports.index', compact('reports'));
    }
}
