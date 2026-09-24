@php
    $existingPanditReview = $booking->reviews?->firstWhere('review_by', 'pandit');
    $panditReviewActor = auth('pandit')->user();
    $panditReviewActorId = (int) ($panditReviewActor?->id ?? 0);

    $panditReviewSentKey = 'review_otp_sent_pandit_'.$panditReviewActorId.'_'.$bookingType.'_'.$booking->id;
    $panditReviewVerifiedKey = 'review_otp_verified_pandit_'.$panditReviewActorId.'_'.$bookingType.'_'.$booking->id;

    $panditReviewPendingOtpId = session($panditReviewSentKey);
    $panditReviewVerifiedOtpId = session($panditReviewVerifiedKey);

    $panditReviewOtpPending = $panditReviewPendingOtpId
        ? \App\Models\ReviewImageOtp::whereKey($panditReviewPendingOtpId)
            ->where('user_type', 'pandit')
            ->where('user_id', $panditReviewActorId)
            ->where('purpose', 'pandit_review_submission')
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->exists()
        : false;

    $panditReviewOtpVerified = $panditReviewVerifiedOtpId
        ? \App\Models\ReviewImageOtp::whereKey($panditReviewVerifiedOtpId)
            ->where('user_type', 'pandit')
            ->where('user_id', $panditReviewActorId)
            ->where('purpose', 'pandit_review_submission')
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->exists()
        : false;

    $panditReviewFeedbackTarget = $bookingType.':'.$booking->id;
    $showPanditReviewFeedback = session('review_feedback_target') === $panditReviewFeedbackTarget;
    $reviewSubjectName = $booking->sankalp?->full_name ?: $booking->user?->name ?: 'Yajman';
@endphp

