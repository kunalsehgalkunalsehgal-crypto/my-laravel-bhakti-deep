<?php

namespace App\Http\Controllers;

use App\Models\Pandit\Pandit;
use App\Models\PanditZoomConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PanditZoomController extends Controller
{
    // Pandit ko Zoom par bhejega
    public function connect()
    {
       
    $pandit = Auth::guard('pandit')->user();

    abort_unless($pandit, 403, 'Pandit not logged in.');

    $state = Str::random(40);

    session([
        'zoom_oauth_state' => $state,
        'zoom_pandit_id' => $pandit->id,
    ]);

    $query = [
        'response_type' => 'code',
        'client_id' => config('services.zoom.client_id'),
        'redirect_uri' => config('services.zoom.redirect_uri'),
        'state' => $state,
    ];

    $scopes = trim((string) config('services.zoom.scopes'));

    if ($scopes !== '') {
        $query['scope'] = $scopes;
    }

    return redirect()->away(
        'https://zoom.us/oauth/authorize?' . http_build_query($query)
    );
    }


    // Zoom Allow ke baad yahan wapas bhejega
    public function callback(Request $request)
    {
        if (!$request->filled('code')) {
            return redirect()
                ->back()
                ->with('error', 'Zoom connection failed.');
        }

        if (
            !$request->filled('state') ||
            $request->state !== session('zoom_oauth_state')
        ) {
            abort(403, 'Invalid Zoom OAuth state.');
        }

        $panditId = session('zoom_pandit_id');

        if (!$panditId) {
            abort(403, 'Pandit session not found.');
        }

        // Authorization code ko tokens me convert karo
        $tokenResponse = Http::asForm()
            ->withBasicAuth(
                config('services.zoom.client_id'),
                config('services.zoom.client_secret')
            )
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'redirect_uri' => config('services.zoom.redirect_uri'),
            ]);

        if ($tokenResponse->failed()) {
            return response()->json([
                'message' => 'Zoom token error',
                'zoom_error' => $tokenResponse->json(),
            ], 400);
        }

        $tokens = $tokenResponse->json();

        // Connected Zoom user ki details nikalo
        $zoomUserResponse = Http::withToken($tokens['access_token'])
            ->get('https://api.zoom.us/v2/users/me');

        if ($zoomUserResponse->failed()) {
            return response()->json([
                'message' => 'Unable to get Zoom user.',
                'zoom_error' => $zoomUserResponse->json(),
            ], 400);
        }

        $zoomUser = $zoomUserResponse->json();

        // DB me save/update
        PanditZoomConnection::updateOrCreate(
            [
                'pandit_id' => $panditId,
            ],
            [
                'zoom_user_id' => $zoomUser['id'] ?? null,
                'zoom_email' => $zoomUser['email'] ?? null,

                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],

                'token_expires_at' => now()->addSeconds(
                    $tokens['expires_in'] ?? 3600
                ),

                'connected_at' => now(),
            ]
        );

        session()->forget([
            'zoom_oauth_state',
            'zoom_pandit_id',
        ]);

        return redirect()
            ->route('pandit.dashboard')
            ->with('success', 'Zoom account connected successfully.');
    }
}
