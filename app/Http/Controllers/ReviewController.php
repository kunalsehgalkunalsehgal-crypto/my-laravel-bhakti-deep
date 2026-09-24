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
    private const OTP_PURPOSE = 'review_submission';

    public function sendImageOtp(Request $request)
    {
        [$booking, $bookingType] = $this->booking($request);
        $actor = $this->actor();
        $otp = (string) random_int(100000, 999999);

        // Expire any older unverified OTP for this actor so only the latest code works.
        ReviewImageOtp::where($this->otpWhere($actor))
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        $otpRecord = ReviewImageOtp::create([
            'user_type' => $actor['type'],
            'user_id' => $actor['id'],
            'email' => $actor['email'],
            'otp' => Hash::make($otp),
            'purpose' => self::OTP_PURPOSE,
            'expires_at' => now()->addMinutes(5),
        ]);

        $request->session()->put(
            $this->sentSessionKey($actor, $bookingType, (int) $booking->id),
            $otpRecord->id
        );
        $request->session()->forget(
            $this->verifiedSessionKey($actor, $bookingType, (int) $booking->id)
        );

        Mail::send('emails.otp', [
            'greetingName' => $actor['name'],
            'otp' => $otp,
            'subject' => 'BhaktiDeep review verification OTP',
            'context' => self::OTP_PURPOSE,
        ], fn ($message) => $message->to($actor['email'])->subject('BhaktiDeep review verification OTP'));

        return back()
            ->with('review_feedback_target', $this->feedbackTarget($bookingType, (int) $booking->id))
            ->with('review_success', 'OTP sent to your email. Enter the 6-digit code to unlock the review form.')
            ->with('review_otp_preview', app()->environment('local') || config('mail.default') === 'log' ? $otp : null);
    }

    public function verifyImageOtp(Request $request)
    {
        $request->validate([
            'booking_type' => ['required', 'in:hawan,pooja'],
            'booking_id' => ['required', 'integer'],
            'otp' => ['required', 'digits:6'],
        ]);

        [$booking, $bookingType] = $this->booking($request);
        $actor = $this->actor();

        $sentKey = $this->sentSessionKey($actor, $bookingType, (int) $booking->id);
        $sentOtpId = $request->session()->get($sentKey);

        $otp = $sentOtpId
            ? ReviewImageOtp::whereKey($sentOtpId)
                ->where($this->otpWhere($actor))
                ->whereNull('verified_at')
                ->first()
            : null;

        if (!$otp || $otp->expires_at->isPast() || !Hash::check($request->otp, $otp->otp)) {
            return back()
                ->withErrors(['otp' => 'Invalid or expired OTP. Please send a new code and try again.'])
                ->withInput();
        }

        $otp->update(['verified_at' => now()]);

        $request->session()->forget($sentKey);
        $request->session()->put(
            $this->verifiedSessionKey($actor, $bookingType, (int) $booking->id),
            $otp->id
        );

        return back()
            ->with('review_feedback_target', $this->feedbackTarget($bookingType, (int) $booking->id))
            ->with('review_success', 'Email verified. You can now submit your review.');
    }

    public function store(Request $request)
    {
        [$booking, $bookingType] = $this->booking($request);
        $actor = $this->actor();

        if (Review::where('booking_type', $bookingType)
            ->where('booking_id', $booking->id)
            ->where('review_by', $actor['type'])
            ->exists()) {
            return back()->withErrors(['review' => 'Review already submitted for this booking.']);
        }

        $verifiedKey = $this->verifiedSessionKey($actor, $bookingType, (int) $booking->id);
        $verifiedOtpId = $request->session()->get($verifiedKey);

        $verifiedOtp = $verifiedOtpId
            ? ReviewImageOtp::whereKey($verifiedOtpId)
                ->where($this->otpWhere($actor))
                ->whereNotNull('verified_at')
                ->where('expires_at', '>', now())
                ->first()
            : null;

        if (!$verifiedOtp) {
            $request->session()->forget($verifiedKey);

            return back()
                ->withErrors(['review_verification' => 'Please verify the email OTP before submitting your review.'])
                ->withInput();
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('reviews', 'public');
        }

        Review::create($data + [
            'booking_type' => $bookingType,
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'pandit_id' => $booking->pandit_id,
            'review_by' => $actor['type'],
        ]);

        // A verified OTP can unlock only one review submission.
        $verifiedOtp->update(['expires_at' => now()]);
        $request->session()->forget($verifiedKey);
        $request->session()->forget(
            $this->sentSessionKey($actor, $bookingType, (int) $booking->id)
        );

        return back()
            ->with('review_feedback_target', $this->feedbackTarget($bookingType, (int) $booking->id))
            ->with('review_success', 'Review submitted successfully.');
    }

    private function booking(Request $request): array
    {
        $data = $request->validate([
            'booking_type' => ['required', 'in:hawan,pooja'],
            'booking_id' => ['required', 'integer'],
        ]);

        $booking = ($data['booking_type'] === 'hawan' ? HawanSession::class : PoojaSession::class)
            ::with('reviews')
            ->findOrFail($data['booking_id']);

        $actor = $this->actor();

        abort_unless($booking->status === 'completed', 403);
        abort_unless(
            $actor['type'] === 'user'
                ? (int) $booking->user_id === $actor['id']
                : (int) $booking->pandit_id === $actor['id'],
            403
        );

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
            'purpose' => self::OTP_PURPOSE,
        ];
    }

    private function feedbackTarget(string $bookingType, int $bookingId): string
    {
        return $bookingType.':'.$bookingId;
    }

    private function sentSessionKey(array $actor, string $bookingType, int $bookingId): string
    {
        return 'review_otp_sent_'.$actor['type'].'_'.$actor['id'].'_'.$bookingType.'_'.$bookingId;
    }

    private function verifiedSessionKey(array $actor, string $bookingType, int $bookingId): string
    {
        return 'review_otp_verified_'.$actor['type'].'_'.$actor['id'].'_'.$bookingType.'_'.$bookingId;
    }
}
