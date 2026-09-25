<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pandit Dashboard - BhaktiDeep')</title>
    
<link rel="icon" type="image/x-icon" href="https://i.pinimg.com/736x/c3/30/ae/c330aeba4ebb8971936067cd0b077c70.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pandit.css') }}" rel="stylesheet">

@stack('styles')
    @vite(['resources/js/app.js'])
</head>
<body class="pandit-dashboard-body">
@php
    $activeMenu = $activeMenu ?? 'dashboard';
    $sidebarItems = [
        ['Dashboard', 'bi-grid-1x2-fill', route('pandit.dashboard'), $activeMenu === 'dashboard'],
        ['My Profile', 'bi-person-badge', route('pandit.profile'), $activeMenu === 'profile'],
        ['Qualification', 'bi-mortarboard', route('pandit.qualification'), $activeMenu === 'qualification'],
        ['My Services', 'bi-stars', route('pandit.services'), $activeMenu === 'services'],
        ['Bookings', 'bi-calendar2-check', route('pandit.bookings.index'), $activeMenu === 'bookings'],
        ['Reports', 'bi-exclamation-triangle', route('pandit.reports.index'), $activeMenu === 'reports'],
        ['Live Sessions', 'bi-camera-video', route('pandit.live-sessions.index'), $activeMenu === 'live-sessions'],
        ['Availability', 'bi-clock-history', route('pandit.availability'), $activeMenu === 'availability'],
        ['Earnings', 'bi-currency-rupee', '#', $activeMenu === 'earnings'],
        ['Dakshina', 'bi-gift', '#', $activeMenu === 'dakshina'],
        ['Reviews', 'bi-chat-heart', '#', $activeMenu === 'reviews'],
        ['Documents', 'bi-file-earmark-lock', route('pandit.documents'), $activeMenu === 'documents'],
        ['Bank Details', 'bi-bank', route('pandit.bank-details'), $activeMenu === 'bank-details'],
        ['Notifications', 'bi-bell', route('pandit.notifications'), $activeMenu === 'notifications'],
        ['Messages', 'bi-chat-dots', route('pandit.messages'), $activeMenu === 'messages'],
        ['Logout', 'bi-box-arrow-right', route('pandit.logout'), $activeMenu === 'logout'],
        ['Connect Zoom', 'bi-camera-video-fill', route('pandit.zoom.connect'), $activeMenu === 'zoom'],
    ];
    $unreadCount = isset($pandit) ? \App\Models\Pandit\PanditNotification::where('pandit_id', $pandit->id)->where('is_read', false)->count() : 0;
    $unreadMessages = isset($pandit) ? \App\Models\Pandit\PanditMessage::where('pandit_id', $pandit->id)->where('sender', 'admin')->where('is_read', false)->count() : 0;
    $openReportCount = isset($pandit)
        ? \App\Models\Dispute::query()
            ->whereIn('status', [\App\Models\Dispute::STATUS_OPEN, \App\Models\Dispute::STATUS_UNDER_REVIEW])
            ->whereHasMorph('disputable', [\App\Models\Admin\HawanSession::class, \App\Models\Admin\PoojaSession::class], function ($query) use ($pandit) {
                $query->where('pandit_id', $pandit->id);
            })
            ->count()
        : 0;
@endphp

<input type="checkbox" id="panditMenuToggle" class="pandit-menu-toggle">
<div class="pandit-dashboard-shell">
    <aside class="pandit-sidebar">
        <a class="pandit-dashboard-brand" href="{{ route('home') }}">
            <span class="brand-icon"><i class="bi bi-fire"></i></span>
            <span>
                <span class="brand-title gold-text">BhaktiDeep</span>
                <span class="brand-tagline">Pandit Portal</span>
            </span>
        </a>
        <nav>
            @foreach($sidebarItems as [$label, $icon, $url, $active])
                @if($label === 'Logout')
                    <form method="POST" action="{{ $url }}" class="pandit-nav-form">
                        @csrf
                        <button type="submit">
                            <i class="bi {{ $icon }}"></i>
                            <span>{{ $label }}</span>
                        </button>
                    </form>
                @else
                    <a href="{{ $url }}" class="{{ $active ? 'active' : '' }}">
                        <i class="bi {{ $icon }}"></i>
                        <span>{{ $label }}</span>
                        @if($label === 'Notifications' && $unreadCount > 0)
                            <span style="margin-left:auto;background:#e85d04;color:#fff;border-radius:999px;font-size:11px;font-weight:900;padding:2px 8px">{{ $unreadCount }}</span>
                        @endif
                        @if($label === 'Reports' && $openReportCount > 0)
                            <span style="margin-left:auto;background:#e85d04;color:#fff;border-radius:999px;font-size:11px;font-weight:900;padding:2px 8px">{{ $openReportCount }}</span>
                        @endif
                        @if($label === 'Messages' && $unreadMessages > 0)
                            <span style="margin-left:auto;background:#e85d04;color:#fff;border-radius:999px;font-size:11px;font-weight:900;padding:2px 8px">{{ $unreadMessages }}</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>
    </aside>

    <div class="pandit-dashboard-main">
        <header class="pandit-topbar">
            <label for="panditMenuToggle" class="pandit-menu-button"><i class="bi bi-list"></i></label>
            <a class="pandit-mobile-brand" href="{{ route('home') }}">
                <span class="brand-icon"><i class="bi bi-fire"></i></span>
                <span class="brand-title gold-text">BhaktiDeep</span>
            </a>
            <div class="pandit-topbar-profile">
                <a class="pandit-icon-button" href="{{ route('pandit.notifications') }}" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    @if($unreadCount > 0)
                        <span>{{ $unreadCount }}</span>
                    @endif
                </a>
                <form method="POST" action="{{ route('pandit.logout') }}" class="pandit-topbar-logout">
                    @csrf
                    <button class="pandit-icon-button" type="submit" aria-label="Logout"><i class="bi bi-box-arrow-right"></i></button>
                </form>
                <img src="{{ $pandit->profile_photo ? asset('storage/'.$pandit->profile_photo) : asset('assets/small-deep.jpg') }}" alt="{{ $pandit->pandit_name ?: $pandit->full_name}}">
                <div>
                    <strong>{{ $pandit->pandit_name ?: $pandit->full_name}}</strong>
                    <span><p>{{ $pandit->status }}</p></span>
                </div>
            </div>
        </header>

        <main class="pandit-dashboard-content">
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
