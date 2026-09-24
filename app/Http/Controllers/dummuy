<?php

namespace App\Http\Controllers;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\Review;
use App\Models\ReviewImageOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ReviewController extends Controller
{
    public function sendImageOtp(Request $request)
    {
        [$booking] = $this->booking($request);
        $actor = $this->actor();
        $otp = (string) random_int(100000, 999999);

        ReviewImageOtp::create([
            'user_type' => $actor['type'],
            'user_id' => $actor['id'],
            'email' => $actor['email'],
            'otp' => Hash::make($otp),
            'purpose' => 'review_image_upload',
            'expires_at' => now()->addMinutes(5),
        ]);

        Mail::send('emails.otp', [
            'greetingName' => $actor['name'],
            'otp' => $otp,
            'subject' => 'Review image upload OTP',
            'context' => 'review_image_upload',
        ], fn ($message) => $message->to($actor['email'])->subject('Review image upload OTP'));

        return back()->with('success', 'Image upload OTP sent to your email.')
            ->with('review_otp_preview', app()->environment('local') || config('mail.default') === 'log' ? $otp : null);
    }

    public function verifyImageOtp(Request $request)
    {
        $request->validate(['otp' => ['required', 'digits:6']]);
        $actor = $this->actor();
        $otp = ReviewImageOtp::where($this->otpWhere($actor))->whereNull('verified_at')->latest()->first();

        if (!$otp || $otp->expires_at->isPast() || !Hash::check($request->otp, $otp->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired image OTP.']);
        }

        $otp->update(['verified_at' => now()]);

        return back()->with('success', 'Image upload verified. You can submit your review with image now.');
    }

    public function store(Request $request)
    {
        [$booking, $bookingType] = $this->booking($request);
        $actor = $this->actor();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $otp = ReviewImageOtp::where($this->otpWhere($actor))->whereNotNull('verified_at')->where('expires_at', '>', now())->latest()->first();
            if (!$otp) {
                return back()->withErrors(['image' => 'Please verify email OTP before uploading review image.'])->withInput();
            }
            $data['image_path'] = $request->file('image')->store('reviews', 'public');
            $otp->update(['expires_at' => now()]);
        }

        if (Review::where('booking_type', $bookingType)->where('booking_id', $booking->id)->where('review_by', $actor['type'])->exists()) {
            return back()->withErrors(['review' => 'Review already submitted for this booking.']);
        }

        Review::create($data + [
            'booking_type' => $bookingType,
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'pandit_id' => $booking->pandit_id,
            'review_by' => $actor['type'],
        ]);

        return back()->with('success', 'Review submitted successfully.');
    }

    private function booking(Request $request): array
    {
        $data = $request->validate([
            'booking_type' => ['required', 'in:hawan,pooja'],
            'booking_id' => ['required', 'integer'],
        ]);

        $booking = ($data['booking_type'] === 'hawan' ? HawanSession::class : PoojaSession::class)::with('reviews')->findOrFail($data['booking_id']);
        $actor = $this->actor();

        abort_unless($booking->status === 'completed', 403);
        abort_unless($actor['type'] === 'user' ? (int) $booking->user_id === $actor['id'] : (int) $booking->pandit_id === $actor['id'], 403);

        return [$booking, $data['booking_type']];
    }

    private function actor(): array
    {
        $pandit = auth('pandit')->user();
        $user = $pandit ?: auth()->user();

        abort_unless($user && $user->email, 403);

        return [
            'type' => $pandit ? 'pandit' : 'user',
            'id' => (int) $user->id,
            'email' => $user->email,
            'name' => $user->pandit_name ?? $user->name ?? $user->full_name ?? 'Devotee',
        ];
    }

    private function otpWhere(array $actor): array
    {
        return [
            'user_type' => $actor['type'],
            'user_id' => $actor['id'],
            'email' => $actor['email'],
            'purpose' => 'review_image_upload',
        ];
    }
}
