<?php

namespace Tests\Feature;

use App\Models\Admin\HawanSession;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\Pandit\Pandit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanditReportResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_pandit_can_view_report_from_dashboard_and_submit_response_with_proof(): void
    {
        Storage::fake('local');
        [$user, $pandit, $session, $dispute] = $this->reportedSession();

        Storage::disk('local')->put('dispute-evidences/'.$dispute->id.'/user-proof.pdf', 'user proof');
        $userEvidence = $dispute->evidences()->create([
            'uploaded_by_type' => User::class,
            'uploaded_by_id' => $user->id,
            'file_path' => 'dispute-evidences/'.$dispute->id.'/user-proof.pdf',
            'original_name' => 'user-proof.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 10,
        ]);

        $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.dashboard'))
            ->assertOk()
            ->assertSee('Reports')
            ->assertSee('Open Reports')
            ->assertSee('Reported Bookings')
            ->assertSee('View Report');

        $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.reports.index'))
            ->assertOk()
            ->assertSee('Reports')
            ->assertSee('Mahamrityunjaya Hawan')
            ->assertSee('View Report');

        $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.reports.show', ['dispute' => $dispute]))
            ->assertOk()
            ->assertSee('Issue Report #'.$dispute->id)
            ->assertSee('Pandit not joined')
            ->assertSee('user-proof.pdf');

        $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.reports.evidence', ['dispute' => $dispute, 'evidence' => $userEvidence]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.reports.respond', ['dispute' => $dispute]), [
                'response' => 'I joined from the assigned link and waited for the user.',
                'proof' => UploadedFile::fake()->create('pandit-proof.jpg', 25, 'image/jpeg'),
            ])
            ->assertRedirect(route('pandit.reports.show', ['dispute' => $dispute]))
            ->assertSessionHas('success', 'Response submitted successfully.');

        $dispute->refresh();
        $panditEvidence = DisputeEvidence::query()
            ->where('uploaded_by_type', Pandit::class)
            ->where('uploaded_by_id', $pandit->id)
            ->firstOrFail();

        $this->assertSame(Dispute::STATUS_UNDER_REVIEW, $dispute->status);
        $this->assertSame('I joined from the assigned link and waited for the user.', $dispute->pandit_response);
        $this->assertNotNull($dispute->pandit_responded_at);
        $this->assertStringStartsWith('dispute-evidences/'.$dispute->id.'/pandit/', $panditEvidence->file_path);
        Storage::disk('local')->assertExists($panditEvidence->file_path);
    }

    public function test_only_assigned_pandit_can_view_respond_and_view_evidence(): void
    {
        Storage::fake('local');
        [$user, $pandit, $session, $dispute] = $this->reportedSession();
        $wrongPandit = Pandit::create([
            'full_name' => 'Wrong Pandit',
            'pandit_name' => 'Wrong Pandit',
            'email' => 'wrong-pandit-report@example.test',
            'status' => 'verified',
        ]);

        Storage::disk('local')->put('dispute-evidences/'.$dispute->id.'/user-proof.pdf', 'user proof');
        $evidence = $dispute->evidences()->create([
            'uploaded_by_type' => User::class,
            'uploaded_by_id' => $user->id,
            'file_path' => 'dispute-evidences/'.$dispute->id.'/user-proof.pdf',
            'original_name' => 'user-proof.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 10,
        ]);

        $this->actingAs($wrongPandit, 'pandit')
            ->get(route('pandit.reports.show', ['dispute' => $dispute]))
            ->assertForbidden();

        $this->actingAs($wrongPandit, 'pandit')
            ->post(route('pandit.reports.respond', ['dispute' => $dispute]), [
                'response' => 'Trying to respond.',
            ])
            ->assertForbidden();

        $this->actingAs($wrongPandit, 'pandit')
            ->get(route('pandit.reports.evidence', ['dispute' => $dispute, 'evidence' => $evidence]))
            ->assertForbidden();

        $this->assertNull($dispute->fresh()->pandit_response);
    }

    public function test_duplicate_pandit_response_is_blocked(): void
    {
        [$user, $pandit, $session, $dispute] = $this->reportedSession([
            'pandit_response' => 'Original response.',
            'pandit_responded_at' => now(),
            'status' => Dispute::STATUS_UNDER_REVIEW,
        ]);

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.reports.respond', ['dispute' => $dispute]), [
                'response' => 'Second response.',
            ])
            ->assertRedirect()
            ->assertSessionHas('info', 'Response already submitted.');

        $this->assertSame('Original response.', $dispute->fresh()->pandit_response);
    }

    public function test_invalid_pandit_proof_is_rejected(): void
    {
        Storage::fake('local');
        [$user, $pandit, $session, $dispute] = $this->reportedSession();

        $this->actingAs($pandit, 'pandit')
            ->from(route('pandit.reports.show', ['dispute' => $dispute]))
            ->post(route('pandit.reports.respond', ['dispute' => $dispute]), [
                'response' => 'Attached invalid file.',
                'proof' => UploadedFile::fake()->create('proof.exe', 1, 'application/x-msdownload'),
            ])
            ->assertRedirect(route('pandit.reports.show', ['dispute' => $dispute]))
            ->assertSessionHasErrors('proof');

        $this->assertNull($dispute->fresh()->pandit_response);
        $this->assertDatabaseCount('dispute_evidences', 0);
    }

    private function reportedSession(array $disputeOverrides = []): array
    {
        $user = User::factory()->create([
            'name' => 'Aarav Sharma',
            'email' => 'aarav-pandit-report@example.test',
        ]);
        $pandit = Pandit::create([
            'full_name' => 'Pandit Assigned',
            'pandit_name' => 'Pandit Assigned',
            'email' => 'assigned-pandit-report@example.test',
            'status' => 'verified',
        ]);
        $session = HawanSession::create([
            'user_id' => $user->id,
            'service_type' => 'hawan',
            'pandit_id' => $pandit->id,
            'booking_date' => now()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['hawan_name' => 'Mahamrityunjaya Hawan']),
        ]);

        $dispute = $session->disputes()->create(array_merge([
            'user_id' => $user->id,
            'pandit_id' => $pandit->id,
            'reason' => 'pandit_not_joined',
            'description' => 'Pandit did not join at the selected time.',
            'status' => Dispute::STATUS_OPEN,
            'opened_at' => now(),
        ], $disputeOverrides));

        return [$user, $pandit, $session, $dispute];
    }
}
