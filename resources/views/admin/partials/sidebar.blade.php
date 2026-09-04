@php
    $admin = auth('admin')->user();
    $totalUnread = \App\Models\Pandit\PanditMessage::where('sender', 'pandit')->where('is_read', false)->count();
    $items = [
        ['Dashboard', 'admin.dashboard', null],
        ['Pandits', 'admin.pandits.index', null],
        ['Pandit Messages', 'admin.pandit-messages.index', null],
        ['Users', 'admin.users.index', 'view-users'],
        ['Services', 'admin.services.index', 'manage-services'],
        ['Poojas', 'admin.poojas.index', 'manage-services'],
        ['Hawans', 'admin.hawans.index', 'manage-services'],
        ['Diyas', 'admin.diyas.index', 'manage-diyas'],
        ['Service Categories', 'admin.service-categories.index', 'manage-service-categories'],
        ['Deities', 'admin.deities.index', 'manage-deities'],
        ['Diya Sessions', 'admin.bookings.diya', 'view-bookings'],
        ['Pooja Bookings', 'admin.bookings.pooja', 'view-bookings'],
        ['Hawan Bookings', 'admin.bookings.hawan', 'view-bookings'],
        ['Donations', 'admin.donations.index', 'view-donations'],
        ['Audio Library', 'admin.audio.index', 'manage-audio'],
        ['Playlists', 'admin.playlists.index', 'manage-playlists'],
        ['Blogs', 'admin.blogs.index', 'manage-blogs'],
        ['Notifications', 'admin.notifications.index', 'view-notifications'],
        ['Reports', 'admin.reports.index', 'view-reports'],
        ['Disputes', 'admin.disputes.index', 'view-reports'],
        ['Payouts', 'admin.payouts.index', 'view-reports'],
        ['Admin Roles', 'admin.roles.index', 'manage-roles'],
        ['Permissions', 'admin.permissions.index', 'manage-permissions'],
        ['Admin Users', 'admin.admins.index', 'manage-admins'],
        ['Settings', 'admin.settings.index', 'manage-settings'],
    ];
@endphp
<aside class="sidebar">
    <div class="brand">BhaktiDeep</div>
    <div class="tagline">Har Deep Mein Bhakti</div>
    <nav class="nav">
        @foreach($items as [$label, $route, $permission])
            @if(!$permission || $admin?->hasPermission($permission))
                <a class="{{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}" style="display:flex;align-items:center;justify-content:space-between">
                    {{ $label }}
                    @if($label === 'Pandit Messages' && $totalUnread > 0)
                        <span style="background:#e85d04;color:#fff;border-radius:999px;font-size:11px;font-weight:900;padding:2px 8px">{{ $totalUnread }}</span>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>
</aside>
