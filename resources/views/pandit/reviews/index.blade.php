@extends('layouts.pandit-dashboard')

@section('title', 'Reviews - BhaktiDeep')

@php
    $activeMenu = 'reviews';
    $maxDistribution = max(1, (int) $distribution->max());
@endphp

@section('content')
<style>
.pandit-reviews-page {
    width: 100%;
}

.pandit-reviews-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.pandit-review-stat {
    min-width: 0;
    border: 1px solid rgba(199, 141, 34, .22);
    border-radius: 20px;
    background: rgba(255, 255, 255, .68);
    box-shadow: 0 20px 60px -54px rgba(63, 36, 23, .7);
    padding: 20px;
}

.pandit-review-stat i {
    width: 44px;
    height: 44px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--gold), var(--saffron));
    color: #fff;
    font-size: 18px;
}

.pandit-review-stat span {
    display: block;
    margin-top: 14px;
    color: var(--muted);
    font-size: 12px;
    font-weight: 800;
}

.pandit-review-stat strong {
    display: block;
    margin-top: 4px;
    color: var(--cream);
    font-family: "Cinzel", serif;
    font-size: 27px;
    line-height: 1.2;
}

.pandit-reviews-tools {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 20px;
    margin-bottom: 20px;
}

.pandit-review-filter-panel,
.pandit-rating-breakdown {
    margin-top: 0;
}

.pandit-review-filter-form {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
    gap: 12px;
    align-items: end;
}

.pandit-review-filter-form label {
    display: grid;
    gap: 7px;
    color: #6f5546;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.pandit-review-filter-form select {
    width: 100%;
    min-height: 44px;
    border: 1px solid rgba(199, 141, 34, .26);
    border-radius: 13px;
    background: rgba(251, 244, 223, .72);
    color: var(--cream);
    font: inherit;
    font-size: 13px;
    font-weight: 700;
    padding: 9px 12px;
    outline: none;
}

.pandit-review-filter-form select:focus {
    border-color: var(--saffron);
    box-shadow: 0 0 0 3px rgba(232, 91, 33, .08);
}

.pandit-review-filter-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pandit-review-filter-actions button,
.pandit-review-filter-actions a {
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border-radius: 999px;
    padding: 0 15px;
    font-size: 12px;
    font-weight: 900;
    text-decoration: none;
    white-space: nowrap;
}

.pandit-review-filter-actions button {
    border: 0;
    background: linear-gradient(135deg, var(--saffron), var(--saffron-dark));
    color: #fff;
}

.pandit-review-filter-actions a {
    border: 1px solid rgba(232, 91, 33, .28);
    background: #fff;
    color: #9b4c14;
}

.pandit-rating-list {
    display: grid;
    gap: 10px;
}

.pandit-rating-row {
    display: grid;
    grid-template-columns: 54px minmax(0, 1fr) 28px;
    align-items: center;
    gap: 9px;
    color: #6f5546;
    font-size: 12px;
    font-weight: 800;
}

.pandit-rating-row b {
    color: #d88613;
}

.pandit-rating-track {
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: rgba(199, 141, 34, .12);
}

.pandit-rating-track span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, var(--gold), var(--saffron));
}

.pandit-reviews-list {
    display: grid;
    gap: 14px;
}

.pandit-review-card {
    border: 1px solid rgba(199, 141, 34, .20);
    border-radius: 18px;
    background: rgba(255, 255, 255, .70);
    padding: 18px;
    box-shadow: 0 18px 50px -46px rgba(63, 36, 23, .72);
}

