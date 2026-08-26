@extends('layouts.app')

@section('title', 'BhaktiDeep Blogs - Spiritual Guides & Devotional Stories')
@section('description', 'Read spiritual guides, pooja information, diya articles, hawan guides, sankalp meaning, live aarti updates, and devotional tips on BhaktiDeep.')

@push('styles')
    <link href="{{ asset('css/how-it-works.css') }}" rel="stylesheet">
    <link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
    <link href="{{ asset('css/blogs.css') }}" rel="stylesheet">
@endpush

@section('body')

@php
    $activeCategoryName = isset($activeCategory) ? $activeCategory->name : 'All';
@endphp

<main class="page-shell blogs-page">
    <!-- Background Pattern -->
    <div class="blog-pattern-bg">
        <svg viewBox="0 0 100 100" class="diya-pattern">
            <g opacity="0.03">
                <path d="M50 10 Q60 20 50 30 Q40 20 50 10" fill="currentColor"/>
                <circle cx="50" cy="30" r="3" fill="currentColor"/>
            </g>
        </svg>
    </div>

    <!-- HERO SECTION -->
    <section class="page-hero blog-hero">
        <div class="container page-hero-content">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <span class="eyebrow"><i class="bi bi-book-fill"></i> BhaktiDeep Wisdom</span>
                    <h1 class="mt-4">Read Spiritual Guides & Devotional Stories</h1>
                    <p class="mt-4">Explore simple guides on pooja, hawan, diya, sankalp, live aarti and spiritual practices to deepen your devotion and understanding of sacred rituals.</p>
                    <div class="hero-buttons mt-5">
                        <a class="btn btn-saffron btn-lg" href="#blog-grid"><i class="bi bi-arrow-down-circle"></i> Explore Blogs</a>
                        <a class="btn btn-ghost-gold btn-lg" href="{{ route('personalized-pooja') }}"><i class="bi bi-heart-fill"></i> Start Bhakti Journey</a>
                    </div>
                </div>
                <div class="col-lg-6">
                <div class="ld-hero-img-wrap">
                    <div class="ld-img-glow"></div>
                    <div class="ld-img-card glass">
                        <img src="{{ asset('assets/diya.jpg') }}" alt="Glowing diya">
                        <div class="ld-img-overlay"></div>
                        <div class="ld-img-badge">
                            <div class="d-flex align-items-center gap-2">
                                <span class="ld-pulse-dot"></span>
                                <span>Live diya • animated flame</span>
                            </div>
                            <span class="ld-akhand-tag">Akhand</span>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </section>

    <!-- CATEGORY FILTER TABS -->
    <section class="page-section blog-filter-section">
        <div class="container">
            <h2 class="text-center ">Explore by Category</h2>
            <div class="category-filter">
                <a href="{{ route('blogs') }}" class="category-pill {{ $activeCategoryName === 'All' ? 'active' : '' }}">All</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('blogs.category', $cat->slug) }}" class="category-pill {{ $activeCategoryName === $cat->name ? 'active' : '' }}">{{ $cat->name }}</a>
                @endforeach
            </div>
        </div>
    

    <!-- BLOG GRID SECTION -->
        <div class="container">
            <div class="blog-grid">
                @forelse ($blogs as $blog)
                    <article class="blog-card">
                        <div class="blog-card-image">
                            <img src="{{ $blog->featured_image ? asset('storage/'.$blog->featured_image) : asset('assets/temple-hero.jpg') }}" alt="{{ $blog->image_alt ?: $blog->title }}" class="img-fluid">
                            <div class="blog-card-overlay"></div>
                            <span class="blog-category-badge">{{ $blog->category?->name ?? '' }}</span>
                        </div>
                        <div class="blog-card-content">
                            <h3 class="blog-card-title">{{ $blog->title }}</h3>
                            <p class="blog-card-description">{{ $blog->excerpt }}</p>
                            <div class="blog-card-footer">
                                <div class="blog-meta">
                                    <span class="blogs-page"><i class="bi bi-calendar3" style="color: white"></i> {{ $blog->published_at?->format('M d, Y') }}</span>
                                </div>
                                <a href="{{ route('blogs.show', $blog->slug) }}" class="read-more-link"><i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-center" style="color:var(--cream-dark)">No blogs found in this category.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="container page-section">
        <div class="hiw-final-cta">
            <div>
                <h2>Begin Your Spiritual Journey With <span class="gold-text">BhaktiDeep</span></h2>
                <p>Read guides, join live prayers, and book sacred rituals with ease.</p>
            </div>
            <div class="cta-buttons mt-5">
                    <a class="btn btn-light btn-lg" href="{{ route('personalized-pooja') }}"><i class="bi bi-heart-fill"></i> Start Bhakti Journey</a>
                    <a class="btn btn-outline-light btn-lg" href="{{ route('live.sessions') }}"><i class="bi bi-bell-fill"></i> Join Live Aarti</a>
                </div>
        </div>
    </section>
</main>

@endsection

@push('scripts')
@endpush