@if($booking->status === 'completed')
<section class="pandit-review-card-shell" data-pandit-review-card>
    <div class="pandit-review-head">
        <div>
            <span class="pandit-review-kicker">Completed {{ ucfirst($bookingType) }}</span>
            <h2>Review Yajman</h2>
            <p>Share your experience with {{ $reviewSubjectName }} after the completed session.</p>
        </div>
        <span class="pandit-review-head-icon"><i class="bi bi-star"></i></span>
    </div>

    @if($showPanditReviewFeedback && session('review_success'))
        <div class="pandit-review-alert success">
            <i class="bi bi-check-circle"></i>
            <span>{{ session('review_success') }}</span>
        </div>
    @endif

    @if($showPanditReviewFeedback && session('review_otp_preview'))
        <div class="pandit-review-dev-otp">
            <i class="bi bi-code-slash"></i>
            <span>Dev OTP</span>
            <strong>{{ session('review_otp_preview') }}</strong>
        </div>
    @endif

    @if($existingPanditReview)
        <div class="pandit-review-complete-badge">
            <i class="bi bi-check-circle-fill"></i>
            Review submitted
        </div>

        <div class="pandit-review-existing-grid">
            <div class="pandit-review-existing-item">
                <small>Your Rating</small>
                <strong class="pandit-review-stars" aria-label="{{ (int) $existingPanditReview->rating }} out of 5 stars">
                    {{ str_repeat('★', (int) $existingPanditReview->rating) }}{{ str_repeat('☆', max(0, 5 - (int) $existingPanditReview->rating)) }}
                </strong>
            </div>

            @if($existingPanditReview->image_path)
                <div class="pandit-review-existing-item">
                    <small>Review Image</small>
                    <strong>
                        <a href="{{ asset('storage/'.$existingPanditReview->image_path) }}" target="_blank" rel="noopener">
                            <i class="bi bi-image"></i> View image
                        </a>
                    </strong>
                </div>
            @endif

            <div class="pandit-review-existing-item wide">
                <small>Your Review</small>
                <strong>{{ $existingPanditReview->comment ?: 'No written comment added.' }}</strong>
            </div>
        </div>
    @elseif(!$panditReviewOtpVerified)
        <div class="pandit-review-steps" aria-label="Review verification steps">
            <div class="pandit-review-step active">
                <span>1</span>
                <div><small>Step 1</small><strong>Verify Email</strong></div>
            </div>
            <div class="pandit-review-step-line"></div>
            <div class="pandit-review-step">
                <span>2</span>
                <div><small>Step 2</small><strong>Write Review</strong></div>
            </div>
        </div>

        <div class="pandit-review-verification-box">
            <div class="pandit-review-verification-copy">
                <span class="pandit-review-mini-icon"><i class="bi bi-shield-check"></i></span>
                <div>
                    <strong>Email Verification</strong>
                    @if($panditReviewOtpPending)
                        <p>OTP sent to {{ $panditReviewActor?->email }}. Enter the 6-digit code below.</p>
                    @else
                        <p>Verify your email first. The review form will unlock after OTP verification.</p>
                    @endif
                </div>
            </div>

            @if($showPanditReviewFeedback && $errors->has('otp'))
                <div class="pandit-review-inline-error">
                    <i class="bi bi-exclamation-circle"></i> {{ $errors->first('otp') }}
                </div>
            @endif

            @if($showPanditReviewFeedback && $errors->has('review_verification'))
                <div class="pandit-review-inline-error">
                    <i class="bi bi-exclamation-circle"></i> {{ $errors->first('review_verification') }}
                </div>
            @endif

            @if(!$panditReviewOtpPending)
                <form method="POST" action="{{ $sendOtpRoute }}" class="pandit-review-send-form">
                    @csrf
                    <input type="hidden" name="booking_type" value="{{ $bookingType }}">
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                    <button class="pandit-review-primary-btn" type="submit">
                        <i class="bi bi-envelope-check"></i>
                        Send OTP
                    </button>
                </form>
            @else
                <form method="POST" action="{{ $verifyOtpRoute }}" class="pandit-review-otp-form">
                    @csrf
                    <input type="hidden" name="booking_type" value="{{ $bookingType }}">
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                    <div class="pandit-review-field pandit-review-otp-field">
                        <label for="pandit-review-otp-{{ $bookingType }}-{{ $booking->id }}">Enter 6-digit OTP</label>
                        <input
                            id="pandit-review-otp-{{ $bookingType }}-{{ $booking->id }}"
                            type="text"
                            name="otp"
                            class="pandit-review-control"
                            placeholder="Enter OTP"
                            maxlength="6"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            value="{{ old('otp') }}"
                            required
                        >
                    </div>

                    <button class="pandit-review-primary-btn" type="submit">
                        <i class="bi bi-shield-check"></i>
                        Verify OTP
                    </button>
                </form>

                <form method="POST" action="{{ $sendOtpRoute }}" class="pandit-review-resend-form">
                    @csrf
                    <input type="hidden" name="booking_type" value="{{ $bookingType }}">
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <button type="submit">Didn't get the code? <strong>Resend OTP</strong></button>
                </form>
            @endif
        </div>
    @else
        <div class="pandit-review-unlocked">
            <span><i class="bi bi-check-circle-fill"></i></span>
            <div>
                <strong>Email verified</strong>
                <small>Review form unlocked</small>
            </div>
        </div>

        <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" class="pandit-review-submit-form">
            @csrf
            <input type="hidden" name="booking_type" value="{{ $bookingType }}">
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

            @if($showPanditReviewFeedback && $errors->has('review'))
                <div class="pandit-review-inline-error wide">
                    <i class="bi bi-exclamation-circle"></i> {{ $errors->first('review') }}
                </div>
            @endif

            @if($showPanditReviewFeedback && $errors->has('review_verification'))
                <div class="pandit-review-inline-error wide">
                    <i class="bi bi-exclamation-circle"></i> {{ $errors->first('review_verification') }}
                </div>
            @endif

            <div class="pandit-review-field">
                <label for="pandit-review-rating-{{ $bookingType }}-{{ $booking->id }}">Rating</label>
                <select
                    id="pandit-review-rating-{{ $bookingType }}-{{ $booking->id }}"
                    name="rating"
                    class="pandit-review-control"
                    required
                >
                    <option value="">Select Rating</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected((string) old('rating') === (string) $i)>
                            {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                        </option>
                    @endfor
                </select>
                @error('rating')
                    <small class="pandit-review-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="pandit-review-field">
                <label for="pandit-review-image-{{ $bookingType }}-{{ $booking->id }}">Review Image <span>Optional</span></label>
                <input
                    id="pandit-review-image-{{ $bookingType }}-{{ $booking->id }}"
                    type="file"
                    name="image"
                    class="pandit-review-control pandit-review-file-control"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >
                @error('image')
                    <small class="pandit-review-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="pandit-review-field wide">
                <label for="pandit-review-comment-{{ $bookingType }}-{{ $booking->id }}">Your Review</label>
                <textarea
                    id="pandit-review-comment-{{ $bookingType }}-{{ $booking->id }}"
                    name="comment"
                    rows="4"
                    class="pandit-review-control"
                    placeholder="Write your experience with the Yajman..."
                >{{ old('comment') }}</textarea>
                @error('comment')
                    <small class="pandit-review-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="pandit-review-submit-row wide">
                <button class="pandit-review-primary-btn submit" type="submit">
                    <i class="bi bi-send"></i>
                    Submit Review
                </button>
            </div>
        </form>
    @endif
</section>

@once
    @push('styles')
        <style>
            .pandit-review-card-shell {
                min-width: 0;
                margin-top: 20px;
                padding: 24px;
                border: 1px solid rgba(199, 141, 34, .22);
                border-radius: 20px;
                background: rgba(255, 255, 255, .68);
                box-shadow: 0 20px 60px -54px rgba(63, 36, 23, .7);
                box-sizing: border-box;
            }

            .pandit-bottom-grid > .pandit-review-card-shell {
                margin-top: 0;
            }

            .pandit-review-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 18px;
            }

            .pandit-review-kicker {
                display: block;
                margin-bottom: 5px;
                color: var(--gold);
                font-size: 10px;
                font-weight: 900;
                letter-spacing: .12em;
                text-transform: uppercase;
            }

            .pandit-review-head h2 {
                margin: 0;
                color: var(--cream);
                font-family: "Cinzel", serif;
                font-size: 24px;
                line-height: 1.2;
            }

            .pandit-review-head p {
                margin: 7px 0 0;
                color: var(--muted);
                font-size: 13px;
                line-height: 1.5;
            }

            .pandit-review-head-icon {
                width: 46px;
                height: 46px;
                display: grid;
                place-items: center;
                flex: 0 0 46px;
                border-radius: 14px;
                background: linear-gradient(135deg, var(--gold), var(--saffron));
                color: #fff;
                font-size: 18px;
            }

            .pandit-review-alert,
            .pandit-review-inline-error,
            .pandit-review-dev-otp {
                display: flex;
                align-items: flex-start;
                gap: 8px;
                margin-bottom: 14px;
                padding: 10px 12px;
                border-radius: 12px;
                font-size: 12px;
                line-height: 1.45;
            }

            .pandit-review-alert.success {
                border: 1px solid rgba(47, 182, 109, .22);
                background: rgba(47, 182, 109, .09);
                color: #237a48;
            }

            .pandit-review-inline-error {
                border: 1px solid rgba(232, 91, 33, .24);
                background: rgba(232, 91, 33, .08);
                color: #9b4c14;
            }

            .pandit-review-dev-otp {
                align-items: center;
                border: 1px dashed rgba(199, 141, 34, .42);
                background: rgba(251, 244, 223, .62);
                color: var(--muted);
            }

            .pandit-review-dev-otp strong {
                color: var(--saffron-dark);
                letter-spacing: .08em;
            }

            .pandit-review-steps {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 40px minmax(0, 1fr);
                align-items: center;
                gap: 8px;
                margin-bottom: 16px;
            }

            .pandit-review-step {
                display: flex;
                align-items: center;
                gap: 9px;
                min-width: 0;
                color: var(--muted);
            }

            .pandit-review-step > span {
                width: 32px;
                height: 32px;
                flex: 0 0 32px;
                display: grid;
                place-items: center;
                border: 1px solid rgba(199, 141, 34, .24);
                border-radius: 50%;
                background: rgba(251, 244, 223, .58);
                font-size: 11px;
                font-weight: 900;
            }

            .pandit-review-step small,
            .pandit-review-step strong {
                display: block;
            }

            .pandit-review-step small {
                margin-bottom: 1px;
                font-size: 9px;
                letter-spacing: .08em;
                text-transform: uppercase;
            }

            .pandit-review-step strong {
                color: var(--cream);
                font-size: 12px;
                white-space: nowrap;
            }

            .pandit-review-step.active > span {
                border-color: transparent;
                background: linear-gradient(135deg, var(--gold), var(--saffron));
                color: #fff;
            }

            .pandit-review-step-line {
                height: 1px;
                background: rgba(199, 141, 34, .24);
            }

            .pandit-review-verification-box {
                padding: 18px;
                border: 1px solid rgba(199, 141, 34, .18);
                border-radius: 16px;
                background: rgba(251, 244, 223, .50);
            }

            .pandit-review-verification-copy {
                display: flex;
                align-items: flex-start;
                gap: 11px;
                margin-bottom: 16px;
            }

            .pandit-review-mini-icon {
                width: 38px;
                height: 38px;
                display: grid;
                place-items: center;
                flex: 0 0 38px;
                border-radius: 12px;
                background: rgba(199, 141, 34, .14);
                color: var(--gold);
            }

            .pandit-review-verification-copy strong {
                display: block;
                color: var(--cream);
                font-size: 14px;
            }

            .pandit-review-verification-copy p {
                margin: 4px 0 0;
                color: var(--muted);
                font-size: 12px;
                line-height: 1.5;
                overflow-wrap: anywhere;
            }

            .pandit-review-send-form {
                margin: 0;
            }

            .pandit-review-otp-form {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: end;
                gap: 10px;
            }

            .pandit-review-field {
                min-width: 0;
            }

            .pandit-review-field.wide,
            .pandit-review-inline-error.wide,
            .pandit-review-submit-row.wide {
                grid-column: 1 / -1;
            }

            .pandit-review-field label {
                display: block;
                margin-bottom: 6px;
                color: var(--muted);
                font-size: 11px;
                font-weight: 800;
            }

            .pandit-review-field label span {
                font-weight: 600;
                opacity: .8;
            }

            .pandit-review-control {
                width: 100%;
                min-width: 0;
                min-height: 44px;
                box-sizing: border-box;
                padding: 10px 12px;
                border: 1px solid rgba(199, 141, 34, .24);
                border-radius: 12px;
                background: rgba(255, 255, 255, .72);
                color: var(--cream);
                font: inherit;
                font-size: 13px;
                outline: none;
            }

            .pandit-review-control:focus {
                border-color: rgba(232, 91, 33, .55);
                box-shadow: 0 0 0 3px rgba(232, 91, 33, .09);
            }

            textarea.pandit-review-control {
                min-height: 104px;
                resize: vertical;
            }

            .pandit-review-file-control {
                padding: 7px 9px;
            }

            .pandit-review-primary-btn {
                min-height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                padding: 10px 16px;
                border: 0;
                border-radius: 12px;
                background: linear-gradient(135deg, var(--saffron), var(--saffron-dark));
                color: #fff;
                font-size: 12px;
                font-weight: 800;
                cursor: pointer;
                box-shadow: 0 12px 26px -18px rgba(232, 91, 33, .9);
            }

            .pandit-review-primary-btn.submit {
                width: 100%;
            }

            .pandit-review-resend-form {
                margin-top: 10px;
                text-align: center;
            }

            .pandit-review-resend-form button {
                border: 0;
                background: transparent;
                color: var(--muted);
                font-size: 11px;
                cursor: pointer;
            }

            .pandit-review-resend-form strong {
                color: var(--saffron-dark);
            }

            .pandit-review-unlocked,
            .pandit-review-complete-badge {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 16px;
                padding: 11px 13px;
                border: 1px solid rgba(47, 182, 109, .20);
                border-radius: 13px;
                background: rgba(47, 182, 109, .08);
                color: #237a48;
            }

            .pandit-review-unlocked > span {
                font-size: 18px;
            }

            .pandit-review-unlocked strong,
            .pandit-review-unlocked small {
                display: block;
            }

            .pandit-review-unlocked strong {
                font-size: 12px;
            }

            .pandit-review-unlocked small {
                margin-top: 2px;
                font-size: 10px;
            }

            .pandit-review-submit-form {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 13px;
            }

            .pandit-review-field-error {
                display: block;
                margin-top: 5px;
                color: var(--saffron-dark);
                font-size: 10px;
            }

            .pandit-review-existing-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }

            .pandit-review-existing-item {
                min-width: 0;
                padding: 14px;
                border: 1px solid rgba(199, 141, 34, .18);
                border-radius: 14px;
                background: rgba(251, 244, 223, .55);
            }

            .pandit-review-existing-item.wide {
                grid-column: 1 / -1;
            }

            .pandit-review-existing-item small,
            .pandit-review-existing-item strong {
                display: block;
            }

            .pandit-review-existing-item small {
                margin-bottom: 6px;
                color: var(--muted);
                font-size: 11px;
                font-weight: 800;
            }

            .pandit-review-existing-item strong {
                color: var(--cream);
                font-size: 13px;
                line-height: 1.5;
                overflow-wrap: anywhere;
            }

            .pandit-review-existing-item a {
                color: var(--saffron-dark);
            }

            .pandit-review-stars {
                color: var(--gold) !important;
                letter-spacing: .08em;
                font-size: 17px !important;
            }

            @media (max-width: 767.98px) {
                .pandit-review-card-shell {
                    margin-top: 14px;
                    padding: 18px;
                    border-radius: 18px;
                }

                .pandit-review-head h2 {
                    font-size: 21px;
                }

                .pandit-review-head-icon {
                    width: 40px;
                    height: 40px;
                    flex-basis: 40px;
                    border-radius: 12px;
                }

                .pandit-review-steps {
                    grid-template-columns: minmax(0, 1fr) 22px minmax(0, 1fr);
                    gap: 5px;
                }

                .pandit-review-step {
                    gap: 6px;
                }

                .pandit-review-step > span {
                    width: 28px;
                    height: 28px;
                    flex-basis: 28px;
                }

                .pandit-review-step strong {
                    font-size: 10px;
                }

                .pandit-review-verification-box {
                    padding: 14px;
                }

                .pandit-review-otp-form {
                    grid-template-columns: 1fr;
                }

                .pandit-review-otp-form .pandit-review-primary-btn {
                    width: 100%;
                }

                .pandit-review-submit-form,
                .pandit-review-existing-grid {
                    grid-template-columns: 1fr;
                }

                .pandit-review-existing-item.wide,
                .pandit-review-field.wide,
                .pandit-review-submit-row.wide,
                .pandit-review-inline-error.wide {
                    grid-column: auto;
                }
            }

            @media (max-width: 360px) {
                .pandit-review-card-shell {
                    padding: 15px;
                }

                .pandit-review-head {
                    gap: 10px;
                }

                .pandit-review-head p {
                    font-size: 12px;
                }

                .pandit-review-step > div {
                    min-width: 0;
                }

                .pandit-review-step strong {
                    white-space: normal;
                }
            }
        </style>
    @endpush
@endonce
@endif
