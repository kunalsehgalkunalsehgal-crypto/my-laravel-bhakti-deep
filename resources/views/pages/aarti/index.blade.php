@extends('layouts.app')

@section('title', 'Aarti Darshan - BhaktiDeep')

@section('description', 'Choose your deity and experience divine Aarti Darshan.')

@push('styles')

<style>

.aarti-list-page {
    padding-top: 60px;
    padding-bottom: 70px;
}

.aarti-list-title {
    text-align: center;
    margin-bottom: 40px;
}

.aarti-card {
    height: 100%;
    overflow: hidden;
    border-radius: 20px;
    border: 1px solid rgba(199, 141, 34, .22);
    background: #ffffff;
    box-shadow: 0 8px 25px rgba(0,0,0,.08);
}

.aarti-card-image {
    height: 230px;
    width: 100%;
    object-fit: contain;
}

.aarti-card-body {
    padding: 20px;
    text-align: center;
}

.aarti-card-body h3 {
    font-size: 21px;
    margin-bottom: 8px;
}

.aarti-card-body p {
    color: #777;
    min-height: 45px;
}

</style>

@endpush


@section('body')

<main class="aarti-list-page">

    <div class="container">

        <div class="aarti-list-title">

            <h1>
                🪔 Aarti Darshan
            </h1>

            <p class="text-muted">
                Apne isht devta ko chuniye aur Aarti Darshan shuru kijiye.
            </p>

        </div>


        <div class="row g-4">

            @forelse($deities as $deity)

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


                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">

                    <div class="aarti-card">

                        <img
                            src="{{ $imageUrl }}"
                            class="aarti-card-image"
                            alt="{{ $deity->name }}"
                        >


                        <div class="aarti-card-body">

                            <h3>
                                {{ $deity->name }}
                            </h3>


                            <p>

                                {{
                                    $deity->short_description
                                    ?: 'Divya Aarti Darshan'
                                }}

                            </p>


                            <a
href="{{ route('aarti.session', $deity->slug) }}"                                class="btn btn-warning w-100"
                            >
                                🪔 Aarti Darshan
                            </a>

                        </div>

                    </div>

                </div>


            @empty

                <div class="col-12">

                    <div class="text-center py-5">

                        <h4>
                            Abhi koi Aarti available nahi hai.
                        </h4>

                    </div>

                </div>

            @endforelse

        </div>

    </div>

</main>

@endsection