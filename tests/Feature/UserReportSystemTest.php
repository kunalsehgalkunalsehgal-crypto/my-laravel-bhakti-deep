<?php

namespace Tests\Feature;

use App\Models\Admin\HawanSession;
use App\Models\Admin\NotificationLog;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserReportSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_owner_can_report_issue_with_optional_proof(): void
    {
        Storage::fake('local');
        [$user, $session] = $this->liveSession();

        $this->actingAs($user)
            ->post(route('live.issue-report.store', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'technical_issue',
                'description' => 'Audio was not clear during the session.',
                'proof' => UploadedFile::fake()->create('audio-proof.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Issue Reported - Status: Open');

        $dispute = Dispute::firstOrFail();
        $evidence = DisputeEvidence::firstOrFail();
        $notification = PanditNotification::firstOrFail();

        $this->assertDatabaseHas('disputes', [
            'id' => $dispute->id,
            'disputable_type' => HawanSession::class,
            'disputable_id' => $session->id,
            'user_id' => $user->id,
            'pandit_id' => $session->pandit_id,
            'reason' => 'technical_issue',
            'description' => 'Audio was not clear during the session.',
            'status' => Dispute::STATUS_OPEN,
        ]);

        $this->assertSame(User::class, $evidence->uploaded_by_type);
        $this->assertSame($user->id, $evidence->uploaded_by_id);
        $this->assertSame('audio-proof.jpg', $evidence->original_name);
        $this->assertStringStartsWith('dispute-evidences/'.$dispute->id.'/', $evidence->file_path);
        Storage::disk('local')->assertExists($evidence->file_path);

        $this->assertSame($session->pandit_id, $notification->pandit_id);
        $this->assertSame('New issue reported', $notification->title);
        $this->assertFalse((bool) $notification->is_read);
        $this->assertStringContainsString('Report #'.$dispute->id, $notification->message);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'channel' => 'my_bookings',
            'message_type' => 'report_submitted_'.$dispute->id,
            'message' => 'Your issue for Booking #'.$session->id.' has been submitted. Report #'.$dispute->id.' is now under review.',
            'delivery_status' => 'sent',
        ]);

        $this->actingAs($session->pandit, 'pandit')
            ->get(route('pandit.notifications'))
            ->assertOk()
            ->assertSee('New issue reported')
            ->assertSee('Report #'.$dispute->id);
    }

    public function test_active_duplicate_report_is_blocked(): void
    {
        [$user, $session] = $this->liveSession();

        $session->disputes()->create([
            'user_id' => $user->id,
            'pandit_id' => $session->pandit_id,
            'reason' => 'pandit_not_joined',
            'description' => 'First report.',
            'status' => Dispute::STATUS_UNDER_REVIEW,
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('live.issue-report.store', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'technical_issue',
                'description' => 'Second report.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Issue Reported - Status: Open');

        $this->assertDatabaseCount('disputes', 1);
        $this->assertSame(0, NotificationLog::where('channel', 'my_bookings')->count());
    }

    public function test_invalid_proof_files_are_rejected(): void
    {
        Storage::fake('local');
        [$user, $session] = $this->liveSession();

        $this->actingAs($user)
            ->from(route('live.session', ['type' => 'hawan', 'id' => $session->id]))
            ->post(route('live.issue-report.store', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'technical_issue',
                'description' => 'Suspicious file upload attempt.',
                'proof' => UploadedFile::fake()->create('proof.php', 1, 'application/x-php'),
            ])
            ->assertRedirect(route('live.session', ['type' => 'hawan', 'id' => $session->id]))
            ->assertSessionHasErrors('proof');

        $this->assertDatabaseCount('disputes', 0);
        Storage::disk('local')->assertMissing('proof.php');
    }

    public function test_only_report_owner_can_view_report_and_evidence(): void
    {
        Storage::fake('local');
        [$user, $session] = $this->liveSession();
        $wrongUser = User::factory()->create();

        $dispute = $session->disputes()->create([
            'user_id' => $user->id,
            'pandit_id' => $session->pandit_id,
            'reason' => 'wrong_service',
            'description' => 'The booked service was not performed.',
            'status' => Dispute::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        Storage::disk('local')->put('dispute-evidences/'.$dispute->id.'/proof.pdf', 'test-pdf');

        $evidence = $dispute->evidences()->create([
            'uploaded_by_type' => User::class,
            'uploaded_by_id' => $user->id,
            'file_path' => 'dispute-evidences/'.$dispute->id.'/proof.pdf',
            'original_name' => 'proof.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 8,
        ]);

        $this->actingAs($wrongUser)
            ->get(route('user.reports.show', ['dispute' => $dispute]))
            ->assertForbidden();

        $this->actingAs($wrongUser)
            ->get(route('user.reports.evidence', ['dispute' => $dispute, 'evidence' => $evidence]))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('user.reports.show', ['dispute' => $dispute]))
            ->assertOk()
            ->assertSee('Issue Report #'.$dispute->id)
            ->assertSee('View');

        $this->actingAs($user)
            ->get(route('user.reports.evidence', ['dispute' => $dispute, 'evidence' => $evidence]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_live_session_shows_view_report_for_existing_active_report(): void
    {
        [$user, $session] = $this->liveSession();

        $session->disputes()->create([
            'user_id' => $user->id,
            'pandit_id' => $session->pandit_id,
            'reason' => 'other',
            'description' => 'Existing report.',
            'status' => Dispute::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('live.session', ['type' => 'hawan', 'id' => $session->id]))
            ->assertOk()
            ->assertSee('Issue Reported - Status: Open')
            ->assertSee('View Report');
    }

    private function liveSession(): array
    {
        $user = User::factory()->create([
            'name' => 'Aarav Sharma',
            'email' => 'aarav-report@example.test',
        ]);
        $pandit = Pandit::create([
            'full_name' => 'Pandit Report',
            'pandit_name' => 'Pandit Report',
            'email' => 'pandit-report@example.test',
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
        $session->videoMeeting()->create([
            'provider' => 'fake',
            'external_meeting_id' => 'meeting-report',
            'join_url' => 'https://provider.example/join/meeting-report',
            'host_url' => 'https://provider.example/start/meeting-report',
            'passcode' => '123456',
            'status' => 'scheduled',
            'starts_at' => now(),
            'duration_minutes' => 60,
            'pandit_id' => $pandit->id,
        ]);

        return [$user, $session->fresh(['videoMeeting', 'pandit'])];
    }
}
