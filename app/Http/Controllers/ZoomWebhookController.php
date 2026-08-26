<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZoomWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Zoom endpoint validation
        if ($request->input('event') === 'endpoint.url_validation') {

            $plainToken = $request->input('payload.plainToken');

            $encryptedToken = hash_hmac(
                'sha256',
                $plainToken,
                config('services.zoom.webhook_secret_token')
            );

            return response()->json([
                'plainToken' => $plainToken,
                'encryptedToken' => $encryptedToken,
            ]);
        }

        // Abhi testing ke liye saare Zoom events log kar do
        Log::info('Zoom Webhook Event', $request->all());

        return response()->json([
            'status' => 'success'
        ]);
    }
}