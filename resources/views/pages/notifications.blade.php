@extends('layouts.app')

@section('title', 'Notifications - BhaktiDeep')
@section('description', 'View your BhaktiDeep booking, payment, refund, report, and session updates.')

@push('styles')
<style>
.notifications-page { background: linear-gradient(180deg, #fff8e8, #fbf4df); min-height: 70vh; }
.notifications-wrap { padding: 46px 12px 70px; }
.notifications-head { align-items: center; display: flex; justify-content: space-between; gap: 18px; flex-wrap: wrap; margin-bottom: 22px; }
.notifications-head h1 { color: var(--cream); font-size: 38px; margin: 0; }
.notifications-head p { color: var(--muted); margin: 6px 0 0; }
.notifications-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.notification-list { display: grid; gap: 12px; }
.notification-row { align-items: flex-start; background: rgba(255,255,255,.58); border: 1px solid rgba(199,141,34,.22); border-radius: 16px; display: grid; gap: 14px; grid-template-columns: 46px minmax(0, 1fr) auto; padding: 16px; }
.notification-row.unread { background: rgba(232,91,33,.08); border-color: rgba(232,91,33,.32); }
.notification-row > span { align-items: center; background: linear-gradient(135deg, var(--gold), var(--saffron)); border-radius: 14px; color: #fff; display: grid; height: 46px; justify-content: center; width: 46px; }
.notification-row h2 { color: var(--cream); font-family: "Inter", sans-serif; font-size: 15px; font-weight: 800; margin: 0 0 6px; }
.notification-row p { color: rgba(55,32,22,.82); font-size: 14px; line-height: 1.55; margin: 0; overflow-wrap: anywhere; }
.notification-meta { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.notification-meta em { background: rgba(199,141,34,.14); border-radius: 999px; color: #8a541b; font-size: 11px; font-style: normal; font-weight: 800; padding: 5px 9px; }
.notification-meta small { color: var(--muted); font-size: 12px; }
.notification-read-form { align-self: center; }
.notification-read-form button { white-space: nowrap; }
.notification-empty { color: var(--muted); padding: 48px 12px; text-align: center; }
.notification-empty i { display: block; font-size: 42px; margin-bottom: 12px; }
@media (max-width: 767px) {
    .notification-row { grid-template-columns: 42px minmax(0, 1fr); }
    .notification-read-form { grid-column: 1 / -1; }
}
</style>
@endpush

@section('body')
<main class="notifications-page">
    <section class="container notifications-wrap">
        <div class="notifications-head">
            <div>
                <h1>Notifications</h1>
                <p>{{ $unreadCount }} unread update{{ $unreadCount === 1 ? '' : 's' }}</p>
            </div>
            {{-- <div class="notifications-actions">
                <a class="btn btn-outline-saffron rounded-pill" href="{{ route('user.profile') }}">
                    <i class="bi bi-person-circle"></i> Profile
                </a>
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('user.notifications.read-all') }}">
                        @csrf
                        <button class="btn btn-saffron rounded-pill" type="submit">
                            <i class="bi bi-check2-all"></i> Mark All Read
                        </button>
                    </form>
                @endif
            </div> --}}
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="notification-list">
            @forelse($notifications as $notification)
                @php($record = $notification['record'])
                <article class="notification-row {{ $record->read_at ? '' : 'unread' }}">
                    <span><i class="bi {{ $notification['icon'] }}"></i></span>
                    <div>
                        <h2>{{ $record->subject ?: $notification['category'].' Update' }}</h2>
                        <p>{{ $record->message }}</p>
                        <div class="notification-meta">
                            <em>{{ $notification['category'] }}</em>
                            <em>{{ str_replace('_', ' ', $record->channel) }}</em>
                            <small>{{ $record->created_at?->format('d M Y, h:i A') ?? 'Recent' }}</small>
                            @if($record->read_at)
                                <small>Read {{ $record->read_at->diffForHumans() }}</small>
                            @endif
                        </div>
                    </div>
                    @if(!$record->read_at)
                        <form class="notification-read-form" method="POST" action="{{ route('user.notifications.read', ['notification' => $record]) }}">
                            @csrf
                            <button class="btn btn-outline-saffron btn-sm rounded-pill" type="submit">
                                <i class="bi bi-check2"></i> Mark Read
                            </button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="notification-empty">
                    <i class="bi bi-bell-slash"></i>
                    <p>No notifications yet.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
</main>
@endsection
