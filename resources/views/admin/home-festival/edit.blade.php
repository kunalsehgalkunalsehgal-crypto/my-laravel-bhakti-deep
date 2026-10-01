@extends('admin.layout')

@section('title', 'Homepage Festival')

@section('content')

<h1>Homepage Festival</h1>

<div class="panel" style="max-width:720px">

    <form
        action="{{ route('admin.home-festival.update') }}"
        method="POST"
        enctype="multipart/form-data"
    >

        @csrf


        {{-- First Heading --}}

        <label>
            Heading - First Line (Gold)
        </label>

        <input
            type="text"
            name="first_line"
            maxlength="36"
            required
            value="{{ old('first_line', $lines[0] ?? 'Guru Purnima') }}"
        >


        {{-- Second Heading --}}

        <label style="margin-top:16px">
            Heading - Second Line (Dark)
        </label>

        <input
            type="text"
            name="second_line"
            maxlength="36"
            required
            value="{{ old('second_line', $lines[1] ?? 'Mahotsav') }}"
        >


        {{-- Subheading --}}

        <label style="margin-top:16px">
            Subheading
        </label>

        <textarea
            name="subheading"
            maxlength="200"
            required
        >{{ old('subheading', $settings->get('home_festival_subheading') ?: 'Celebrate with personalized pooja, live aarti and sacred diya lighting from home.') }}</textarea>


        {{-- Festival Image --}}

        <label style="margin-top:16px">
            Festival Image
        </label>

        <img
            src="{{ $settings->get('home_festival_image') ? asset('storage/'.$settings->get('home_festival_image')) : asset('assets/pornima.jpeg') }}"
            alt="Current Festival Image"
            style="
                display:block;
                max-width:100%;
                max-height:230px;
                margin:12px 0;
                border-radius:12px;
            "
        >

        <input
            type="file"
            name="image"
            accept="image/jpeg,image/png,image/webp"
        >


        {{-- Save Button --}}

        <button
            type="submit"
            class="btn primary"
            style="margin-top:20px"
        >
            Save Changes
        </button>

    </form>

</div>

@endsection