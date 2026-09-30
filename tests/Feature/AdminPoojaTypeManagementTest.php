<?php

namespace Tests\Feature;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use App\Models\Admin\Audio;
use App\Models\Admin\Pooja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPoojaTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_edit_live_and_digital_pooja_types(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $mantra = Audio::create([
            'title' => 'Digital Pooja Mantra',
            'slug' => 'digital-pooja-mantra',
            'category' => 'mantra',
            'audio_file' => 'audio/digital-pooja.mp3',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.poojas.create'))
            ->assertOk()
            ->assertSee('Enable Live Pooja')
            ->assertSee('Enable Digital Pooja')
            ->assertSee('Digital Pooja Video')
            ->assertSee('Digital Mantra Audio')
            ->assertSee('Digital Access Duration (minutes)')
            ->assertSee('value="120"', false)
            ->assertSee('Digital Pooja Mantra');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.poojas.store'), [
                'name' => 'Admin Test Pooja',
                'slug' => 'admin-test-pooja',
                'base_price' => 1001,
                'live_pooja_enabled' => '1',
                'live_pooja_title' => 'Live Test Pooja',
                'live_pooja_description' => 'Join this pooja live.',
                'live_pooja_price' => 1501,
                'digital_pooja_title' => 'Digital Test Pooja',
                'digital_pooja_description' => 'Receive the pooja video.',
                'digital_pooja_price' => 751,
                'digital_pooja_video' => UploadedFile::fake()->create('pooja.mp4', 100, 'video/mp4'),
                'digital_pooja_audio_id' => $mantra->id,
                'mode' => 'Live + Replay',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.poojas.index'));

        $pooja = Pooja::where('slug', 'admin-test-pooja')->firstOrFail();

        $this->assertTrue($pooja->live_pooja_enabled);
        $this->assertFalse($pooja->digital_pooja_enabled);
        $this->assertSame('1501.00', $pooja->live_pooja_price);
        $this->assertSame($mantra->id, $pooja->digital_pooja_audio_id);
        $this->assertSame(120, $pooja->digital_pooja_access_minutes);
        Storage::disk('public')->assertExists($pooja->digital_pooja_video);

        $oldVideo = $pooja->digital_pooja_video;

        $this->actingAs($admin, 'admin')
            ->put(route('admin.poojas.update', $pooja), [
                'name' => 'Admin Test Pooja',
                'slug' => 'admin-test-pooja',
                'base_price' => 1001,
                'live_pooja_title' => 'Live Test Pooja',
                'live_pooja_description' => 'Join this pooja live.',
                'live_pooja_price' => 1501,
                'digital_pooja_enabled' => '1',
                'digital_pooja_title' => 'Updated Digital Pooja',
                'digital_pooja_description' => 'Updated digital video.',
                'digital_pooja_price' => 801,
                'digital_pooja_video' => UploadedFile::fake()->create('updated-pooja.mp4', 100, 'video/mp4'),
                'digital_pooja_audio_id' => $mantra->id,
                'digital_pooja_access_minutes' => 60,
                'mode' => 'Live + Replay',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.poojas.index'));

        $pooja->refresh();

        $this->assertFalse($pooja->live_pooja_enabled);
        $this->assertTrue($pooja->digital_pooja_enabled);
        $this->assertSame('Updated Digital Pooja', $pooja->digital_pooja_title);
        $this->assertSame('801.00', $pooja->digital_pooja_price);
        $this->assertSame(60, $pooja->digital_pooja_access_minutes);
        $this->assertTrue($pooja->digitalPoojaAudio->is($mantra));
        $this->assertNotSame($oldVideo, $pooja->digital_pooja_video);
        Storage::disk('public')->assertExists($pooja->digital_pooja_video);
    }

    private function admin(): Admin
    {
        $role = AdminRole::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'status' => 'active',
        ]);

        return Admin::create([
            'name' => 'Admin',
            'email' => 'pooja-admin@example.test',
            'password' => 'password',
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }
}