.pandit-review-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.pandit-review-identity {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.pandit-review-avatar {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: rgba(232, 91, 33, .10);
    color: var(--saffron-dark);
    font-weight: 900;
}

.pandit-review-identity strong,
.pandit-review-service strong {
    display: block;
    color: var(--cream);
}

.pandit-review-identity small,
.pandit-review-service small,
.pandit-review-date {
    display: block;
    margin-top: 3px;
    color: var(--muted);
    font-size: 11px;
}

.pandit-review-stars {
    flex: 0 0 auto;
    color: #d88613;
    letter-spacing: 2px;
    font-size: 15px;
    white-space: nowrap;
}

.pandit-review-meta {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(0, .8fr) auto;
    gap: 14px;
    align-items: center;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid rgba(199, 141, 34, .14);
}

.pandit-review-type {
    width: fit-content;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    background: rgba(199, 141, 34, .14);
    color: #9b4c14;
    font-size: 10px;
    font-weight: 900;
    padding: 5px 9px;
    text-transform: uppercase;
}

.pandit-review-comment {
    margin: 14px 0 0;
    color: #5f4639;
    font-size: 13px;
    line-height: 1.7;
}

.pandit-review-image {
    display: block;
    width: 92px;
    height: 72px;
    margin-top: 14px;
    overflow: hidden;
    border: 1px solid rgba(199, 141, 34, .22);
    border-radius: 12px;
    background: #fff9ea;
}

.pandit-review-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.pandit-review-booking-link {
    min-height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: 1px solid rgba(232, 91, 33, .28);
    border-radius: 999px;
    background: #fff;
    color: #9b4c14;
    font-size: 11px;
    font-weight: 900;
    padding: 0 12px;
    text-decoration: none;
    white-space: nowrap;
}

.pandit-review-empty {
    min-height: 260px;
    display: grid;
    place-items: center;
    text-align: center;
    border: 1px dashed rgba(199, 141, 34, .30);
    border-radius: 20px;
    background: rgba(255, 255, 255, .54);
    padding: 30px;
}

.pandit-review-empty i {
    font-size: 34px;
    color: var(--gold);
}

.pandit-review-empty h3 {
    margin: 10px 0 6px;
    color: var(--cream);
    font-family: "Cinzel", serif;
}

.pandit-review-empty p {
    margin: 0;
    color: var(--muted);
}

@media (max-width: 1100px) {
    .pandit-reviews-summary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .pandit-reviews-tools {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767px) {
    .pandit-review-filter-form {
        grid-template-columns: 1fr 1fr;
    }

    .pandit-review-filter-actions {
        grid-column: 1 / -1;
    }

    .pandit-review-meta {
        grid-template-columns: 1fr 1fr;
    }

    .pandit-review-meta > :last-child {
        grid-column: 1 / -1;
    }

    .pandit-review-booking-link {
        justify-self: start;
    }
}

@media (max-width: 520px) {
    .pandit-reviews-summary,
    .pandit-review-filter-form,
    .pandit-review-meta {
        grid-template-columns: 1fr;
    }

    .pandit-review-filter-actions {
        grid-column: auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .pandit-review-card-top {
        flex-direction: column;
    }

    .pandit-review-stars {
        margin-left: 56px;
    }
}
</style>

<div class="pandit-reviews-page">
    <div class="pandit-page-heading">
        <div>
            <p>Yajman Feedback</p>
            <h1>Reviews</h1>
        </div>

        <a href="{{ route('pandit.dashboard') }}" class="pandit-add-btn">
            <i class="bi bi-grid-1x2-fill"></i>
            Dashboard
        </a>
    </div>

    <section class="pandit-reviews-summary">
        <article class="pandit-review-stat">
            <i class="bi bi-star-fill"></i>
            <span>Average Rating</span>
            <strong>{{ number_format($summary['average'], 1) }} / 5</strong>
        </article>

        <article class="pandit-review-stat">
            <i class="bi bi-chat-heart"></i>
            <span>Total Reviews</span>
            <strong>{{ number_format($summary['total']) }}</strong>
        </article>

        <article class="pandit-review-stat">
            <i class="bi bi-stars"></i>
            <span>5 Star Reviews</span>
            <strong>{{ number_format($summary['five_star']) }}</strong>
        </article>

        <article class="pandit-review-stat">
            <i class="bi bi-journal-check"></i>
            <span>Pooja / Hawan</span>
            <strong>{{ $summary['pooja'] }} / {{ $summary['hawan'] }}</strong>
        </article>
    </section>

    <section class="pandit-reviews-tools">
        <div class="pandit-panel pandit-review-filter-panel">
            <div class="pandit-panel-heading">
                <div>
                    <h2>Filter Reviews</h2>
                    <p>Filter the feedback received from your Yajmans.</p>
                </div>
                <span><i class="bi bi-funnel"></i></span>
            </div>

            <form method="GET" action="{{ route('pandit.reviews.index') }}" class="pandit-review-filter-form">
                <label>
                    Rating
                    <select name="rating">
                        <option value="">All Ratings</option>
                        @for($star = 5; $star >= 1; $star--)
                            <option value="{{ $star }}" @selected((int) $filters['rating'] === $star)>
                                {{ $star }} Star{{ $star > 1 ? 's' : '' }}
                            </option>
                        @endfor
                    </select>
                </label>

                <label>
                    Service
                    <select name="service_type">
                        <option value="all" @selected($filters['service_type'] === 'all')>All Services</option>
                        <option value="pooja" @selected($filters['service_type'] === 'pooja')>Pooja</option>
                        <option value="hawan" @selected($filters['service_type'] === 'hawan')>Hawan</option>
                    </select>
                </label>

                <label>
                    Per Page
                    <select name="per_page">
                        <option value="10" @selected((int) $filters['per_page'] === 10)>10</option>
                        <option value="15" @selected((int) $filters['per_page'] === 15)>15</option>
                    </select>
                </label>

                <div class="pandit-review-filter-actions">
                    <button type="submit">
                        <i class="bi bi-search"></i>
                        Apply
                    </button>
                    <a href="{{ route('pandit.reviews.index') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="pandit-panel pandit-rating-breakdown">
            <div class="pandit-panel-heading">
                <div>
                    <h2>Rating Breakdown</h2>
                    <p>All received reviews</p>
                </div>
                <span><i class="bi bi-bar-chart"></i></span>
            </div>

            <div class="pandit-rating-list">
                @foreach($distribution as $star => $count)
                    <div class="pandit-rating-row">
                        <b>{{ $star }} ★</b>
                        <div class="pandit-rating-track">
                            <span style="width: {{ $summary['total'] > 0 ? round(($count / $summary['total']) * 100, 2) : 0 }}%"></span>
                        </div>
                        <span>{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pandit-panel">
        <div class="pandit-panel-heading">
            <div>
                <h2>Received Reviews</h2>
                <p>
                    @if($reviews->total())
                        Showing {{ $reviews->firstItem() }}-{{ $reviews->lastItem() }} of {{ $reviews->total() }} reviews
                    @else
                        No reviews found for the selected filters
                    @endif
                </p>
            </div>
            <span><i class="bi bi-chat-heart"></i></span>
        </div>

        @if($reviews->isNotEmpty())
            <div class="pandit-reviews-list">
                @foreach($reviews as $review)
                    <article class="pandit-review-card">
                        <div class="pandit-review-card-top">
                            <div class="pandit-review-identity">
                                <span class="pandit-review-avatar">
                                    {{ strtoupper(mb_substr($review['yajman'], 0, 1)) }}
                                </span>
                                <div>
                                    <strong>{{ $review['yajman'] }}</strong>
                                    <small>{{ $review['booking_id'] }}</small>
                                </div>
                            </div>

                            <div class="pandit-review-stars" aria-label="{{ $review['rating'] }} out of 5 stars">
                                {{ str_repeat('★', $review['rating']) }}{{ str_repeat('☆', max(0, 5 - $review['rating'])) }}
                            </div>
                        </div>

                        @if($review['comment'])
                            <p class="pandit-review-comment">{{ $review['comment'] }}</p>
                        @else
                            <p class="pandit-review-comment">No written comment was added with this rating.</p>
                        @endif

                        @if($review['image_path'])
                            <a
                                href="{{ asset('storage/'.$review['image_path']) }}"
                                target="_blank"
                                rel="noopener"
                                class="pandit-review-image"
                                aria-label="Open review image"
                            >
                                <img src="{{ asset('storage/'.$review['image_path']) }}" alt="Review image from {{ $review['yajman'] }}">
                            </a>
                        @endif

                        <div class="pandit-review-meta">
                            <div class="pandit-review-service">
                                <span class="pandit-review-type">{{ ucfirst($review['type']) }}</span>
                                <strong>{{ $review['service_name'] }}</strong>
                                @if($review['booking_date'])
                                    <small>Booking: {{ $review['booking_date']->format('d M Y') }}</small>
                                @endif
                            </div>

                            <div>
                                <strong class="pandit-review-date">
                                    Reviewed {{ $review['reviewed_at']?->format('d M Y, h:i A') ?? '—' }}
                                </strong>
                            </div>

                            <div>
                                @if($review['detail_url'])
                                    <a href="{{ $review['detail_url'] }}" class="pandit-review-booking-link">
                                        View Booking
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($reviews->hasPages())
                <div class="pandit-pagination">
                    @if($reviews->onFirstPage())
                        <span><i class="bi bi-chevron-left"></i> Previous</span>
                    @else
                        <a href="{{ $reviews->previousPageUrl() }}"><i class="bi bi-chevron-left"></i> Previous</a>
                    @endif

                    <strong>Page {{ $reviews->currentPage() }} of {{ $reviews->lastPage() }}</strong>

                    @if($reviews->hasMorePages())
                        <a href="{{ $reviews->nextPageUrl() }}">Next <i class="bi bi-chevron-right"></i></a>
                    @else
                        <span>Next <i class="bi bi-chevron-right"></i></span>
                    @endif
                </div>
            @endif
        @else
            <div class="pandit-review-empty">
                <div>
                    <i class="bi bi-chat-heart"></i>
                    <h3>No reviews yet</h3>
                    <p>Reviews submitted by Yajmans after completed Pooja or Hawan bookings will appear here.</p>
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
