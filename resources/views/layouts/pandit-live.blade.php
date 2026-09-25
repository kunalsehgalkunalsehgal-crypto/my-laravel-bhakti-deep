<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pandit Live Session - BhaktiDeep')</title>
    <link rel="icon" type="image/x-icon" href="https://i.pinimg.com/736x/c3/30/ae/c330aeba4ebb8971936067cd0b077c70.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pandit.css') }}" rel="stylesheet">
    <style>
        body.pandit-live-body {
            min-height: 100vh;
            margin: 0;
            color: var(--cream);
            background:
                radial-gradient(circle at top left, rgba(255, 231, 161, .44), transparent 34%),
                linear-gradient(180deg, #fff9e9 0%, var(--brown) 52%, #f8ead0 100%);
            font-family: "Inter", sans-serif;
            overflow-x: hidden;
        }

        .pandit-live-header {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px clamp(16px, 4vw, 40px);
            border-bottom: 1px solid rgba(199, 141, 34, .18);
            background: rgba(251, 244, 223, .9);
            backdrop-filter: blur(18px);
        }

        .pandit-live-brand,
        .pandit-live-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pandit-live-brand {
            color: var(--cream);
            text-decoration: none;
        }

        .pandit-live-brand strong {
            display: block;
            font-family: "Cinzel", serif;
            font-size: 20px;
            line-height: 1.1;
        }

        .pandit-live-brand span span,
        .pandit-live-profile span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .pandit-live-profile {
            text-align: right;
        }

        .pandit-live-profile strong {
            display: block;
            font-size: 14px;
        }

        .pandit-live-button {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--saffron), var(--saffron-dark));
            color: #fff;
            font-weight: 900;
            padding: 0 16px;
            text-decoration: none;
            box-shadow: 0 16px 34px -20px rgba(232, 91, 33, .8);
        }

        .pandit-live-button.secondary {
            border: 1px solid rgba(232, 91, 33, .32);
            background: rgba(255, 255, 255, .72);
            color: #9b4c14;
            box-shadow: none;
        }

        .pandit-live-button.icon-only {
            width: 42px;
            padding: 0;
        }

        .pandit-live-shell {
            width: min(1440px, 100%);
            margin: 0 auto;
            padding: clamp(20px, 4vw, 34px);
        }

        @media (max-width: 720px) {
            .pandit-live-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .pandit-live-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .pandit-live-profile {
                text-align: left;
            }
        }
    </style>
    @vite(['resources/js/app.js'])
    @stack('styles')
</head>
<body class="pandit-live-body">
    <header class="pandit-live-header">
        <a class="pandit-live-brand" href="{{ route('pandit.dashboard') }}">
            <span class="brand-icon"><i class="bi bi-fire"></i></span>
            <span>
                <strong class="gold-text">BhaktiDeep</strong>
                <span>Pandit Live Room</span>
            </span>
        </a>

        <div class="pandit-live-actions">
            <a href="{{ route('pandit.live-sessions.index') }}" class="pandit-live-button secondary">
                <i class="bi bi-camera-video"></i> Live Sessions
            </a>
            <a href="{{ route('pandit.bookings.index') }}" class="pandit-live-button secondary">
                <i class="bi bi-calendar2-check"></i> Bookings
            </a>
            <div class="pandit-live-profile">
                <strong>{{ $pandit->pandit_name ?: $pandit->full_name }}</strong>
                <span>{{ ucfirst(str_replace('_', ' ', $pandit->status)) }}</span>
            </div>
            <form method="POST" action="{{ route('pandit.logout') }}">
                @csrf
                <button type="submit" class="pandit-live-button icon-only" aria-label="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </header>

    <main class="pandit-live-shell">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
