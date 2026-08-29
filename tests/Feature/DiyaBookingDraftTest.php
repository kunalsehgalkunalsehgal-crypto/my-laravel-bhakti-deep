<?php

namespace Tests\Feature;

use App\Models\Admin\Deity;
use App\Models\Admin\Diya;
use App\Models\Admin\DiyaSession;
use App\Models\Admin\Donation;
use App\Models\Admin\PaymentLog;
use App\Models\Admin\SankalpForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiyaBookingDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_save_only_diya_form_fields_as_session_draft_without_creating_booking(): void
    {
        [$diya, $deity] = $this->diyaFixtures();

        $response = $this->postJson(route('diya.draft'), [
            'diya_id' => $diya->id,
            'deity_id' => $deity->id,
            'full_name' => 'Aarav Sharma',
            'mobile' => '9876543210',
            'gotra' => 'Kashyap',
            'dob' => '1992-04-15',
            'birth_time' => '06:30',
            'birth_place' => 'Varanasi',
            'father_name' => 'Ramesh Sharma',
            'mother_name' => 'Sita Sharma',
            'spouse_name' => 'Priya Sharma',
            'family_names' => 'Anaya, Kabir',
            'purpose' => 'Family Peace',
            'mannokamna' => 'Peace and good health',
            'amount' => '999999',
            'seva_amount' => '999999',
            'total_amount' => '999999',
            'payment_status' => 'paid',
            'consent' => '1',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'redirect_url' => route('diya.continue'),
            ])
            ->assertSessionHas('diya_booking_draft', [
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'full_name' => 'Aarav Sharma',
                'mobile' => '9876543210',
                'gotra' => 'Kashyap',
                'dob' => '1992-04-15',
                'birth_time' => '06:30',
                'birth_place' => 'Varanasi',
                'father_name' => 'Ramesh Sharma',
                'mother_name' => 'Sita Sharma',
                'spouse_name' => 'Priya Sharma',
                'family_names' => 'Anaya, Kabir',
                'purpose' => 'Family Peace',
                'mannokamna' => 'Peace and good health',
            ]);

        $draft = session('diya_booking_draft');

        $this->assertArrayNotHasKey('amount', $draft);
        $this->assertArrayNotHasKey('seva_amount', $draft);
        $this->assertArrayNotHasKey('total_amount', $draft);
        $this->assertArrayNotHasKey('payment_status', $draft);
        $this->assertArrayNotHasKey('consent', $draft);
        $this->assertDatabaseCount('diya_sessions', 0);
        $this->assertDatabaseCount('sankalp_forms', 0);
        $this->assertDatabaseCount('donations', 0);
        $this->assertDatabaseCount('payment_logs', 0);
    }

    public function test_saved_diya_draft_is_available_to_restore_on_form_page(): void
    {
        [$diya, $deity] = $this->diyaFixtures();

        $this->withSession([
            'diya_booking_draft' => [
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'full_name' => 'Aarav Sharma',
                'mobile' => '9876543210',
                'purpose' => 'Protection',
            ],
        ])->get(route('light-diya'))
            ->assertOk()
            ->assertSee('"full_name":"Aarav Sharma"', false)
            ->assertSee('"purpose":"Protection"', false);
    }

    public function test_successful_authenticated_diya_booking_clears_session_draft(): void
    {
        [$diya, $deity] = $this->diyaFixtures();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession([
                'diya_booking_draft' => [
                    'diya_id' => $diya->id,
                    'deity_id' => $deity->id,
                    'full_name' => 'Aarav Sharma',
                    'mobile' => '9876543210',
                    'purpose' => 'Family Peace',
                ],
            ])
            ->postJson(route('diya.store'), [
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'full_name' => 'Aarav Sharma',
                'mobile' => '9876543210',
                'gotra' => 'Kashyap',
                'dob' => '1992-04-15',
                'birth_time' => '06:30',
                'birth_place' => 'Varanasi',
                'father_name' => 'Ramesh Sharma',
                'mother_name' => 'Sita Sharma',
                'spouse_name' => 'Priya Sharma',
                'family_names' => 'Anaya, Kabir',
                'purpose' => 'Family Peace',
                'mannokamna' => 'Peace and good health',
                'consent' => '1',
                'amount' => '999999',
            ])
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertSessionMissing('diya_booking_draft');

        $this->assertDatabaseCount('diya_sessions', 1);
        $this->assertDatabaseCount('sankalp_forms', 1);
        $this->assertDatabaseCount('donations', 1);
        $this->assertDatabaseCount('payment_logs', 1);
        $this->assertSame($user->id, DiyaSession::first()->user_id);
        $this->assertSame($user->id, SankalpForm::first()->user_id);
        $this->assertSame(108.0, (float) Donation::first()->amount);
        $this->assertSame(108.0, (float) PaymentLog::first()->amount);
    }

    private function diyaFixtures(): array
    {
        $deity = Deity::create([
            'name' => 'Maa Lakshmi',
            'slug' => 'maa-lakshmi',
            'status' => 'active',
        ]);

        $diya = Diya::create([
            'name' => 'Akhand Diya',
            'slug' => 'akhand-diya',
            'short_description' => 'Sacred diya offering',
            'seva_amount' => 108,
            'duration' => '1 day',
            'deity_selection_mode' => Diya::MODE_USER_SELECT,
            'status' => 'active',
        ]);

        return [$diya, $deity];
    }
}
