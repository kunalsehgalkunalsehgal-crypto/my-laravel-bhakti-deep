<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\PanditPayout;
use Illuminate\Http\Request;

class AdminPayoutController extends Controller
{
    public function index(Request $request)
    {
        $statuses = [
            PanditPayout::STATUS_HOLD,
            PanditPayout::STATUS_READY,
            PanditPayout::STATUS_PROCESSING,
            PanditPayout::STATUS_PAID,
            PanditPayout::STATUS_CANCELLED,
            PanditPayout::STATUS_FAILED,
        ];
        $status = $request->query('status');

        $payouts = PanditPayout::with(['pandit', 'paymentAttempt', 'session'])
            ->when(in_array($status, $statuses, true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payouts.index', [
            'payouts' => $payouts,
            'statuses' => $statuses,
            'status' => $status,
            'routeAutomatic' => config('services.payouts.mode', 'manual') === 'route'
                && (bool) config('services.payouts.route_enabled', false),
            'payoutMode' => config('services.payouts.mode', 'manual'),
            'readyTotal' => PanditPayout::where('status', PanditPayout::STATUS_READY)->sum('pandit_amount'),
            'hawanSessionClass' => HawanSession::class,
            'poojaSessionClass' => PoojaSession::class,
        ]);
    }
}
