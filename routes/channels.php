<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\Admin\Admin;
use App\Models\User;


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('live-session.{type}.{id}', function ($user, string $type, string $id) {
    $booking = match ($type) {
        'hawan' => HawanSession::find($id),
        'pooja' => PoojaSession::find($id),
        default => null,
    };

    return $booking && match (true) {
        $user instanceof User => (int) $booking->user_id === (int) $user->id,
        $user instanceof Pandit => (int) $booking->pandit_id === (int) $user->id,
        default => false,
    };
}, ['guards' => ['web', 'pandit']]);

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
