@extends('layouts.app')

@section('title', 'Your Diya Session - BhaktiDeep')
@section('description', 'Your virtual diya is glowing with sankalp, deity and mantra ambience.')

@push('styles')
<link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
@endpush

@section('body')
@php
    $diya = $session->diya;
    $deity = $session->deity;
    $sankalp = $session->sankalp;
    $meta = $session->admin_note ? (json_decode($session->admin_note, true) ?: []) : [];
    $isScheduled = $session->status === 'scheduled';
    $isActive = $session->status === 'active';
    $isCompleted = $session->status === 'completed';
    $startAt = $session->start_at?->toIso8601String();
    $endAt = $session->end_at?->toIso8601String() ?: $session->expires_at?->toIso8601String();
    $amount = (float) ($meta['total_amount'] ?? $meta['seva_amount'] ?? 0);
    $mantraAudio = $mantraAudio ?? null;
    $statusHeading = match ($session->status) {
        'scheduled' => 'Is Scheduled',
        'completed' => 'Has Completed',
        default => 'Is Glowing',
    };
    $statusCopy = match ($session->status) {
        'scheduled' => 'Your diya offering is scheduled and will glow automatically at its start time.',
        'completed' => 'Your diya offering has completed with devotion.',
        default => 'Your diya is glowing with your sankalp and temple ambience.',
    };
@endphp

