<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditNotification;
use App\Models\Pandit\PanditService;
use Illuminate\Http\Request;

class AdminPanditController extends Controller
{
    public function index(Request $request)
    {
        $query = Pandit::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('mobile', 'like', '%' . $request->search . '%');
            });
        }

        $pandits = $query->latest()->paginate(15)->withQueryString();

        return view('admin.pandits.index', compact('pandits'));
    }

    public function show($id)
    {
        $pandit = Pandit::with([
            'qualification', 'services', 'languages',
            'availabilitySlots', 'onlineSetup', 'document', 'bankDetail'
        ])->findOrFail($id);

        return view('admin.pandits.show', compact('pandit'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:under_review,needs_correction,verified,rejected,suspended',
            'admin_remark' => 'nullable|string|max:1000',
        ]);

        $pandit = Pandit::findOrFail($id);
        $pandit->update([
            'status'       => $request->status,
            'admin_remark' => $request->admin_remark,
            'reviewed_at'  => now(),
        ]);

        if ($request->filled('admin_remark')) {
            PanditNotification::create([
                'pandit_id' => $pandit->id,
                'title'     => 'Admin Remark — Action Required',
                'message'   => $request->admin_remark,
            ]);
        }

        return back()->with('success', 'Pandit status updated to ' . $request->status);
    }

    public function updateServiceStatus(Request $request, $id, $serviceId)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        PanditService::where('pandit_id', $id)
            ->where('id', $serviceId)
            ->update(['status' => $request->status]);

        return back()->with('success', 'Service status updated to ' . $request->status);
    }
}
