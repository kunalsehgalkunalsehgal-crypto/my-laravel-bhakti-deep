@php
    $existingReview = $booking->reviews?->firstWhere('review_by', $reviewBy);

    $reviewActor = auth('pandit')->user() ?: auth()->user();
    $reviewActorType = auth('pandit')->check() ? 'pandit' : 'user';
    $reviewActorId = (int) ($reviewActor?->id ?? 0);

    $reviewOtpSentKey = 'review_otp_sent_'.$reviewActorType.'_'.$reviewActorId.'_'.$bookingType.'_'.$booking->id;
    $reviewOtpVerifiedKey = 'review_otp_verified_'.$reviewActorType.'_'.$reviewActorId.'_'.$bookingType.'_'.$booking->id;

    $reviewPendingOtpId = session($reviewOtpSentKey);
    $reviewVerifiedOtpId = session($reviewOtpVerifiedKey);

    $reviewOtpPending = $reviewPendingOtpId
        ? \App\Models\ReviewImageOtp::whereKey($reviewPendingOtpId)
            ->where('user_type', $reviewActorType)
            ->where('user_id', $reviewActorId)
            ->where('purpose', 'review_submission')
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->exists()
        : false;

    $reviewOtpVerified = $reviewVerifiedOtpId
        ? \App\Models\ReviewImageOtp::whereKey($reviewVerifiedOtpId)
            ->where('user_type', $reviewActorType)
            ->where('user_id', $reviewActorId)
            ->where('purpose', 'review_submission')
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->exists()
        : false;

    $reviewServiceLabel = ucfirst($bookingType);
    $reviewFeedbackTarget = $bookingType.':'.$booking->id;
    $showReviewFeedback = session('review_feedback_target') === $reviewFeedbackTarget;
@endphp

