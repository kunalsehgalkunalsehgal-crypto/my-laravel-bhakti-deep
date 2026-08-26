<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\NotificationLog;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $records = NotificationLog::when($request->filled('delivery_status'), fn ($query) => $query->where('delivery_status', $request->delivery_status))
            ->when($request->filled('channel'), fn ($query) => $query->where('channel', $request->channel))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.notifications.index', compact('records'));
    }
}
