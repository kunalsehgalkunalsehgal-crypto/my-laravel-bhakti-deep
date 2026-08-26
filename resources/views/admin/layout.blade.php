<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | BhaktiDeep</title>
    <link rel="icon" type="image/x-icon" href="https://i.pinimg.com/736x/c3/30/ae/c330aeba4ebb8971936067cd0b077c70.jpg">
@vite(['resources/js/app.js'])

    <style>
        :root { color-scheme: light; --ink:#18212f; --muted:#667085; --line:#d9e0ea; --brand:#8a3ffc; --accent:#0f9f7a; --bg:#f6f8fb; --card:#fff; --warn:#b54708; --bad:#b42318; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Arial, sans-serif; color:var(--ink); background:var(--bg); }
        a { color:inherit; text-decoration:none; }
        .admin-shell { display:grid; grid-template-columns:260px 1fr; min-height:100vh; }
        .sidebar { background:#151924; color:#f7f7fb; padding:22px 16px; position:sticky; top:0; height:100vh; overflow:auto; }
        .brand { font-weight:800; font-size:22px; margin-bottom:4px; }
        .tagline { color:#c7ccd8; font-size:12px; margin-bottom:22px; }
        .nav a { display:block; padding:10px 12px; border-radius:7px; color:#eef1f7; font-size:14px; margin-bottom:3px; }
        .nav a:hover, .nav a.active { background:#262d3d; }
        .main { min-width:0; }
        .topbar { height:64px; background:#fff; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; padding:0 24px; position:sticky; top:0; z-index:5; }
        .content { padding:24px; }
        h1 { margin:0 0 18px; font-size:28px; letter-spacing:0; }
        .panel { background:var(--card); border:1px solid var(--line); border-radius:8px; padding:18px; margin-bottom:18px; }
        .grid { display:grid; gap:14px; }
        .stats { grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); }
        .stat { border-left:4px solid var(--brand); }
        .stat span { display:block; color:var(--muted); font-size:13px; margin-bottom:8px; }
        .stat strong { font-size:22px; }
        .toolbar { display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:14px; }
        .filters { display:flex; gap:8px; flex-wrap:wrap; }
        input, select, textarea { width:100%; border:1px solid var(--line); border-radius:7px; padding:10px 11px; font:inherit; background:#fff; }
        textarea { min-height:110px; resize:vertical; }
        label { display:block; font-size:13px; color:#344054; margin-bottom:6px; font-weight:700; }
        .form-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px,1fr)); gap:14px; }
        .full { grid-column:1 / -1; }
        .btn { display:inline-flex; align-items:center; justify-content:center; min-height:38px; padding:9px 13px; border-radius:7px; border:1px solid var(--line); background:#fff; cursor:pointer; font-weight:700; font-size:14px; }
        .btn.primary { background:var(--brand); border-color:var(--brand); color:#fff; }
        .btn.danger { background:#fff5f5; border-color:#f3b7b1; color:var(--bad); }
        .btn.small { min-height:30px; padding:6px 9px; font-size:12px; }
        table { width:100%; border-collapse:collapse; background:#fff; }
        th, td { text-align:left; padding:12px; border-bottom:1px solid var(--line); vertical-align:top; font-size:14px; }
        th { color:#475467; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
        .badge { display:inline-flex; padding:4px 8px; border-radius:999px; background:#eef2ff; color:#3730a3; font-size:12px; font-weight:700; }
        .badge.active, .badge.paid, .badge.published, .badge.completed { background:#e8f7f1; color:#067647; }
        .badge.failed, .badge.cancelled, .badge.inactive { background:#fff1f0; color:var(--bad); }
        .alert { padding:11px 13px; border-radius:7px; margin-bottom:14px; border:1px solid var(--line); background:#fff; }
        .alert.success { border-color:#a6e7c8; color:#067647; background:#effaf5; }
        .alert.error { border-color:#f3b7b1; color:var(--bad); background:#fff5f5; }
        .actions { display:flex; gap:7px; flex-wrap:wrap; }
        .pagination { margin-top:12px; }
        @media (max-width: 860px) {
            .admin-shell { grid-template-columns:1fr; }
            .sidebar { position:relative; height:auto; }
            .content { padding:16px; }
            table { display:block; overflow-x:auto; white-space:nowrap; }
        }
    </style>
</head>
<body>
    <div class="admin-shell">
        @include('admin.partials.sidebar')
        <main class="main">
            <header class="topbar">
                <div>@yield('title', 'Admin')</div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="btn small" type="submit">Logout</button>
                </form>
            </header>
            <section class="content">
                @include('admin.partials.alerts')
                @yield('content')
            </section>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
