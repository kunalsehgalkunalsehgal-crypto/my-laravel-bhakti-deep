<?php

namespace Tests\Feature;

use App\Models\Admin\Audio;
use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PoojaTypeFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pooja_type_columns_and_model_helpers_are_available(): void
    {
        $this->assertTrue(Schema::hasColumns('poojas', [
            'live_pooja_enabled',
            'live_pooja_title',
            'live_pooja_description',
            'live_pooja_price',
            'digital_pooja_enabled',
            'digital_pooja_title',
            'digital_pooja_description',
            'digital_pooja_price',
            'digital_pooja_video',
            'digital_pooja_audio_id',
            'digital_pooja_access_minutes',
        ]));

        $this->assertTrue(Schema::hasColumns('pooja_sessions', [
            'pooja_type',
            'pooja_type_title',
            'pooja_type_price',
            'digital_video_path',
            'digital_audio_id',
            'digital_audio_title',
            'digital_audio_path',
            'digital_access_minutes',
            'start_at',
            'expires_at',
        ]));

        $mantra = Audio::create([
            'title' => 'Foundation Mantra',
            'slug' => 'foundation-mantra',
            'category' => 'mantra',
            'audio_file' => 'audio/foundation.mp3',
            'status' => 'active',
        ]);

        $pooja = Pooja::create([
            'name' => 'Test Pooja',
            'slug' => 'test-pooja',
            'base_price' => 1001,
            'live_pooja_enabled' => true,
            'live_pooja_title' => 'Live Test Pooja',
            'live_pooja_description' => 'Join live.',
            'live_pooja_price' => 1501,
            'digital_pooja_enabled' => true,
            'digital_pooja_title' => 'Digital Test Pooja',
            'digital_pooja_description' => 'Receive the video.',
            'digital_pooja_price' => 751,
            'digital_pooja_video' => 'poojas/test.mp4',
            'digital_pooja_audio_id' => $mantra->id,
        ]);

        $this->assertTrue($pooja->live_pooja_enabled);
        $this->assertSame('1501.00', $pooja->live_pooja_price);
        $this->assertSame(['live', 'digital'], array_column($pooja->enabledPoojaTypes(), 'key'));
        $this->assertSame('poojas/test.mp4', $pooja->enabledPoojaType('digital')['video']);
        $this->assertTrue($pooja->digitalPoojaAudio->is($mantra));
        $this->assertSame(120, $pooja->digital_pooja_access_minutes);
        $this->assertSame(751, $pooja->toBookingArray()['display_price']);
        $this->assertSame($pooja->enabledPoojaTypes(), $pooja->toCardArray()['types']);
        $this->assertSame(1, Pooja::withEnabledPoojaTypes()->count());
    }

    public function test_pooja_session_type_snapshot_is_fillable_and_cast(): void
    {
        $mantra = Audio::create([
            'title' => 'Session Mantra',
            'slug' => 'session-mantra',
            'category' => 'mantra',
            'audio_file' => 'audio/session-mantra.mp3',
            'status' => 'active',
        ]);

        $session = PoojaSession::create([
            'pooja_type' => 'digital',
            'pooja_type_title' => 'Digital Pooja',
            'pooja_type_price' => 751,
            'digital_video_path' => 'pooja-sessions/video.mp4',
            'digital_audio_id' => $mantra->id,
            'digital_audio_title' => 'Session Mantra',
            'digital_audio_path' => 'audio/session-mantra.mp3',
            'digital_access_minutes' => 60,
            'expires_at' => now()->addHour(),
        ]);

        $this->assertSame('digital', $session->pooja_type);
        $this->assertSame('Digital Pooja', $session->pooja_type_title);
        $this->assertSame('751.00', $session->pooja_type_price);
        $this->assertSame('pooja-sessions/video.mp4', $session->digital_video_path);
        $this->assertSame($mantra->id, $session->digital_audio_id);
        $this->assertSame('Session Mantra', $session->digital_audio_title);
        $this->assertSame('audio/session-mantra.mp3', $session->digital_audio_path);
        $this->assertSame(60, $session->digital_access_minutes);
        $this->assertNotNull($session->expires_at);
    }
}
