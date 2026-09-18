@extends('layouts.aarti-session')


@section(
    'title',
    $deity->name.' Aarti Darshan - BhaktiDeep'
)


@section(
    'description',
    'Experience '.$deity->name.' Aarti Darshan on BhaktiDeep.'
)


@section('body')

@php

    /*
    |--------------------------------------------------------------------------
    | FEATURED IMAGE
    |--------------------------------------------------------------------------
    */

    $imageUrl =
        asset(
            'assets/temple-hero.jpg'
        );


    if ($deity->featured_image) {

        $imageUrl =
            str_starts_with(
                $deity->featured_image,
                'assets/'
            )
            ?
            asset(
                $deity->featured_image
            )
            :
            asset(
                'storage/'.
                $deity->featured_image
            );
    }


    /*
    |--------------------------------------------------------------------------
    | VIDEO URL
    |--------------------------------------------------------------------------
    */

    $videoUrl = null;


    if ($deity->aarti_video) {

        $videoUrl =
            str_starts_with(
                $deity->aarti_video,
                'assets/'
            )
            ?
            asset(
                $deity->aarti_video
            )
            :
            asset(
                'storage/'.
                $deity->aarti_video
            );
    }

@endphp



<div
    id="aartiSessionApp"

    @auth
        data-donation-url="{{ route('aarti.donation.create', $deity) }}"
    @endauth
