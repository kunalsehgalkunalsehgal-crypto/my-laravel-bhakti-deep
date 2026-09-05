<?php

namespace App\Http\Controllers;

use App\Models\Admin\NotificationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = NotificationLog::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15)
            ->through(fn (NotificationLog $notification) => [
                'record' => $notification,
                'category' => $this->category($notification),
                'icon' => $this->icon($notification),
            ]);

        return view('pages.notifications', [
            'notifications' => $notifications,
            'unreadCount' => NotificationLog::where('user_id', $request->user()->id)
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    public function markRead(Request $request, NotificationLog $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        NotificationLog::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    private function category(NotificationLog $notification): string
    {
        $type = Str::lower((string) $notification->message_type);
        $text = $type.' '.Str::lower((string) $notification->subject).' '.Str::lower((string) $notification->message);

        return match (true) {
            str_contains($text, 'refund') => 'Refund',
            str_contains($text, 'payment') => 'Payment',
            str_contains($text, 'report') || str_contains($text, 'dispute') || str_contains($text, 'issue') => 'Report',
            str_contains($text, 'session') || str_contains($text, 'meeting') || str_contains($text, 'live') => 'Session',
            str_contains($text, 'booking') => 'Booking',
            default => Str::of((string) $notification->channel)->replace('_', ' ')->title()->toString(),
        };
    }

    private function icon(NotificationLog $notification): string
    {
        return match ($this->category($notification)) {
            'Refund' => 'bi-arrow-counterclockwise',
            'Payment' => 'bi-credit-card',
            'Report' => 'bi-exclamation-triangle',
            'Session' => 'bi-camera-video',
            'Booking' => 'bi-calendar2-check',
            default => 'bi-bell',
        };
    }
}