@if($booking->status === 'completed')
    <div class="review-panel-content">
        <div class="review-panel-heading">
            <h3>
                <i class="bi bi-star"></i>
                {{ $title }}
            </h3>
            <p>Share your experience after completing the {{ $reviewServiceLabel }}.</p>
        </div>

        @if($showReviewFeedback && session('review_success'))
            <div class="review-flow-alert review-flow-alert-success">
                <i class="bi bi-check-circle"></i>
                <span>{{ session('review_success') }}</span>
            </div>
        @endif

        @error('review')
            <div class="review-flow-alert review-flow-alert-error">
                <i class="bi bi-exclamation-circle"></i>
                <span>{{ $message }}</span>
            </div>
        @enderror

        @error('review_verification')
            <div class="review-flow-alert review-flow-alert-error">
                <i class="bi bi-exclamation-circle"></i>
                <span>{{ $message }}</span>
            </div>
        @enderror

        @if($existingReview)
            <div class="booking-details review-existing">
                <div>
                    <small>Rating & Review</small>
                    <b>
                        {{ str_repeat('★', (int) $existingReview->rating) }}
                        @if($existingReview->comment)
                            <span class="review-comment">{{ $existingReview->comment }}</span>
                        @endif
                    </b>
                </div>

                @if($existingReview->image_path)
                    <div>
                        <small>Review Image</small>
                        <b>
                            <a href="{{ asset('storage/'.$existingReview->image_path) }}" target="_blank">
                                View Image
                            </a>
                        </b>
                    </div>
                @endif
            </div>
        @elseif(!$reviewOtpVerified)
            {{-- STEP 1: VERIFY EMAIL OTP. Review fields remain locked until verification succeeds. --}}
            <div class="review-flow-steps" aria-label="Review steps">
                <div class="review-flow-step active">
                    <span>1</span>
                    <div>
                        <small>Step 1</small>
                        <strong>Verify Email</strong>
                    </div>
                </div>
                <div class="review-flow-line"></div>
                <div class="review-flow-step">
                    <span>2</span>
                    <div>
                        <small>Step 2</small>
                        <strong>Write Review</strong>
                    </div>
                </div>
            </div>

            <div class="review-verification review-verification-gate">
                <div class="review-field-heading">
                    <small>Email Verification</small>
                    @if($reviewOtpPending)
                        <p>We sent a 6-digit OTP to your email. Enter it below to unlock the review form.</p>
                    @else
                        <p>Verify your email first. Your rating and review fields will open after OTP verification.</p>
                    @endif
                </div>

                @if($showReviewFeedback && session('review_otp_preview'))
                    <div class="review-dev-otp">
                        <i class="bi bi-code-slash"></i>
                        Dev OTP: <strong>{{ session('review_otp_preview') }}</strong>
                    </div>
                @endif

                @if(!$reviewOtpPending)
                    <form method="POST" action="{{ $sendOtpRoute }}" class="review-action-form">
                        @csrf
                        <input type="hidden" name="booking_type" value="{{ $bookingType }}">
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                        <button class="btn btn-saffron review-primary-action" type="submit">
                            <i class="bi bi-envelope-check"></i>
                            Send OTP
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ $verifyOtpRoute }}" class="review-otp-form">
                        @csrf
                        <input type="hidden" name="booking_type" value="{{ $bookingType }}">
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                        <div class="review-input-group">
                            <label for="review-otp-{{ $bookingType }}-{{ $booking->id }}">Enter 6-digit OTP</label>
                            <input
                                id="review-otp-{{ $bookingType }}-{{ $booking->id }}"
                                type="text"
                                name="otp"
                                class="form-control sacred-input"
                                placeholder="Enter OTP"
                                maxlength="6"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                value="{{ old('otp') }}"
                                required
                            >
                            @error('otp')
                                <small class="review-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-saffron review-verify-action" type="submit">
                            <i class="bi bi-shield-check"></i>
                            Verify OTP
                        </button>
                    </form>

                    <form method="POST" action="{{ $sendOtpRoute }}" class="review-resend-form">
                        @csrf
                        <input type="hidden" name="booking_type" value="{{ $bookingType }}">
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                        <button type="submit" class="review-resend-button">
                            Didn't get the code? <strong>Resend OTP</strong>
                        </button>
                    </form>
                @endif
            </div>
        @else
            {{-- STEP 2: OTP VERIFIED. Verification UI is hidden and only the review form is shown. --}}
            <div class="review-flow-steps review-flow-steps-unlocked" aria-label="Review steps">
                <div class="review-flow-step completed">
                    <span><i class="bi bi-check-lg"></i></span>
                    <div>
                        <small>Step 1</small>
                        <strong>Verified</strong>
                    </div>
                </div>
                <div class="review-flow-line completed"></div>
                <div class="review-flow-step active">
                    <span>2</span>
                    <div>
                        <small>Step 2</small>
                        <strong>Write Review</strong>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" class="review-submit-form">
                @csrf
                <input type="hidden" name="booking_type" value="{{ $bookingType }}">
                <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                <div class="review-form-field">
                    <label for="review-rating-{{ $bookingType }}-{{ $booking->id }}">Rating</label>
                    <select
                        id="review-rating-{{ $bookingType }}-{{ $booking->id }}"
                        name="rating"
                        class="form-control sacred-input"
                        required
                    >
                        <option value="">Select Rating</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" @selected((string) old('rating') === (string) $i)>
                                {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                            </option>
                        @endfor
                    </select>
                    @error('rating')
                        <small class="review-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="review-form-field">
                    <label for="review-image-{{ $bookingType }}-{{ $booking->id }}">Review Image <span class="review-optional">Optional</span></label>
                    <input
                        id="review-image-{{ $bookingType }}-{{ $booking->id }}"
                        type="file"
                        name="image"
                        class="form-control sacred-input"
                        accept=".jpg,.jpeg,.png,.webp"
                    >
                    @error('image')
                        <small class="review-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="review-form-field">
                    <label for="review-comment-{{ $bookingType }}-{{ $booking->id }}">Your Review</label>
                    <textarea
                        id="review-comment-{{ $bookingType }}-{{ $booking->id }}"
                        name="comment"
                        rows="4"
                        class="form-control sacred-input"
                        placeholder="Write your experience..."
                    >{{ old('comment') }}</textarea>
                    @error('comment')
                        <small class="review-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="review-submit-button">
                    <button class="btn btn-saffron" type="submit">
                        <i class="bi bi-send"></i>
                        Submit Review
                    </button>
                </div>
            </form>
        @endif
    </div>

    @push('styles')
        <style>
            .review-panel-content {
                width: 100%;
                min-width: 0;
            }

            .review-flow-alert {
                display: flex;
                align-items: flex-start;
                gap: 9px;
                margin: 0 0 14px;
                padding: 11px 12px;
                border-radius: 12px;
                font-size: 12px;
                line-height: 1.45;
            }

            .review-flow-alert-success {
                border: 1px solid rgba(59, 160, 89, .22);
                background: rgba(59, 160, 89, .08);
                color: #487851;
            }

            .review-flow-alert-error {
                border: 1px solid rgba(232, 91, 33, .22);
                background: rgba(232, 91, 33, .08);
                color: #9b4c14;
            }

            .review-flow-steps {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 34px minmax(0, 1fr);
                align-items: center;
                gap: 8px;
                margin: 0 0 16px;
            }

            .review-flow-step {
                display: flex;
                align-items: center;
                gap: 9px;
                min-width: 0;
                color: var(--muted);
            }

            .review-flow-step > span {
                width: 30px;
                height: 30px;
                flex: 0 0 30px;
                display: grid;
                place-items: center;
                border: 1px solid rgba(199, 141, 34, .22);
                border-radius: 50%;
                background: rgba(251, 244, 223, .58);
                color: var(--muted);
                font-size: 11px;
                font-weight: 900;
            }

            .review-flow-step small,
            .review-flow-step strong {
                display: block;
            }

            .review-flow-step small {
                margin-bottom: 1px;
                font-size: 9px;
                line-height: 1.2;
                text-transform: uppercase;
                letter-spacing: .08em;
            }

            .review-flow-step strong {
                color: var(--cream);
                font-size: 11px;
                line-height: 1.2;
                white-space: nowrap;
            }

            .review-flow-step.active > span {
                border-color: transparent;
                background: linear-gradient(135deg, var(--gold), var(--saffron));
                color: #fff;
                box-shadow: 0 8px 20px -14px rgba(232, 91, 33, .8);
            }

            .review-flow-step.completed > span {
                border-color: rgba(59, 160, 89, .25);
                background: rgba(59, 160, 89, .12);
                color: #3b9654;
            }

            .review-flow-line {
                height: 1px;
                background: rgba(199, 141, 34, .20);
            }

            .review-flow-line.completed {
                background: rgba(59, 160, 89, .30);
            }

            .review-verification-gate {
                margin: 0;
                padding: 16px;
                border: 1px solid rgba(199, 141, 34, .18);
                border-radius: 16px;
                background: rgba(251, 244, 223, .46);
            }

            .review-dev-otp {
                display: flex;
                align-items: center;
                gap: 7px;
                margin-top: 10px;
                padding: 8px 10px;
                border-radius: 10px;
                background: rgba(199, 141, 34, .10);
                color: #8a5c24;
                font-size: 11px;
            }

            .review-primary-action {
                width: 100%;
                min-height: 44px;
                border-radius: 12px !important;
                font-weight: 800;
            }

            .review-otp-form {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: end;
                gap: 10px;
                margin-top: 14px;
            }

            .review-verify-action {
                min-height: 46px;
                padding-inline: 16px;
                white-space: nowrap;
            }

            .review-resend-form {
                margin-top: 10px;
                text-align: center;
            }

            .review-resend-button {
                appearance: none;
                border: 0;
                background: transparent;
                color: var(--muted);
                padding: 3px;
                font-size: 11px;
                cursor: pointer;
            }

            .review-resend-button strong {
                color: var(--saffron);
            }

            .review-field-error {
                display: block;
                margin-top: 5px;
                color: #b24a24;
                font-size: 11px;
            }

            .review-optional {
                color: var(--muted);
                font-size: 10px;
                font-weight: 500;
            }

            .review-submit-form {
                display: grid;
                gap: 14px;
                margin-top: 0;
            }

            .review-submit-button .btn {
                width: 100%;
                min-height: 46px;
                border-radius: 12px !important;
                font-size: 14px;
                font-weight: 800;
            }

            @media (max-width: 420px) {
                .review-flow-steps {
                    grid-template-columns: minmax(0, 1fr) 18px minmax(0, 1fr);
                    gap: 5px;
                }

                .review-flow-step {
                    gap: 6px;
                }

                .review-flow-step > span {
                    width: 27px;
                    height: 27px;
                    flex-basis: 27px;
                }

                .review-flow-step strong {
                    font-size: 10px;
                }

                .review-otp-form {
                    grid-template-columns: 1fr;
                    align-items: stretch;
                }

                .review-verify-action {
                    width: 100%;
                }
            }
        </style>
    @endpush
@endif
