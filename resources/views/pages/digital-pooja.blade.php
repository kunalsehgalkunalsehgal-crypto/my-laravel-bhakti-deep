@extends('layouts.app')

@section('title', $session->pooja_type_title.' - BhaktiDeep')
@section('description', 'Watch your paid Digital Pooja and begin the mantra when you are ready.')

@push('styles')
<style>
    .digital-pooja-page { min-height:70vh; background:linear-gradient(180deg,#fff8e8,#fbf4df); }
    .digital-pooja-card { padding:22px; border:1px solid rgba(171,114,23,.18); border-radius:20px; background:rgba(255,255,255,.78); box-shadow:0 16px 45px rgba(102,62,10,.08); }
    .digital-pooja-video { width:100%; max-height:560px; border-radius:16px; background:#160d05; object-fit:contain; }
    .digital-pooja-audio-actions { display:flex; flex-wrap:wrap; gap:12px; margin:18px 0; }
    .digital-pooja-secondary { border:1px solid #d19a3a; border-radius:999px; padding:10px 18px; color:#8a5b12; background:#fff; }
    .digital-pooja-volume { display:flex; align-items:center; gap:14px; }
    .digital-pooja-volume input { flex:1; }
    .digital-pooja-countdown { color:#8a5b12; font-weight:700; }
</style>
@endpush

@section('body')
<main class="digital-pooja-page py-5">
    <section class="container" style="max-width:960px;">
        <div class="text-center mb-4">
            <span class="eyebrow"><i class="bi bi-play-circle"></i> DIGITAL POOJA</span>
            <h1 class="mt-3">{{ $session->pooja_type_title }}</h1>
            <p class="text-muted mb-0">Prepared for {{ $session->sankalp?->full_name ?? 'you' }}</p>
            <p class="digital-pooja-countdown mt-2 mb-0">
                Access Remaining: <span id="digitalAccessRemaining">00:00:00</span>
            </p>
        </div>

        <div id="digitalPoojaContent">
            <div class="digital-pooja-card mb-4">
                @if($session->digital_video_path)
                    <video id="digitalPoojaVideo" class="digital-pooja-video" autoplay muted loop playsinline preload="metadata" controls>
                        <source src="{{ route('pooja.digital.media', ['session' => $session, 'media' => 'video']) }}">
                        Your browser does not support video playback.
                    </video>
                @else
                    <div class="alert alert-info mb-0">Digital Pooja video is not configured yet.</div>
                @endif
            </div>

            <div class="digital-pooja-card">
                <div class="eyebrow"><i class="bi bi-music-note-beamed"></i> MANTRA</div>
                @if($session->digital_audio_path)
                    <h2 class="h4 mt-3">{{ $session->digital_audio_title ?: 'Pooja Mantra' }}</h2>
                    <p class="text-muted">Mantra automatically start nahi hoga. Jab aap ready ho, button dabaiye.</p>

                    <audio id="digitalPoojaAudio" preload="metadata">
                        <source src="{{ route('pooja.digital.media', ['session' => $session, 'media' => 'audio']) }}">
                    </audio>

                    <div class="digital-pooja-audio-actions">
                        <button type="button" id="startDigitalPooja" class="btn btn-saffron rounded-pill px-4">&#128591; Pooja Shuru Kare</button>
                        <button type="button" id="restartDigitalPooja" class="digital-pooja-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Restart
                        </button>
                    </div>

                    <label class="digital-pooja-volume" for="digitalPoojaVolume">
                        <span><i class="bi bi-volume-up"></i> Volume</span>
                        <input type="range" id="digitalPoojaVolume" min="0" max="1" value="1" step="0.05">
                    </label>
                @else
                    <div class="alert alert-warning mt-3 mb-0">Mantra audio is not configured for this Digital Pooja.</div>
                @endif
            </div>
        </div>

        <div id="digitalPoojaCompleted" class="digital-pooja-card text-center d-none">
            <h2 class="h4 mb-0">&#128591; Digital Pooja Session Completed</h2>
        </div>

        <div class="text-center mt-4">
            <a class="btn btn-outline-saffron rounded-pill" href="{{ route('user.profile') }}">
                <i class="bi bi-arrow-left"></i> Back to My Bookings
            </a>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('digitalPoojaVideo');
    const audio = document.getElementById('digitalPoojaAudio');
    const startButton = document.getElementById('startDigitalPooja');
    const restartButton = document.getElementById('restartDigitalPooja');
    const volume = document.getElementById('digitalPoojaVolume');
    const remaining = document.getElementById('digitalAccessRemaining');
    const content = document.getElementById('digitalPoojaContent');
    const completed = document.getElementById('digitalPoojaCompleted');
    const expiresAt = new Date(@json($session->expires_at->toIso8601String())).getTime();

    function completeDigitalPooja() {
        [video, audio].forEach(function (media) {
            if (!media) return;
            media.pause();
            media.removeAttribute('src');
            media.querySelector('source')?.removeAttribute('src');
            media.load();
        });

        content?.classList.add('d-none');
        completed?.classList.remove('d-none');
    }

    function updateCountdown() {
        const seconds = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
        const hours = String(Math.floor(seconds / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
        const remainder = String(seconds % 60).padStart(2, '0');

        remaining.textContent = hours + ':' + minutes + ':' + remainder;

        if (seconds === 0) {
            completeDigitalPooja();
            return false;
        }

        return true;
    }

    if (updateCountdown()) {
        const timer = setInterval(function () {
            if (!updateCountdown()) clearInterval(timer);
        }, 1000);
    }

    if (video) {
        video.muted = true;
        video.play().catch(function () {});
    }

    if (!audio || !startButton) return;

    const readyLabel = startButton.textContent.trim();
    const pauseLabel = 'Pause';

    startButton.addEventListener('click', async function () {
        try {
            if (audio.paused) {
                await audio.play();
                startButton.textContent = pauseLabel;
            } else {
                audio.pause();
                startButton.textContent = readyLabel;
            }
        } catch (error) {
            alert('Mantra audio start nahi ho paya. Please dobara try karein.');
        }
    });

    restartButton?.addEventListener('click', async function () {
        audio.currentTime = 0;
        try {
            await audio.play();
            startButton.textContent = pauseLabel;
        } catch (error) {
            alert('Mantra audio restart nahi ho paya.');
        }
    });

    volume?.addEventListener('input', function () {
        audio.volume = Number(volume.value);
    });

    audio.addEventListener('ended', function () {
        audio.currentTime = 0;
        startButton.textContent = readyLabel;
    });
});
</script>
@endpush