>


    {{-- =========================================================
       TOP BAR
    ========================================================== --}}

    <header class="aarti-topbar">

        <div class="aarti-topbar-inner">


            <a
                href="{{ route('aarti.index') }}"
                class="aarti-exit-btn"
            >

                <i class="bi bi-arrow-left"></i>

                Exit Aarti

            </a>



            <div class="aarti-brand">

                <span>
                    🪔
                </span>

                BhaktiDeep

            </div>



            <div class="aarti-session-badge">

                <span class="aarti-session-dot"></span>

                Aarti Session

            </div>


        </div>

    </header>



    {{-- =========================================================
       MAIN PAGE
    ========================================================== --}}

    <main class="aarti-session-main">


        {{-- HEADING --}}

        <section class="aarti-session-heading">


            <span class="aarti-kicker">

                DIVYA AARTI DARSHAN

            </span>


            <h1>

                {{ $deity->name }} Aarti

            </h1>


            <p>

                Darshan automatically chal rahe hain.
                Aarti shuru karne ke liye neeche
                “Aarti Shuru Kare” button dabaiye.

            </p>


        </section>



        {{-- =====================================================
           VIDEO PLAYER
        ====================================================== --}}

        <section class="aarti-player-shell">


            @if($videoUrl)


                <video
                    id="aartiVideo"
                    class="aarti-video"

                    autoplay
                    muted
                    loop
                    playsinline

                    preload="metadata"

                    poster="{{ $imageUrl }}"
                >

                    <source
                        src="{{ $videoUrl }}"
                    >

                    Your browser does not
                    support video.

                </video>


            @else


                <img
                    src="{{ $imageUrl }}"

                    alt="{{ $deity->name }}"

                    class="aarti-video"
                >


            @endif



            <div
                class="aarti-video-overlay"
            ></div>



            <div
                class="aarti-video-label"
            >

                <i
                    class="bi bi-flower1"
                ></i>

                {{ $deity->name }}
                Darshan

            </div>


        </section>



        {{-- =====================================================
           AUDIO + DONATION
        ====================================================== --}}

        <section class="aarti-bottom-grid">


            {{-- ================================================
               AUDIO
            ================================================= --}}

            <div class="aarti-panel">


                <div
                    class="aarti-panel-label"
                >

                    AARTI

                </div>


                @if(
                    $deity->aartiAudio
                    &&
                    $deity->aartiAudio->status === 'active'
                    &&
                    $deity->aartiAudio->audio_file
                )


                    <h2>

                        {{
                            $deity
                                ->aartiAudio
                                ->title
                        }}

                    </h2>


                    <p
                        class="aarti-muted-text"
                    >

                        Audio automatically
                        start nahi hoga.
                        Jab aap ready ho,
                        button dabakar Aarti
                        shuru karein.

                    </p>



                    <audio
                        id="aartiAudio"

                        preload="metadata"
                    >

                        <source
                            src="{{
                                $deity
                                    ->aartiAudio
                                    ->fileUrl()
                            }}"
                        >

                    </audio>



                    <div
                        class="aarti-audio-actions"
                    >


                        <button
                            type="button"

                            id="startAartiButton"

                            class="aarti-primary-btn"
                        >

                            🪔 Aarti Shuru Kare

                        </button>



                        <button
                            type="button"

                            id="restartAartiButton"

                            class="aarti-secondary-btn"
                        >

                            <i
                                class="bi bi-arrow-counterclockwise"
                            ></i>

                            Restart

                        </button>


                    </div>



                    <div
                        class="aarti-volume-row"
                    >

                        <span>

                            <i
                                class="bi bi-volume-up"
                            ></i>

                            Volume

                        </span>


                        <input
                            type="range"

                            id="aartiVolume"

                            min="0"
                            max="1"

                            value="1"

                            step="0.05"
                        >

                    </div>


                @else


                    <h2>

                        {{ $deity->name }}
                        Aarti

                    </h2>


                    <div
                        class="aarti-audio-missing"
                    >

                        Is deity ki Aarti
                        audio abhi available
                        nahi hai.

                    </div>


                @endif


            </div>



            {{-- ================================================
               DONATION
            ================================================= --}}

            <aside class="aarti-panel">


                <div
                    class="aarti-panel-label"
                >

                    SEVA

                </div>


                <h3>

                    🙏 Quick Dakshina

                </h3>


                <p
                    class="aarti-muted-text"
                >

                    Apni shraddha aur
                    ichha se Dakshina
                    arpit karein.

                </p>



                @auth


                    <div
                        class="aarti-donation-values"
                    >


                        <button
                            type="button"

                            class="
                                aarti-donation-value
                                active
                            "

                            data-amount="101"
                        >

                            ₹101

                        </button>


                        <button
                            type="button"

                            class="aarti-donation-value"

                            data-amount="251"
                        >

                            ₹251

                        </button>


                        <button
                            type="button"

                            class="aarti-donation-value"

                            data-amount="501"
                        >

                            ₹501

                        </button>


                        <button
                            type="button"

                            class="aarti-donation-value"

                            data-amount="custom"
                        >

                            Custom

                        </button>


                    </div>



                    <div
                        id="customAmountBox"

                        class="aarti-custom-amount"
                    >


                        <input
                            type="number"

                            id="customAmount"

                            min="1"

                            max="100000"

                            placeholder="Enter amount"
                        >


                    </div>



                    <button
                        type="button"

                        id="donateButton"

                        class="
                            aarti-primary-btn
                            aarti-donate-btn
                        "
                    >

                        🙏 Offer Dakshina

                    </button>



                    <div
                        id="donationMessage"

                        class="
                            aarti-donation-message
                        "
                    ></div>



                @else


                    <a
                        href="{{ route('login') }}"

                        class="
                            aarti-primary-btn
                            aarti-login-link
                        "
                    >

                        Login to Offer Dakshina

                    </a>


                @endauth


            </aside>


        </section>



        <div
            class="aarti-session-note"
        >

            This is a devotional
            Aarti Darshan experience.
            Video is prerecorded and
            is not a real-time temple stream.

        </div>


    </main>


</div>

@endsection



@push('scripts')


@auth

<script
    src="https://checkout.razorpay.com/v1/checkout.js"
></script>

@endauth


<script
    src="{{ asset('js/aarti-session.js') }}"
></script>


@endpush