<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | BhaktiDeep</title>
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; background:#f6f8fb; font-family:Arial,sans-serif; color:#18212f; }
        .login { width:min(420px, 92vw); background:#fff; border:1px solid #d9e0ea; border-radius:8px; padding:28px; }
        h1 { margin:0 0 8px; }
        p { margin:0 0 22px; color:#667085; }
        label { display:block; font-weight:700; font-size:13px; margin:14px 0 6px; }
        input { width:100%; border:1px solid #d9e0ea; border-radius:7px; padding:11px; font:inherit; box-sizing:border-box; }
        button { width:100%; margin-top:18px; border:0; border-radius:7px; background:#8a3ffc; color:#fff; padding:12px; font-weight:800; cursor:pointer; }
        .alert { padding:10px; border-radius:7px; margin-bottom:12px; color:#b42318; background:#fff1f0; }
    </style>
</head>
<body>
    <form class="login" method="POST" action="{{ route('admin.login.store') }}">
        @csrf
        <h1>BhaktiDeep Admin</h1>
        <p>Har Deep Mein Bhakti</p>
        @if($errors->any()) <div class="alert">{{ $errors->first() }}</div> @endif
        @if(session('success')) <div class="alert">{{ session('success') }}</div> @endif
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        <label>Password</label>
        <input type="password" name="password" required>
        <label><input style="width:auto" type="checkbox" name="remember" value="1"> Remember me</label>
        <button type="submit">Login</button>
    </form>
</body>
</html>
