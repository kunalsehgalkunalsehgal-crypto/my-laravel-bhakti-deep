@extends('layouts.app')

@section('title', $deity->name.' Aarti - BhaktiDeep')

@section('description', 'Experience '.$deity->name.' Aarti Darshan on BhaktiDeep.')


@push('styles')

<style>

.aarti-page {
    padding-top: 45px;
    padding-bottom: 70px;
}

.aarti-wrapper {
    max-width: 1000px;
    margin: auto;
}

.aarti-heading {
    text-align: center;
    margin-bottom: 25px;
}

.aarti-video-box {
    background: #000;
    overflow: hidden;
    border-radius: 24px;
    box-shadow: 0 15px 40px rgba(0,0,0,.18);
}

.aarti-video-box video {
    display: block;
    width: 100%;
    height: 520px;
    object-fit: cover;
}

.aarti-audio-box {
    text-align: center;
    margin-top: 30px;
}

.aarti-start-btn {
    padding: 14px 30px;
    font-size: 17px;
    border-radius: 50px;
}

.donation-box {
    margin-top: 35px;
    padding: 28px;
    background: #fff;
    border-radius: 20px;
    border: 1px solid rgba(199,141,34,.22);
    box-shadow: 0 8px 25px rgba(0,0,0,.06);
    text-align: center;
}

.donation-options {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
}

.donation-btn.active {
    background: #f39c12;
    border-color: #f39c12;
    color: white;
}

#customAmountBox {
    max-width: 260px;
    margin: 15px auto 0;
}

@media(max-width: 768px) {

    .aarti-video-box video {
        height: 350px;
    }

    .donation-box {
        padding: 20px;
    }
}

</style>

@endpush



@section('body')

@php

    $imageUrl =
        asset('assets/temple-hero.jpg');

    if ($deity->featured_image) {

        $imageUrl =
            str_starts_with(
                $deity->featured_image,
                'assets/'
            )
            ? asset(
                $deity->featured_image
            )
            : asset(
                'storage/'.
                $deity->featured_image
            );
    }

@endphp


<main class="aarti-page">

<div class="container">

<div class="aarti-wrapper">


    {{-- BACK --}}

    <div class="mb-3">

        <a
            href="{{ route('aarti.index') }}"
            class="btn btn-outline-secondary"
        >
            ← All Aartis
        </a>

    </div>



    {{-- HEADING --}}

    <div class="aarti-heading">

        <h1>
            {{ $deity->name }}
        </h1>

        <p class="text-muted">
            🪔 Divya Aarti Darshan
        </p>

    </div>



    {{-- AUTO PLAY VIDEO --}}

    <div class="aarti-video-box">

        <video
            id="aartiVideo"
            autoplay
            muted
            loop
            playsinline
            preload="metadata"
            poster="{{ $imageUrl }}"
        >

            <source
                src="{{ asset('storage/'.$deity->aarti_video) }}"
            >

            Your browser does not support video.

        </video>

    </div>



    {{-- AARTI AUDIO --}}

    <div class="aarti-audio-box">

        @if(
            $deity->aartiAudio
            &&
            $deity->aartiAudio->status === 'active'
            &&
            $deity->aartiAudio->audio_file
        )

            <h4>
                {{ $deity->aartiAudio->title }}
            </h4>


            <p class="text-muted">
                Aarti shuru karne ke liye button dabaiye.
            </p>


            <audio
                id="aartiAudio"
                preload="metadata"
            >

                <source
                    src="{{ $deity->aartiAudio->fileUrl() }}"
                >

            </audio>


            <button
                type="button"
                id="startAartiBtn"
                class="btn btn-warning aarti-start-btn"
            >
                🪔 Aarti Shuru Kare
            </button>


        @else

            <div class="alert alert-warning">

                Is Aarti ka audio abhi available nahi hai.

            </div>

        @endif

    </div>



    {{-- QUICK DONATION --}}

    <div class="donation-box">

        <h3>
            🙏 Quick Donation
        </h3>

        <p class="text-muted">
            Apni ichha se Dakshina arpit karein.
        </p>


        @auth


            <div class="donation-options">

                <button
                    type="button"
                    class="btn btn-outline-warning donation-btn active"
                    data-amount="101"
                >
                    ₹101
                </button>


                <button
                    type="button"
                    class="btn btn-outline-warning donation-btn"
                    data-amount="251"
                >
                    ₹251
                </button>


                <button
                    type="button"
                    class="btn btn-outline-warning donation-btn"
                    data-amount="501"
                >
                    ₹501
                </button>


                <button
                    type="button"
                    class="btn btn-outline-warning donation-btn"
                    data-amount="custom"
                >
                    Custom
                </button>

            </div>



            <div
                id="customAmountBox"
                style="display:none;"
            >

                <input
                    type="number"
                    id="customAmount"
                    class="form-control"
                    placeholder="Enter amount"
                    min="1"
                    max="100000"
                >

            </div>



            <button
                type="button"
                id="donateBtn"
                class="btn btn-success mt-4 px-4"
            >
                🙏 Offer Dakshina
            </button>



            <div
                id="donationMessage"
                class="mt-3"
            ></div>


        @else


            <p class="mb-3">
                Dakshina offer karne ke liye login karein.
            </p>


            <a
                href="{{ route('login') }}"
                class="btn btn-warning"
            >
                Login
            </a>


        @endauth

    </div>


