<?php

namespace Tests\Feature;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Pusher\Pusher;
use Tests\TestCase;

class PusherProductionReadinessTest extends TestCase
{
    public function test_pusher_configuration_and_frontend_build_are_production_ready(): void
    {
        $env = $this->readEnvFile();

        $broadcastConnections = $this->allEnvValues('BROADCAST_CONNECTION');

        $this->assertNotEmpty(
            $broadcastConnections,
            'BROADCAST_CONNECTION .env me mila nahi.'
        );

        foreach ($broadcastConnections as $connection) {
            $this->assertSame(
                'pusher',
                strtolower($connection),
                'Har active BROADCAST_CONNECTION ko pusher hona chahiye. Duplicate reverb line abhi bhi .env me ho sakti hai.'
            );
        }

        foreach (['PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_CLUSTER'] as $key) {
            $this->assertNotEmpty(
                $env[$key] ?? '',
                $key.' .env me empty/missing hai.'
            );
        }

        $this->assertSame(
            'ap2',
            strtolower($env['PUSHER_APP_CLUSTER'] ?? ''),
            'PUSHER_APP_CLUSTER expected ap2 hai.'
        );

        $host = strtolower(trim($env['PUSHER_HOST'] ?? ''));
        $this->assertFalse(
            in_array($host, ['localhost', '127.0.0.1'], true),
            'PUSHER_HOST localhost/127.0.0.1 nahi hona chahiye. Pusher Cloud use karna hai.'
        );

        $this->assertSame(
            '443',
            (string) ($env['PUSHER_PORT'] ?? '443'),
            'PUSHER_PORT 443 hona chahiye.'
        );

        $this->assertSame(
            'https',
            strtolower((string) ($env['PUSHER_SCHEME'] ?? 'https')),
            'PUSHER_SCHEME https hona chahiye.'
        );

        $this->assertTrue(
            class_exists(Pusher::class),
            'pusher/pusher-php-server package available nahi hai.'
        );

        $connection = config('broadcasting.connections.pusher');
        $this->assertSame('pusher', $connection['driver'] ?? null, 'config/broadcasting.php me Pusher driver missing hai.');

        $echoPath = resource_path('js/echo.js');
        $this->assertFileExists($echoPath, 'resources/js/echo.js missing hai.');
        $echo = file_get_contents($echoPath);

        $this->assertMatchesRegularExpression(
            "/broadcaster\\s*:\\s*['\"]pusher['\"]/",
            $echo,
            'echo.js me active broadcaster pusher nahi mila.'
        );
        $this->assertStringContainsString('VITE_PUSHER_APP_KEY', $echo, 'echo.js me VITE_PUSHER_APP_KEY use nahi ho raha.');
        $this->assertStringContainsString('VITE_PUSHER_APP_CLUSTER', $echo, 'echo.js me VITE_PUSHER_APP_CLUSTER use nahi ho raha.');

        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists(
            $manifestPath,
            'public/build/manifest.json missing hai. Live upload se pehle npm run build chalao.'
        );

        $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        $asset = $manifest['resources/js/app.js']['file'] ?? null;

        $this->assertNotEmpty($asset, 'Vite manifest me resources/js/app.js build entry nahi mili.');

        $assetPath = public_path('build/'.$asset);
        $this->assertFileExists($assetPath, 'Built JS asset missing hai: '.$asset);

        $builtJs = file_get_contents($assetPath);

        $this->assertStringNotContainsString(
            'localhost:8080',
            $builtJs,
            'Production build me abhi localhost:8080/Reverb mila. npm run build dobara chalao.'
        );

        $this->assertStringContainsString(
            $env['PUSHER_APP_KEY'],
            $builtJs,
            'Production JS build me current Pusher app key nahi mila. npm run build dobara chalao.'
        );

        $this->assertStringContainsString(
            $env['PUSHER_APP_CLUSTER'],
            $builtJs,
            'Production JS build me current Pusher cluster nahi mila. npm run build dobara chalao.'
        );

        foreach ([
            \App\Events\PanditMessageSent::class,
            \App\Events\FamilyMemberStatusChanged::class,
            \App\Events\LiveSessionUpdated::class,
        ] as $eventClass) {
            $this->assertTrue(
                is_subclass_of($eventClass, ShouldBroadcastNow::class),
                $eventClass.' ko ShouldBroadcastNow implement karna chahiye.'
            );
        }
    }

    public function test_real_pusher_cloud_accepts_a_broadcast(): void
    {
        $env = $this->readEnvFile();

        foreach (['PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_CLUSTER'] as $key) {
            $this->assertNotEmpty($env[$key] ?? '', $key.' missing hai, isliye real Pusher test nahi chal sakta.');
        }

        $pusher = new Pusher(
            $env['PUSHER_APP_KEY'],
            $env['PUSHER_APP_SECRET'],
            $env['PUSHER_APP_ID'],
            [
                'cluster' => $env['PUSHER_APP_CLUSTER'],
                'useTLS' => true,
            ]
        );

        $result = $pusher->trigger(
            'bhaktideep-smoke-test',
            'bhaktideep.pusher.test',
            [
                'ok' => true,
                'source' => 'phpunit',
                'sent_at' => now()->toIso8601String(),
            ]
        );

        $this->assertTrue(
            (bool) $result,
            'Pusher Cloud ne test event accept nahi kiya. Credentials/network/config check karo.'
        );
    }

    private function readEnvFile(): array
    {
        $path = base_path('.env');
        $this->assertFileExists($path, '.env file missing hai.');

        $values = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $values[$key] = $value;
        }

        return $values;
    }

    private function allEnvValues(string $wantedKey): array
    {
        $path = base_path('.env');
        $values = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            if (trim($key) !== $wantedKey) {
                continue;
            }

            $values[] = trim(trim($value), "\"'");
        }

        return $values;
    }
}
