<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminActivityLog;
use App\Models\Admin\DiyaSession;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Services\PanditBookingService;
use App\Services\PanditPayoutLedgerService;
use App\Services\VideoMeetingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'hawan');

        return $this->list($request, $type);
    }

    public function diya(Request $request)
    {
        return $this->list($request, 'diya');
    }

    public function pooja(Request $request)
    {
        return $this->list($request, 'pooja');
    }

    public function hawan(Request $request)
    {
        return $this->list($request, 'hawan');
    }

    public function show(string $type, string $id)
    {
        $record = $this->model($type)::with(['user', 'service', 'sankalp', 'pandit', 'panditPayouts'])->findOrFail($id);

        return view('admin.bookings.show', compact('record', 'type'));
    }

    public function update(Request $request, string $type, string $id)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', PanditBookingService::BOOKING_STATUSES)],
            'payment_status' => ['required', 'in:'.implode(',', PanditBookingService::PAYMENT_STATUSES)],
            'live_session_link' => ['nullable', 'url'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $record = DB::transaction(function () use ($type, $id, $data) {
            $record = $this->model($type)::whereKey($id)->lockForUpdate()->firstOrFail();
            $data['completed_at'] = $data['status'] === 'completed' ? now() : $record->completed_at;
            $record->update($data);

            if (in_array($type, ['hawan', 'pooja'], true)) {
                app(PanditPayoutLedgerService::class)->syncForBooking($record, 'admin_booking_update');
            }

            return $record;
        });

        if (in_array($type, ['hawan', 'pooja'], true)) {
            rescue(fn () => app(VideoMeetingService::class)->createForSessionIfReady($record));
        }

        $this->log('status_update', ucfirst($type).' booking #'.$record->id.' updated');

        return back()->with('success', 'Booking updated successfully.');
    }

    private function list(Request $request, string $type)
    {
        $records = $this->model($type)::with(['user', 'service', 'sankalp', 'pandit'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->payment_status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', compact('records', 'type'));
    }

    private function model(string $type): string
    {
        return match ($type) {
            'diya' => DiyaSession::class,
            'pooja' => PoojaSession::class,
            default => HawanSession::class,
        };
    }

    private function log(string $action, string $description): void
    {
        AdminActivityLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'module' => 'Bookings',
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