</div>

</div>

</main>

@endsection



@push('scripts')


@auth

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

@endauth


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /*
        |--------------------------------------------------------------------------
        | AARTI AUDIO
        |--------------------------------------------------------------------------
        */

        const audio =
            document.getElementById(
                'aartiAudio'
            );

        const startButton =
            document.getElementById(
                'startAartiBtn'
            );


        if (audio && startButton) {

            startButton.addEventListener(
                'click',
                async function () {

                    try {

                        if (audio.paused) {

                            await audio.play();

                            startButton.innerHTML =
                                '⏸ Pause Aarti';

                        } else {

                            audio.pause();

                            startButton.innerHTML =
                                '🪔 Aarti Shuru Kare';

                        }

                    } catch (error) {

                        alert(
                            'Audio start nahi ho paya. Please dobara try karein.'
                        );
                    }
                }
            );


            audio.addEventListener(
                'ended',
                function () {

                    audio.currentTime = 0;

                    startButton.innerHTML =
                        '🪔 Aarti Shuru Kare';
                }
            );

        }



        /*
        |--------------------------------------------------------------------------
        | DONATION
        |--------------------------------------------------------------------------
        */

        let selectedAmount = 101;


        const donationButtons =
            document.querySelectorAll(
                '.donation-btn'
            );


        const customAmountBox =
            document.getElementById(
                'customAmountBox'
            );


        donationButtons.forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        donationButtons.forEach(
                            function (btn) {

                                btn.classList.remove(
                                    'active'
                                );
                            }
                        );


                        button.classList.add(
                            'active'
                        );


                        if (
                            button.dataset.amount
                            ===
                            'custom'
                        ) {

                            selectedAmount = null;

                            customAmountBox.style.display =
                                'block';

                        } else {

                            selectedAmount =
                                parseInt(
                                    button.dataset.amount
                                );

                            customAmountBox.style.display =
                                'none';
                        }
                    }
                );
            }
        );



        const donateButton =
            document.getElementById(
                'donateBtn'
            );


        const donationMessage =
            document.getElementById(
                'donationMessage'
            );


        if (donateButton) {

            donateButton.addEventListener(
                'click',
                async function () {

                    let amount =
                        selectedAmount;


                    if (!amount) {

                        const customInput =
                            document.getElementById(
                                'customAmount'
                            );


                        amount =
                            parseInt(
                                customInput.value
                            );
                    }


                    if (
                        !amount
                        ||
                        amount < 1
                        ||
                        amount > 100000
                    ) {

                        alert(
                            'Please valid amount enter karein.'
                        );

                        return;
                    }


                    donateButton.disabled =
                        true;


                    donationMessage.innerHTML =
                        'Payment start ho raha hai...';


                    try {


                        const response =
                            await fetch(
                                "{{ route('aarti.donation.create', $deity) }}",
                                {
                                    method:
                                        'POST',

                                    headers: {

                                        'Content-Type':
                                            'application/json',

                                        'Accept':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            document
                                                .querySelector(
                                                    'meta[name="csrf-token"]'
                                                )
                                                .content,
                                    },

                                    body:
                                        JSON.stringify({
                                            amount: amount
                                        }),
                                }
                            );


                        const result =
                            await response.json();


                        if (
                            !response.ok
                            ||
                            !result.success
                        ) {

                            throw new Error(
                                result.message
                                ||
                                'Payment start nahi hua.'
                            );
                        }


                        if (
                            typeof Razorpay
                            ===
                            'undefined'
                        ) {

                            throw new Error(
                                'Razorpay payment window load nahi hui.'
                            );
                        }


                        openRazorpay(
                            result.payment
                        );


                    } catch (error) {

                        donationMessage.innerHTML =
                            '<span class="text-danger">'
                            +
                            error.message
                            +
                            '</span>';


                        donateButton.disabled =
                            false;
                    }

                }
            );
        }



        /*
        |--------------------------------------------------------------------------
        | RAZORPAY
        |--------------------------------------------------------------------------
        */

        function openRazorpay(payment) {


            const options = {

                key:
                    payment.key,

                amount:
                    payment.amount,

                currency:
                    payment.currency,

                name:
                    'BhaktiDeep',

                description:
                    payment.description
                    ||
                    'Aarti Dakshina',

                order_id:
                    payment.order_id,

                prefill:
                    payment.prefill,


                handler:
                    async function (
                        razorpayResponse
                    ) {

                        try {


                            const verifyResponse =
                                await fetch(
                                    payment.verify_url,
                                    {
                                        method:
                                            'POST',

                                        headers: {

                                            'Content-Type':
                                                'application/json',

                                            'Accept':
                                                'application/json',

                                            'X-CSRF-TOKEN':
                                                document
                                                    .querySelector(
                                                        'meta[name="csrf-token"]'
                                                    )
                                                    .content,
                                        },

                                        body:
                                            JSON.stringify({

                                                payment_attempt_id:
                                                    payment.attempt_id,

                                                razorpay_order_id:
                                                    razorpayResponse
                                                        .razorpay_order_id,

                                                razorpay_payment_id:
                                                    razorpayResponse
                                                        .razorpay_payment_id,

                                                razorpay_signature:
                                                    razorpayResponse
                                                        .razorpay_signature,
                                            }),
                                    }
                                );


                            const verifyResult =
                                await verifyResponse.json();


                            if (
                                !verifyResponse.ok
                                ||
                                !verifyResult.success
                            ) {

                                throw new Error(
                                    verifyResult.message
                                    ||
                                    'Payment verification failed.'
                                );
                            }


                            donationMessage.innerHTML =
                                '<span class="text-success fw-bold">'
                                +
                                '🙏 Dakshina successfully offered.'
                                +
                                '</span>';


                        } catch (error) {


                            donationMessage.innerHTML =
                                '<span class="text-danger">'
                                +
                                error.message
                                +
                                '</span>';
                        }


                        donateButton.disabled =
                            false;
                    },

                modal: {

                    ondismiss:
                        function () {

                            donateButton.disabled =
                                false;

                            donationMessage.innerHTML =
                                '';
                        }
                }
            };


            const razorpay =
                new Razorpay(options);


            razorpay.on(
                'payment.failed',
                async function (response) {

                    try {

                        await fetch(
                            payment.failure_url,
                            {
                                method:
                                    'POST',

                                headers: {

                                    'Content-Type':
                                        'application/json',

                                    'Accept':
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        document
                                            .querySelector(
                                                'meta[name="csrf-token"]'
                                            )
                                            .content,
                                },

                                body:
                                    JSON.stringify({

                                        payment_attempt_id:
                                            payment.attempt_id,

                                        razorpay_order_id:
                                            payment.order_id,

                                        error:
                                            response.error
                                            || {},
                                    }),
                            }
                        );

                    } catch (error) {
                        console.error(error);
                    }


                    donationMessage.innerHTML =
                        '<span class="text-danger">'
                        +
                        'Payment failed. Please try again.'
                        +
                        '</span>';


                    donateButton.disabled =
                        false;
                }
            );


            razorpay.open();
        }


    }
);

</script>

@endpush