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
    $donation = $session->latestPaymentAttempt?->donation;
    $meta = $session->admin_note ? (json_decode($session->admin_note, true) ?: []) : [];
    $isPaid = $session->payment_status === 'paid';
    $isScheduled = $session->status === 'scheduled';
    $isActive = $session->status === 'active';
    $isCompleted = $session->status === 'completed';
    $startAt = $session->start_at?->toIso8601String();
    $endAt = $session->end_at?->toIso8601String() ?: $session->expires_at?->toIso8601String();
    $amount = (float) ($donation?->amount ?? $meta['donation_amount'] ?? $meta['total_amount'] ?? $meta['seva_amount'] ?? 0);
    $mantraAudio = $mantraAudio ?? null;
    $ambientAudio = $ambientAudio ?? null;
    $particleStyles = ['none', 'golden_sparkles', 'flower_petals', 'smoke', 'snow', 'divine_light'];
    $flameStyles = ['normal', 'golden', 'orange', 'blue', 'soft', 'intense'];
    $particleStyle = in_array($deity?->particle_style, $particleStyles, true) ? $deity->particle_style : 'golden_sparkles';
    $flameStyle = in_array($deity?->flame_style, $flameStyles, true) ? $deity->flame_style : 'normal';
    $isColor = fn ($color) => is_string($color) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $color);
    $primaryColor = $isColor($deity?->primary_color) ? $deity->primary_color : '#c78d22';
    $secondaryColor = $isColor($deity?->secondary_color) ? $deity->secondary_color : '#e85b21';
    $glowColor = $isColor($deity?->glow_color) ? $deity->glow_color : '#ffbc4c';
    $backgroundImage = null;

    if ($deity?->temple_background_image) {
        $backgroundImage = str_starts_with($deity->temple_background_image, 'assets/')
            ? asset($deity->temple_background_image)
            : asset('storage/'.$deity->temple_background_image);
    }

    $statusHeading = match (true) {
        !$isPaid => 'Payment Pending',
        $isScheduled => 'Is Scheduled',
        $isCompleted => 'Has Completed',
        default => 'Is Glowing',
    };
    $statusCopy = match (true) {
        !$isPaid => 'Complete payment to activate your diya offering.',
        $isScheduled => 'Your diya offering is scheduled and will glow automatically at its start time.',
        $isCompleted => 'Your diya offering has completed with devotion.',
        default => 'Your diya is glowing with your sankalp and temple ambience.',
    };
    $showOffering = $isPaid;
@endphp

<main
    class="page-shell ld-page diya-session-page deity-theme deity-particles-{{ $particleStyle }} deity-flame-{{ $flameStyle }} {{ $showOffering ? 'is-paid' : 'is-unpaid' }}"
    style="
        --diya-primary: {{ $primaryColor }};
        --diya-secondary: {{ $secondaryColor }};
        --diya-glow: {{ $glowColor }};
        --diya-bg-image: {{ $backgroundImage ? "url('".$backgroundImage."')" : 'none' }};
    "