<main class="page-shell ld-page diya-session-page">
    <div class="particles subtle">
        @for ($i = 0; $i < 26; $i++)
            <span style="left: {{ ($i * 47) % 100 }}%; animation-delay: {{ $i * 0.22 }}s;"></span>
        @endfor
    </div>

    <section class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="eyebrow"><i class="bi bi-fire"></i> DIYA SESSION</span>
                <h1 class="mt-4">
                    <span>Your </span><span class="gold-text">{{ $diya?->name ?? ($meta['diya_name'] ?? 'Diya') }}</span><br>
                    <span>{{ $statusHeading }}</span>
                </h1>
                <p class="mt-4">
                    {{ $statusCopy }}
                </p>

                <div class="diya-session-meta mt-4">
                    <span><small>Deity</small>{{ $deity?->name ?? ($meta['deity_name'] ?? '-') }}</span>
                    <span><small>Sankalp</small>{{ $sankalp?->full_name ?? '-' }}</span>
                    <span><small>Purpose</small>{{ $sankalp?->purpose ?? '-' }}</span>
                    <span><small>Starts</small>{{ $session->start_at?->format('d M Y, h:i A') ?? '-' }}</span>
                    <span><small>Ends</small>{{ $session->end_at?->format('d M Y, h:i A') ?? '-' }}</span>
                    <span><small>Duration</small>{{ $diya?->duration ?? ($meta['duration'] ?? '-') }}</span>
                    <span><small>Seva Paid</small>Rs.{{ number_format($amount, 2) }}</span>
                    <span><small>Status</small>{{ ucfirst($session->status) }}</span>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-saffron rounded-pill" data-copy-session-link><i class="bi bi-link-45deg"></i> Copy Link</button>
                    <a href="{{ route('light-diya') }}" class="btn btn-ghost-gold rounded-pill"><i class="bi bi-fire"></i> Light Another Diya</a>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="glass diya-live-card">
                    <div class="diya-lamp-stage">
                        <div class="diya-flame {{ $isCompleted || $isScheduled ? 'dimmed' : '' }}">
                            <span></span>
                        </div>
                        <div class="diya-bowl"></div>
                        <div class="diya-glow-ring"></div>
                    </div>
                    <div class="diya-countdown">
                        <span>{{ $isScheduled ? 'Starts In' : ($isCompleted ? 'Completed' : 'Remaining Time') }}</span>
                        <strong id="diyaTimer">{{ $isCompleted ? '00:00:00' : '--:--:--' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container pb-5">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="glass rounded-4 p-4 p-md-5 h-100">
                    <div class="ld-step-label">Sankalp</div>
                    <h2 class="mt-2">Offering Details</h2>
                    <div class="diya-detail-list mt-3">
                        <div><span>Name</span><strong>{{ $sankalp?->full_name ?? '-' }}</strong></div>
                        <div><span>Mobile</span><strong>{{ $sankalp?->mobile ?? '-' }}</strong></div>
                        <div><span>Gotra</span><strong>{{ $sankalp?->gotra ?? '-' }}</strong></div>
                        <div><span>Mannokamna</span><strong>{{ $sankalp?->mannokamna ?? '-' }}</strong></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="glass rounded-4 p-4 p-md-5 h-100">
                    <div class="ld-step-label">Ambience</div>
                    <h2 class="mt-2">Mantra / Ambience</h2>
                    @if($mantraAudio?->fileUrl())
                        <p class="mt-3">{{ $mantraAudio->title }} is playing for {{ $mantraAudio->deity?->name ?? 'the selected mantra deity' }}.</p>
                        <audio id="diyaMantraAudio" src="{{ $mantraAudio->fileUrl() }}" preload="auto" loop></audio>
                        <div class="diya-audio-player mt-4" data-diya-audio-player>
                            <button type="button" class="btn btn-saffron rounded-pill" data-audio-play>
                                <i class="bi bi-play-fill"></i> Play Mantra
                            </button>
                            <button type="button" class="btn btn-ghost-gold rounded-pill" data-audio-pause>
                                <i class="bi bi-pause-fill"></i> Pause
                            </button>
                            <button type="button" class="btn btn-ghost-gold rounded-pill" data-audio-mute>
                                <i class="bi bi-volume-up"></i> Mute
                            </button>
                        </div>
                        <p class="diya-audio-note mt-3 d-none" data-autoplay-note>Autoplay was blocked. Tap Play Mantra to begin.</p>
                    @else
                        <p class="mt-3">No active mantra audio has been uploaded for {{ $deity?->name ?? ($meta['deity_name'] ?? 'this deity') }} yet.</p>
                    @endif
                    <div class="diya-audio-bars mt-4 {{ $mantraAudio?->fileUrl() ? '' : 'is-paused' }}" data-audio-bars aria-hidden="true">
                        @for ($i = 0; $i < 36; $i++)
                            <span style="height: {{ 8 + (($i * 7) % 24) }}px; animation-delay: {{ $i * .05 }}s"></span>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sessionStatus = @json($session->status);
    const startAt = @json($startAt);
    const endAt = @json($endAt);
    const timer = document.getElementById('diyaTimer');
    const audio = document.getElementById('diyaMantraAudio');
    const playButton = document.querySelector('[data-audio-play]');
    const pauseButton = document.querySelector('[data-audio-pause]');
    const muteButton = document.querySelector('[data-audio-mute]');
    const autoplayNote = document.querySelector('[data-autoplay-note]');
    const audioBars = document.querySelector('[data-audio-bars]');

    function renderTimer() {
        if (!timer) {
            return;
        }

        if (sessionStatus === 'completed') {
            timer.textContent = '00:00:00';
            return;
        }

        const targetAt = sessionStatus === 'scheduled' ? startAt : endAt;

        if (!targetAt) {
            timer.textContent = '--:--:--';
            return;
        }

        const remaining = new Date(targetAt).getTime() - Date.now();

        if (remaining <= 0) {
            timer.textContent = '00:00:00';
            return;
        }

        const totalSeconds = Math.floor(remaining / 1000);
        const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
        const seconds = String(totalSeconds % 60).padStart(2, '0');
        timer.textContent = hours + ':' + minutes + ':' + seconds;
    }

    renderTimer();
    window.setInterval(renderTimer, 1000);

    function setAudioPlaying(isPlaying) {
        audioBars?.classList.toggle('is-paused', !isPlaying);
        if (playButton) {
            playButton.innerHTML = isPlaying ? '<i class="bi bi-play-fill"></i> Playing' : '<i class="bi bi-play-fill"></i> Play Mantra';
        }
    }

    async function playMantra(showErrors = true) {
        if (!audio) {
            return;
        }

        try {
            await audio.play();
            autoplayNote?.classList.add('d-none');
            setAudioPlaying(true);
        } catch (error) {
            setAudioPlaying(false);
            if (showErrors) {
                autoplayNote?.classList.remove('d-none');
            }
        }
    }

    if (audio) {
        audio.loop = true;

        audio.addEventListener('ended', function () {
            audio.currentTime = 0;
            playMantra(false);
        });

        audio.addEventListener('pause', function () {
            setAudioPlaying(false);
        });

        audio.addEventListener('play', function () {
            setAudioPlaying(true);
        });

        playButton?.addEventListener('click', function () {
            playMantra(true);
        });

        pauseButton?.addEventListener('click', function () {
            audio.pause();
        });

        muteButton?.addEventListener('click', function () {
            audio.muted = !audio.muted;
            muteButton.innerHTML = audio.muted
                ? '<i class="bi bi-volume-mute"></i> Unmute'
                : '<i class="bi bi-volume-up"></i> Mute';
        });

        playMantra(true);
    }

    document.querySelector('[data-copy-session-link]')?.addEventListener('click', function () {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href);
        }

        const original = this.innerHTML;
        this.innerHTML = '<i class="bi bi-check-circle"></i> Link Copied';
        window.setTimeout(() => this.innerHTML = original, 1600);
    });
});
</script>
@endpush
@endsection
