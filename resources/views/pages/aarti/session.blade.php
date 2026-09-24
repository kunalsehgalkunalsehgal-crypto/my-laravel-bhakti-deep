@extends('layouts.app')


@section('title', $deity->name . ' Aarti Darshan - BhaktiDeep')


@section('description', 'Experience ' . $deity->name . ' Aarti Darshan on BhaktiDeep.')


@push('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | AARTI SESSION
    | Uses the same site-wide visual language:
    | .page-shell / .page-hero / .page-section / .glass /
    | .live-player / .btn-saffron / .btn-outline-saffron
    |--------------------------------------------------------------------------
    */

    /*
     * .section-heading is a shared component used for desktop section rows
     * elsewhere on the site. It intentionally uses flex, which would place
     * the Aarti hero title and description side-by-side. The Aarti hero is a
     * page introduction, so keep its content in a normal vertical flow.
     */
    .aarti-session-page .aarti-hero-heading {
        display: block;
        margin-bottom: 24px;
    }

    .aarti-session-page .aarti-hero-heading .section-kicker {
        display: inline-flex;
    }

    .aarti-session-page .aarti-hero-heading h1 {
        display: block;
        margin-top: 20px;
        margin-bottom: 0;
    }

    .aarti-session-page .aarti-hero-heading p {
        display: block;
        margin-top: 18px;
        margin-bottom: 0;
        max-width: 680px;
    }

    .aarti-session-page .aarti-session-player {
        overflow: hidden;
        min-height: 0;
        background: #000;
    }

    .aarti-session-page .aarti-session-video {
        display: block;
        width: 100%;
        height: clamp(360px, 58vw, 650px);
        object-fit: contain;
        background: #000;
    }

    .aarti-session-page .aarti-session-player::after {
        content: none;
    }

    .aarti-session-page .aarti-session-player-label {
        position: absolute;
        left: 20px;
        bottom: 20px;
        z-index: 2;

        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding: 9px 14px;

        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 999px;

        background: rgba(55, 32, 22, .68);
        backdrop-filter: blur(10px);

        color: var(--cream);
        font-size: 12px;
        font-weight: 800;
    }

    .aarti-session-page .aarti-panel {
        height: 100%;
        padding: 26px;
        border-radius: 22px;
    }

    .aarti-session-page .aarti-panel-label {
        display: inline-flex;
        align-items: center;
        gap: 7px;

        margin-bottom: 10px;

        color: var(--gold);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .aarti-session-page .aarti-panel h2,
    .aarti-session-page .aarti-panel h3 {
        color: var(--cream);
        margin-bottom: 9px;
    }

    .aarti-session-page .aarti-muted-text {
        color: var(--muted);
        line-height: 1.65;
        margin: 0;
    }

    .aarti-session-page .aarti-audio-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
    }

    .aarti-session-page .aarti-secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;

        min-height: 44px;
        padding: 10px 18px;

        border: 1px solid rgba(232, 91, 33, .35);
        border-radius: 999px;

        background: rgba(255, 255, 255, .6);
        color: #9b4c14;

        font-size: 13px;
        font-weight: 700;
    }

    .aarti-session-page .aarti-secondary-btn:hover {
        background: rgba(232, 91, 33, .08);
        border-color: rgba(232, 91, 33, .5);
        color: #9b4c14;
    }

    .aarti-session-page .aarti-volume-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    .aarti-session-page .aarti-volume-row span {
        color: var(--muted);
        font-size: 13px;
    }

    .aarti-session-page .aarti-volume-row input {
        width: min(260px, 100%);
        accent-color: var(--saffron);
    }

    .aarti-session-page .aarti-audio-missing,
    .aarti-session-page .aarti-donation-message {
        color: var(--muted);
    }

    .aarti-session-page .aarti-audio-missing {
        margin-top: 15px;
        padding: 13px 15px;

        border: 1px solid rgba(199, 141, 34, .22);
        border-radius: 14px;
        background: rgba(251, 244, 223, .55);
    }

    .aarti-session-page .aarti-donation-values {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 19px;
    }

    .aarti-session-page .aarti-donation-value {
        min-height: 48px;

        border: 1px solid rgba(199, 141, 34, .24);
        border-radius: 14px;

        background: rgba(251, 244, 223, .58);
        color: var(--cream);

        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
    }

    .aarti-session-page .aarti-donation-value:hover,
    .aarti-session-page .aarti-donation-value.active {
        border-color: rgba(232, 91, 33, .55);
        background: rgba(232, 91, 33, .08);
        color: var(--saffron);
    }

    .aarti-session-page .aarti-custom-amount {
        display: none;
        margin-top: 12px;
    }

    .aarti-session-page .aarti-custom-amount.show {
        display: block;
    }

    .aarti-session-page .aarti-custom-amount input {
        width: 100%;
    }

    .aarti-session-page .aarti-donate-btn {
        width: 100%;
        margin-top: 14px;
    }

    .aarti-session-page .aarti-login-link {
        display: block;
        margin-top: 18px;
        text-align: center;
    }

    .aarti-session-page .aarti-session-note {
        max-width: 700px;
        margin: 28px auto 0;
        text-align: center;
        color: var(--muted);
        font-size: 12px;
    }

    @media (max-width: 767.98px) {

        .aarti-session-page .aarti-session-video {
            height: 420px;
        }

        .aarti-session-page .aarti-panel {
            padding: 20px;
        }

        .aarti-session-page .aarti-audio-actions {
            flex-direction: column;
        }

        .aarti-session-page .aarti-audio-actions .btn,
        .aarti-session-page .aarti-secondary-btn {
            width: 100%;
        }
    }
