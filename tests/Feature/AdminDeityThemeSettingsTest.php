<?php

namespace Tests\Feature;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use App\Models\Admin\Audio;
use App\Models\Admin\Deity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDeityThemeSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_deity_temple_theme_settings(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $audio = Audio::create([
            'title' => 'Temple Bells',
            'slug' => 'temple-bells',
            'category' => 'temple_ambience',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.deities.create'))
            ->assertOk()
            ->assertSee('Background Image')
            ->assertSee('Primary Color')
            ->assertSee('Mantra/Music / Ambient Sound')
            ->assertSee('Temple Bells')
            ->assertSee('Golden Sparkles')
            ->assertSee('Intense');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.deities.store'), [
                'name' => 'Maa Durga',
                'slug' => 'maa-durga',
                'short_description' => 'Durga temple theme',
                'description' => 'Theme settings for Durga.',
                'featured_image' => $this->fakePng('durga.png'),
                'temple_background_image' => $this->fakePng('durga-temple.png'),
                'primary_color' => '#b42318',
                'secondary_color' => '#f6c453',
                'glow_color' => '#ffd166',
                'ambient_audio_id' => $audio->id,
                'particle_style' => 'golden_sparkles',
                'flame_style' => 'intense',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.deities.index'));

        $deity = Deity::where('slug', 'maa-durga')->firstOrFail();

        $this->assertSame('#b42318', $deity->primary_color);
        $this->assertSame('#f6c453', $deity->secondary_color);
        $this->assertSame('#ffd166', $deity->glow_color);
        $this->assertSame($audio->id, $deity->ambient_audio_id);
        $this->assertSame('golden_sparkles', $deity->particle_style);
        $this->assertSame('intense', $deity->flame_style);
        Storage::disk('public')->assertExists($deity->featured_image);
        Storage::disk('public')->assertExists($deity->temple_background_image);

        $oldBackground = $deity->temple_background_image;

        $this->actingAs($admin, 'admin')
            ->put(route('admin.deities.update', $deity), [
                'name' => 'Maa Durga',
                'slug' => 'maa-durga',
                'short_description' => 'Durga temple theme',
                'description' => 'Theme settings for Durga.',
                'primary_color' => '#7c2d12',
                'secondary_color' => '#f6c453',
                'glow_color' => '#fff3bf',
                'ambient_audio_id' => $audio->id,
                'particle_style' => 'flower_petals',
                'flame_style' => 'soft',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.deities.index'));

        $deity->refresh();

        $this->assertSame($oldBackground, $deity->temple_background_image);
        $this->assertSame('#7c2d12', $deity->primary_color);
        $this->assertSame('#fff3bf', $deity->glow_color);
        $this->assertSame('flower_petals', $deity->particle_style);
        $this->assertSame('soft', $deity->flame_style);
    }

    public function test_deity_without_theme_data_still_works(): void
    {
        $admin = $this->admin();

        $deity = Deity::create([
            'name' => 'Maa Lakshmi',
            'slug' => 'maa-lakshmi',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.deities.edit', $deity))
            ->assertOk()
            ->assertSee('Maa Lakshmi');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.deities.update', $deity), [
                'name' => 'Maa Lakshmi',
                'slug' => 'maa-lakshmi',
                'short_description' => null,
                'description' => null,
                'primary_color' => null,
                'secondary_color' => null,
                'glow_color' => null,
                'ambient_audio_id' => null,
                'particle_style' => null,
                'flame_style' => null,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.deities.index'));

        $this->assertDatabaseHas('deities', [
            'id' => $deity->id,
            'temple_background_image' => null,
            'primary_color' => null,
            'ambient_audio_id' => null,
        ]);
    }

    private function admin(): Admin
    {
        $role = AdminRole::firstOrCreate([
            'slug' => 'super-admin',
        ], [
            'name' => 'Super Admin',
            'status' => 'active',
        ]);

        return Admin::create([
            'name' => 'Admin',
            'email' => uniqid('admin').'@example.test',
            'password' => 'password',
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }

    private function fakePng(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'deity-theme');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
