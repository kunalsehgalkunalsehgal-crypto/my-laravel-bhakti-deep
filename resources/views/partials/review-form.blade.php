@php
    $existingReview = $booking->reviews?->firstWhere('review_by', $reviewBy);
@endphp

@if($booking->status === 'completed')
    <div class="booking-details">
        @if($existingReview)
            <div><small>{{ $title }}</small><b>{{ str_repeat('★', (int) $existingReview->rating) }} {{ $existingReview->comment ?: '' }}</b></div>
            @if($existingReview->image_path)
                <div><small>Review Image</small><b><a href="{{ asset('storage/'.$existingReview->image_path) }}" target="_blank">View image</a></b></div>
            @endif
        @else
            <form method="POST" action="{{ $sendOtpRoute }}" class="booking-actions">
                @csrf
                <input type="hidden" name="booking_type" value="{{ $bookingType }}">
                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                <button class="btn btn-outline-saffron btn-sm rounded-pill" type="submit">Send Image OTP</button>
            </form>
            <form method="POST" action="{{ $verifyOtpRoute }}" class="booking-actions">
                @csrf
                <input class="form-control sacred-input" style="max-width:160px" name="otp" placeholder="Image OTP">
                <button class="btn btn-outline-saffron btn-sm rounded-pill" type="submit">Verify OTP</button>
            </form>
            <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" class="booking-details">
                @csrf
                <input type="hidden" name="booking_type" value="{{ $bookingType }}">
                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                <div><small>{{ $title }}</small><b>
                    <select name="rating" required>
                        <option value="">Rating</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }} Star</option>
                        @endfor
                    </select>
                </b></div>
                <textarea name="comment" rows="2" placeholder="Write comment" style="width:100%"></textarea>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                <button class="btn btn-saffron btn-sm rounded-pill" type="submit">Submit Review</button>
            </form>
        @endif
    </div>
@endif