</style>
@endpush


@section('body')

@php

$imageUrl = asset('assets/temple-hero.jpg');

if ($deity->featured_image) {
$imageUrl = str_starts_with($deity->featured_image, 'assets/')
? asset($deity->featured_image)
: asset('storage/' . $deity->featured_image);
}

$videoUrl = null;

if ($deity->aarti_video) {
$videoUrl = str_starts_with($deity->aarti_video, 'assets/')
? asset($deity->aarti_video)
: asset('storage/' . $deity->aarti_video);
}

@endphp


<div id="aartiSessionApp" class="page-shell aarti-session-page" @auth
    data-donation-url="{{ route('aarti.donation.create', $deity) }}" @endauth>


    {{-- HERO --}}

    <section class="page-hero compact">

        <div class="container">

            <div class="breadcrumb-line">

                <a href="{{ route('aarti.index') }}">
                    Aarti Darshan
                </a>

                <span>›</span>

                <strong>
                    {{ $deity->name }}
                </strong>

            </div>


            <div class="section-heading aarti-hero-heading mt-4">

                <span class="section-kicker">
                    <i class="bi bi-flower1"></i>
                    DIVYA AARTI DARSHAN
                </span>

                <h1 class="mt-3">
                    {{ $deity->name }} Aarti
                </h1>

                <p>
                    Darshan dekhiye aur neeche diye gaye controls se
                    Aarti audio shuru karein.
                </p>

            </div>

        </div>

    </section>


    {{-- VIDEO --}}

    <section class="page-section pt-2">

        <div class="container">

            <div class="
                    live-player
                    aarti-session-player
                ">

                @if ($videoUrl)
                <video id="aartiVideo" class="aarti-session-video" autoplay muted loop playsinline preload="metadata"
                    poster="{{ $imageUrl }}">

                    <source src="{{ $videoUrl }}">

                    Your browser does not support video.

                </video>
                @else
                <img src="{{ $imageUrl }}" class="aarti-session-video" alt="{{ $deity->name }}">
                @endif


                <div class="aarti-session-player-label">

                    <i class="bi bi-flower1"></i>

                    {{ $deity->name }} Darshan

                </div>

            </div>


            {{-- AUDIO + DONATION --}}

            <div class="row g-4 mt-1">


                {{-- AUDIO --}}

                <div class="col-12 col-lg-7">

                    <div class="glass aarti-panel">

                        <div class="aarti-panel-label">
                            <i class="bi bi-music-note-beamed"></i>
                            AARTI
                        </div>


                        @if ($deity->aartiAudio && $deity->aartiAudio->status === 'active' &&
                        $deity->aartiAudio->audio_file)
                        <h2>
                            {{ $deity->aartiAudio->title }}
                        </h2>

                        <p class="aarti-muted-text">
                            Audio automatically start nahi hoga.
                            Jab aap ready ho, button dabakar Aarti shuru karein.
                        </p>


                        <audio id="aartiAudio" preload="metadata">

                            <source src="{{ $deity->aartiAudio->fileUrl() }}">

                        </audio>


                        <div class="aarti-audio-actions">

                            <button type="button" id="startAartiButton" class="btn btn-saffron rounded-pill px-4">
                                🪔 Aarti Shuru Kare
                            </button>


                            <button type="button" id="restartAartiButton" class="aarti-secondary-btn">

                                <i class="bi bi-arrow-counterclockwise"></i>

                                Restart

                            </button>

                        </div>


                        <div class="aarti-volume-row">

                            <span>
                                <i class="bi bi-volume-up"></i>
                                Volume
                            </span>

                            <input type="range" id="aartiVolume" min="0" max="1" value="1" step="0.05">

                        </div>
                        @else
                        <h2>
                            {{ $deity->name }} Aarti
                        </h2>

                        <div class="aarti-audio-missing">
                            Is deity ki Aarti audio abhi available nahi hai.
                        </div>
                        @endif

                    </div>

                </div>


                {{-- DONATION --}}

                <div class="col-12 col-lg-5">

                    <aside class="glass aarti-panel">

                        <div class="aarti-panel-label">
                            <i class="bi bi-heart-fill"></i>
                            SEVA
                        </div>

                        <h3>
                            🙏 Quick Dakshina
                        </h3>

                        <p class="aarti-muted-text">
                            Apni shraddha aur ichha se Dakshina arpit karein.
                        </p>


                        @auth

                        <div class="aarti-donation-values">

                            <button type="button" class="aarti-donation-value active" data-amount="101">
                                ₹101
                            </button>

                            <button type="button" class="aarti-donation-value" data-amount="251">
                                ₹251
                            </button>

                            <button type="button" class="aarti-donation-value" data-amount="501">
                                ₹501
                            </button>

                            <button type="button" class="aarti-donation-value" data-amount="custom">
                                Custom
                            </button>

                        </div>


                        <div id="customAmountBox" class="aarti-custom-amount">

                            <input type="number" id="customAmount" class="form-control sacred-input" min="1"
                                max="100000" placeholder="Enter amount">

                        </div>


                        <button type="button" id="donateButton" class="
                                    btn
                                    btn-saffron
                                    rounded-pill
                                    aarti-donate-btn
                                ">
                            🙏 Offer Dakshina
                        </button>


                        <div id="donationMessage" class="aarti-donation-message"></div>
                        @else
                        <a href="{{ route('login') }}" class="
                                    btn
                                    btn-saffron
                                    rounded-pill
                                    aarti-login-link
                            ">
                            Login to Offer Dakshina
                        </a>

                        @endauth

                    </aside>

                </div>

            </div>


            <div class="aarti-session-note">

                This is a devotional Aarti Darshan experience.
                Video is prerecorded and is not a real-time temple stream.

            </div>

        </div>

    </section>

</div>

@endsection


@push('scripts')
@auth

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

@endauth

<script src="{{ asset('js/aarti-session.js') }}"></script>
@endpush