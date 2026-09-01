<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\Admin\Admin;


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('live-session.{type}.{id}', function ($user, string $type, string $id) {
    $booking = match ($type) {
        'hawan' => HawanSession::find($id),
        'pooja' => PoojaSession::find($id),
        default => null,
    };

    return $booking && (int) $booking->user_id === (int) $user->id;
}, ['guards' => ['web']]);

// Admin <-> Pandit private chat
Broadcast::channel('pandit.chat.{panditId}', function ($user, $panditId) {

    // Koi bhi logged-in Admin
    if ($user instanceof Admin) {
        return true;
    }

    // Pandit sirf apni chat access kare
    if ($user instanceof Pandit) {
        return (int) $user->id === (int) $panditId;
    }

    return false;

}, [
    'guards' => ['admin', 'pandit']
]);
