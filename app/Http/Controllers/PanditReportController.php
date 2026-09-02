<?php

namespace App\Http\Controllers;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\Pandit\Pandit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PanditReportController extends Controller
{
    public function index(Request $request): View
    {
        $pandit = Auth::guard('pandit')->user();
        abort_unless($pandit, 403);

        $reports = Dispute::query()
            ->with(['disputable.service', 'disputable.sankalp', 'user'])
            ->whereIn('disputable_type', [HawanSession::class, PoojaSession::class])
            ->whereHasMorph('disputable', [HawanSession::class, PoojaSession::class], function ($query) use ($pandit) {
                $query->where('pandit_id', $pandit->id);
            })
            ->latest()
            ->paginate(12)
            ->through(fn (Dispute $dispute) => $this->reportRow($dispute));

        return view('pandit.reports.index', compact('pandit', 'reports'));
    }

    public function show(Request $request, Dispute $dispute): View
    {
        $pandit = $this->authorizedPandit($request, $dispute);
        $dispute->load(['user', 'evidences']);

        $booking = $dispute->disputable;
        $booking?->loadMissing(['service', 'user', 'sankalp', 'pandit']);

        return view('pandit.reports.show', [
            'pandit' => $pandit,
            'dispute' => $dispute,
            'booking' => $booking,
            'reportType' => $booking instanceof HawanSession ? 'Hawan' : 'Pooja',
            'reportReason' => $this->reasonLabel($dispute->reason),
            'reportStatus' => Str::of($dispute->status)->replace('_', ' ')->title(),
            'serviceName' => $this->serviceName($dispute, $booking),
            'userEvidences' => $dispute->evidences->where('uploaded_by_type', \App\Models\User::class),
            'panditEvidences' => $dispute->evidences
                ->where('uploaded_by_type', Pandit::class)
                ->where('uploaded_by_id', $pandit->id),
        ]);
    }

    public function respond(Request $request, Dispute $dispute): RedirectResponse
    {
        $pandit = $this->authorizedPandit($request, $dispute);

        if ($dispute->pandit_responded_at) {
            return back()->with('info', 'Response already submitted.');
        }

        $validated = $request->validate([
            'response' => ['required', 'string', 'max:5000'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $responseSaved = DB::transaction(function () use ($dispute, $validated) {
            $lockedDispute = Dispute::whereKey($dispute->id)->lockForUpdate()->firstOrFail();

            if ($lockedDispute->pandit_responded_at) {
                return false;
            }

            $lockedDispute->forceFill([
                'pandit_response' => $validated['response'],
                'pandit_responded_at' => now(),
                'status' => $lockedDispute->status === Dispute::STATUS_OPEN
                    ? Dispute::STATUS_UNDER_REVIEW
                    : $lockedDispute->status,
            ])->save();

            $dispute->forceFill($lockedDispute->only(['pandit_response', 'pandit_responded_at', 'status']));

            return true;
        });

        if (!$responseSaved) {
            return back()->with('info', 'Response already submitted.');
        }

        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $filePath = Storage::disk('local')->putFileAs(
                'dispute-evidences/'.$dispute->id.'/pandit',
                $file,
                Str::uuid().'.'.$file->extension()
            );

            $dispute->evidences()->create([
                'uploaded_by_type' => $pandit::class,
                'uploaded_by_id' => $pandit->id,
                'file_path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()
            ->route('pandit.reports.show', ['dispute' => $dispute])
            ->with('success', 'Response submitted successfully.');
    }

    public function evidence(Request $request, Dispute $dispute, DisputeEvidence $evidence): StreamedResponse
    {
        $this->authorizedPandit($request, $dispute);
        abort_unless((int) $evidence->dispute_id === (int) $dispute->id, 404);
        abort_unless(Storage::disk('local')->exists($evidence->file_path), 404);

        return Storage::disk('local')->response($evidence->file_path, $evidence->original_name, [
            'Content-Type' => $evidence->mime_type,
        ]);
    }

    private function authorizedPandit(Request $request, Dispute $dispute): Pandit
    {
        $pandit = Auth::guard('pandit')->user();
        abort_unless($pandit, 403);

        $booking = $dispute->disputable;
        abort_unless($booking instanceof HawanSession || $booking instanceof PoojaSession, 404);
        abort_unless((int) $booking->pandit_id === (int) $pandit->id, 403);

        return $pandit;
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

    private function reportRow(Dispute $dispute): array
    {
        $booking = $dispute->disputable;
        $type = $booking instanceof HawanSession ? 'hawan' : 'pooja';

        return [
            'id' => $dispute->id,
            'booking_id' => strtoupper($type).'-'.$booking?->id,
            'label' => ucfirst($type),
            'service_name' => $this->serviceName($dispute, $booking),
            'booking_date' => $booking?->booking_date,
            'slot' => $booking?->slot,
            'reason' => $this->reasonLabel($dispute->reason),
            'status' => Str::of($dispute->status)->replace('_', ' ')->title(),
            'reported_at' => $dispute->opened_at ?: $dispute->created_at,
            'response_status' => $dispute->pandit_responded_at ? 'Submitted' : 'Pending',
            'yajman' => $booking?->sankalp?->full_name ?? $dispute->user?->name ?? 'Not added',
            'report_url' => route('pandit.reports.show', ['dispute' => $dispute]),
        ];
    }
}
