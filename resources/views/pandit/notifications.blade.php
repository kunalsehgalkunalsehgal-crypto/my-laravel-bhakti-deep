@extends('layouts.pandit-dashboard')

@section('title', 'Notifications - BhaktiDeep')
@php $activeMenu = 'notifications'; @endphp

@section('content')

<div class="pandit-page-heading">
    <div><h1>Notifications</h1></div>
</div>

<section class="pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-bell"></i></span>
        <div><h2>All Notifications</h2></div>
    </div>

    @forelse($notifications as $notif)
        <div style="border:1px solid rgba(199,141,34,0.2);border-radius:14px;background:rgba(251,244,223,0.5);padding:16px;margin-bottom:12px">
            <strong style="display:block;color:var(--cream);font-size:15px;margin-bottom:6px">
                <i class="bi bi-megaphone" style="color:var(--gold)"></i>
                {{ $notif->title }}
            </strong>
            <p style="margin:0 0 8px;color:rgba(55,32,22,0.8);font-size:14px">{{ $notif->message }}</p>
            <small style="color:var(--muted);font-size:12px">{{ $notif->created_at->diffForHumans() }}</small>
        </div>
    @empty
        <div style="text-align:center;padding:40px;color:var(--muted)">
            <i class="bi bi-bell-slash" style="font-size:40px;display:block;margin-bottom:12px"></i>
            <p>No notifications yet.</p>
        </div>
    @endforelse
</section>

@endsection
