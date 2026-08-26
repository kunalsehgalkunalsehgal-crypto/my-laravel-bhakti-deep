@extends('layouts.app')

@section('title', 'Pandit Login - BhaktiDeep')
@section('description', 'Pandit entry page for BhaktiDeep registration.')

@push('styles')
    <link href="{{ asset('css/pandit.css') }}" rel="stylesheet">
@endpush

@section('body')
<main class="pandit-login-page">
    <section class="pandit-login-panel">
        <div class="pandit-kicker"><i class="bi bi-fire"></i> BhaktiDeep Pandit Portal</div>
        <h1>Serve devotees with grace, discipline, and trust.</h1>
        @if(session('error'))<p style="color:red">{{ session('error') }}</p>@endif
        <form method="POST" action="{{ route('login.send-otp') }}">
            @csrf
            <label>Email<input type="email" name="email" required></label>
            <button type="submit" class="pandit-primary-btn"><i class="bi bi-box-arrow-in-right"></i> Send OTP</button>
        </form>
        <p style="margin-top:16px">New pandit? <a href="{{ route('pandit.register') }}">Register here</a></p>
    </section>
</main>
@endsection
