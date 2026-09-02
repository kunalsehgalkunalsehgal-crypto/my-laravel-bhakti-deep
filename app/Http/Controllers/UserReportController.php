<?php

namespace App\Http\Controllers;

use App\Models\Admin\HawanSession;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserReportController extends Controller
{
    public function show(Request $request, Dispute $dispute): View
    {
        $this->authorizeOwner($request, $dispute);

        $dispute->load(['evidences', 'pandit']);
        $booking = $dispute->disputable;
        $booking?->loadMissing(['service', 'pandit']);

        return view('pages.user-report-show', [
            'dispute' => $dispute,
            'booking' => $booking,
            'reportType' => $booking instanceof HawanSession ? 'Hawan' : 'Pooja',
            'reportReason' => $this->reasonLabel($dispute->reason),
            'reportStatus' => Str::of($dispute->status)->replace('_', ' ')->title(),
            'serviceName' => $this->serviceName($dispute, $booking),
        ]);
    }

    public function evidence(Request $request, Dispute $dispute, DisputeEvidence $evidence): StreamedResponse
    {
        $this->authorizeOwner($request, $dispute);
        abort_unless((int) $evidence->dispute_id === (int) $dispute->id, 404);
        abort_unless(Storage::disk('local')->exists($evidence->file_path), 404);

        return Storage::disk('local')->response($evidence->file_path, $evidence->original_name, [
            'Content-Type' => $evidence->mime_type,
        ]);
    }

    private function authorizeOwner(Request $request, Dispute $dispute): void
    {
        abort_unless($request->user() && (int) $request->user()->id === (int) $dispute->user_id, 403);
    }

    private function reasonLabel(string $reason): string
    {
        return [
            'pandit_not_joined' => 'Pandit not joined',
            'pandit_joined_late' => 'Pandit joined late',
            'session_incomplete' => 'Session incomplete',
            'wrong_service' => 'Wrong service',
            'technical_issue' => 'Technical issue',
            'behaviour_issue' => 'Behaviour issue',
            'other' => 'Other',
        ][$reason] ?? Str::of($reason)->replace('_', ' ')->title();
    }

    private function serviceName(Dispute $dispute, $booking): string
    {
        $meta = $booking?->admin_note ? (json_decode($booking->admin_note, true) ?: []) : [];

        return $meta['hawan_name']
            ?? $meta['pooja_name']
            ?? $booking?->service?->name
            ?? ($dispute->disputable_type === HawanSession::class ? 'Hawan' : 'Pooja');
    }
}