>
    <div class="diya-theme-bg"></div>
    <div class="particles subtle deity-particles">
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
                    <span><small>Diya</small>{{ $diya?->name ?? ($meta['diya_name'] ?? 'Diya Offering') }}</span>
                    <span><small>Deity</small>{{ $deity?->name ?? ($meta['deity_name'] ?? '-') }}</span>
                    <span><small>Devotee</small>{{ $sankalp?->full_name ?? '-' }}</span>
                    <span><small>Purpose</small>{{ $sankalp?->purpose ?? '-' }}</span>
                    <span><small>Starts</small>{{ $session->start_at?->format('d M Y, h:i A') ?? '-' }}</span>
                    <span><small>Ends</small>{{ $session->end_at?->format('d M Y, h:i A') ?? '-' }}</span>
                    <span><small>Donation</small>Rs.{{ number_format($amount, 2) }}</span>
                    <span><small>Payment</small>{{ ucfirst($session->payment_status ?? 'pending') }}</span>
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
                        <div class="diya-flame {{ !$showOffering || $isCompleted || $isScheduled ? 'dimmed' : '' }}">
                            <span></span>
                        </div>
                        <div class="diya-bowl"></div>
                        <div class="diya-glow-ring"></div>
                    </div>
                    <div class="diya-countdown">
                        <span>{{ !$isPaid ? 'Payment Pending' : ($isScheduled ? 'Starts In' : ($isCompleted ? 'Completed' : 'Remaining Time')) }}</span>
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
                    @if(!$showOffering)
                        <p class="mt-3">Mantra and ambient sound will be available after payment is completed.</p>
                        <div class="diya-audio-bars mt-4 is-paused" aria-hidden="true">
                            @for ($i = 0; $i < 36; $i++)
                                <span style="height: {{ 8 + (($i * 7) % 24) }}px; animation-delay: {{ $i * .05 }}s"></span>
                            @endfor
                        </div>
                    @else
                        @if($mantraAudio?->fileUrl())
                            <p class="mt-3">{{ $mantraAudio->title }} is playing for {{ $mantraAudio->deity?->name ?? 'the selected mantra deity' }}.</p>
                            <audio id="diyaMantraAudio" src="{{ $mantraAudio->fileUrl() }}" preload="auto" loop></audio>
                            <div class="diya-audio-player mt-4" data-audio-player data-audio-id="diyaMantraAudio">
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

                        @if($ambientAudio?->fileUrl())
                            <div class="diya-ambient-box mt-4">
                                <p class="mb-2"><strong>Ambient Sound</strong><br>{{ $ambientAudio->title }}</p>
                                <audio id="diyaAmbientAudio" src="{{ $ambientAudio->fileUrl() }}" preload="auto" loop></audio>
                                <div class="diya-audio-player" data-audio-player data-audio-id="diyaAmbientAudio">
                                    <button type="button" class="btn btn-saffron rounded-pill" data-audio-play>
                                        <i class="bi bi-play-fill"></i> Play Ambient
                                    </button>
                                    <button type="button" class="btn btn-ghost-gold rounded-pill" data-audio-pause>
                                        <i class="bi bi-pause-fill"></i> Pause
                                    </button>
                                    <button type="button" class="btn btn-ghost-gold rounded-pill" data-audio-mute>
                                        <i class="bi bi-volume-up"></i> Mute
                                    </button>
                                </div>
                                <p class="diya-audio-note mt-3 d-none" data-autoplay-note>Autoplay was blocked. Tap Play Ambient to begin.</p>
                            </div>
                        @endif

                        <div class="diya-audio-bars mt-4 {{ $mantraAudio?->fileUrl() || $ambientAudio?->fileUrl() ? '' : 'is-paused' }}" data-audio-bars aria-hidden="true">
                            @for ($i = 0; $i < 36; $i++)
                                <span style="height: {{ 8 + (($i * 7) % 24) }}px; animation-delay: {{ $i * .05 }}s"></span>
                            @endfor
                        </div>
                    @endif
                    @if($showOffering && !$mantraAudio?->fileUrl() && !$ambientAudio?->fileUrl())
                        <div class="diya-audio-player mt-4">
                            <button type="button" class="btn btn-saffron rounded-pill" disabled>
                                <i class="bi bi-play-fill"></i> Play Mantra
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sessionStatus = @json($session->status);
    const isPaid = @json($isPaid);
    const startAt = @json($startAt);
    const endAt = @json($endAt);
    const timer = document.getElementById('diyaTimer');
    const audioBars = document.querySelector('[data-audio-bars]');

    function renderTimer() {
        if (!timer) {
            return;
        }

        if (!isPaid) {
            timer.textContent = 'Payment Pending';
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

    function updateAudioBars() {
        const hasPlayingAudio = Array.from(document.querySelectorAll('audio')).some(audio => !audio.paused);
        audioBars?.classList.toggle('is-paused', !hasPlayingAudio);
    }

    async function playAudio(audio, autoplayNote) {
        if (!audio) {
            return;
        }

        try {
            await audio.play();
            autoplayNote?.classList.add('d-none');
            updateAudioBars();
        } catch (error) {
            updateAudioBars();
            autoplayNote?.classList.remove('d-none');
        }
    }

    document.querySelectorAll('[data-audio-player]').forEach(function (player) {
        const audio = document.getElementById(player.dataset.audioId);
        const playButton = player.querySelector('[data-audio-play]');
        const pauseButton = player.querySelector('[data-audio-pause]');
        const muteButton = player.querySelector('[data-audio-mute]');
        const autoplayNote = player.parentElement.querySelector('[data-autoplay-note]');
        const playText = playButton ? playButton.innerHTML : '';

        if (!audio) {
            return;
        }

        audio.loop = true;

        audio.addEventListener('pause', function () {
            if (playButton) {
                playButton.innerHTML = playText;
            }
            updateAudioBars();
        });

        audio.addEventListener('play', function () {
            if (playButton) {
                playButton.innerHTML = '<i class="bi bi-play-fill"></i> Playing';
            }
            updateAudioBars();
        });

        playButton?.addEventListener('click', function () {
            playAudio(audio, autoplayNote);
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

        playAudio(audio, autoplayNote);
    });

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
