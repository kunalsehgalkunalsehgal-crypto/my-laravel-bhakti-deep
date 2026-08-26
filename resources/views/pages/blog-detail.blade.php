@extends('layouts.app')

@section('title', $blog->meta_title ?: $blog->title)
@section('description', $blog->meta_description ?: $blog->excerpt)

@php
    $heroImage = $blog->featured_image ? asset('storage/'.$blog->featured_image) : asset('assets/havan-live.jpg');
    $shareUrl = route('blogs.show', $blog->slug);
    $shareText = $blog->title;
    $readMinutes = max(1, ceil(str_word_count(strip_tags($blog->content ?? '')) / 200));
    $authorName = $blog->author_name ?: $blog->author?->name ?: 'BhaktiDeep Team';
    $categoryName = $blog->category?->name ?: 'Blog';
    $contentHtml = $blog->content ?? '';
    $tocItems = [];

    $contentHtml = preg_replace_callback('/<h([23])([^>]*)>(.*?)<\/h\1>/is', function ($matches) use (&$tocItems) {
        $text = trim(strip_tags($matches[3]));
        if ($text === '') {
            return $matches[0];
        }

        $id = str($text)->slug()->toString();
        $baseId = $id;
        $index = 2;
        while (collect($tocItems)->contains('id', $id)) {
            $id = $baseId.'-'.$index;
            $index++;
        }

        $tocItems[] = [
            'id' => $id,
            'title' => $text,
            'level' => (int) $matches[1],
        ];

        $attrs = preg_replace('/\s+id=(["\']).*?\1/i', '', $matches[2]);
        return '<h'.$matches[1].$attrs.' id="'.$id.'">'.$matches[3].'</h'.$matches[1].'>';
    }, $contentHtml);
@endphp

@push('styles')
    @if($blog->canonical_url)
        <link rel="canonical" href="{{ $blog->canonical_url }}">
    @endif
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="og:title" content="{{ $blog->og_title ?: $blog->title }}">
    <meta property="og:description" content="{{ $blog->og_description ?: $blog->excerpt }}">
    <meta property="og:image" content="{{ $blog->og_image ? asset('storage/'.$blog->og_image) : $heroImage }}">
    @if($blog->schema_markup)
        <script type="application/ld+json">
            {!! json_encode($blog->schema_markup, JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif
    <style>
        :root {
            --bd-cream: #fff7ea;
            --bd-cream-strong: #f7e5c4;
            --bd-maroon: #581208;
            --bd-maroon-dark: #2b0803;
            --bd-gold: #e7a31a;
            --bd-gold-soft: #f6d087;
            --bd-ink: #21140f;
            --bd-muted: #6f4a36;
        }

        .blog-detail-page {
            background:
                radial-gradient(circle at 8% 18%, rgba(231, 163, 26, .14), transparent 28%),
                radial-gradient(circle at 94% 8%, rgba(88, 18, 8, .12), transparent 24%),
                linear-gradient(180deg, #fff5e4 0%, #fffaf1 44%, #f8ead1 100%);
            color: var(--bd-ink);
            font-family: Inter, sans-serif;
        }

        .blog-hero-detail {
            position: relative;
            min-height: 430px;
            display: flex;
            align-items: stretch;
            overflow: hidden;
            background: var(--bd-maroon-dark);
            isolation: isolate;
        }

        .blog-hero-detail::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(43, 8, 3, .98) 0%, rgba(88, 18, 8, .94) 38%, rgba(88, 18, 8, .46) 61%, rgba(43, 8, 3, .08) 100%),
                radial-gradient(circle at 18% 36%, rgba(231, 163, 26, .18), transparent 33%);
            z-index: -1;
        }

        .blog-hero-image {
            position: absolute;
            inset: 0 0 0 auto;
            width: 62%;
            background: center / cover no-repeat;
            opacity: .92;
            z-index: -2;
        }

        .blog-hero-inner {
            width: min(1180px, calc(100% - 36px));
            margin: 0 auto;
            padding: 58px 0 54px;
        }

        .blog-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
            margin-bottom: 28px;
            color: #fff4d9;
            font-size: 14px;
            font-weight: 700;
        }

        .blog-breadcrumb a {
            color: inherit;
            text-decoration: none;
        }

        .hero-copy {
            max-width: 610px;
        }

        .hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 15px;
            border: 1px solid var(--bd-gold);
            border-radius: 999px;
            color: #ffd978;
            background: rgba(43, 8, 3, .64);
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .blog-hero-detail h1 {
            margin: 20px 0 14px;
            font-family: Cinzel, serif;
            font-size: clamp(34px, 5vw, 58px);
            line-height: 1.1;
            color: #fffaf0;
            letter-spacing: 0;
        }

        .hero-excerpt {
            max-width: 580px;
            color: #ffe6bd;
            font-size: 18px;
            line-height: 1.7;
            margin: 0 0 24px;
        }

        .hero-divider {
            width: min(390px, 90%);
            height: 1px;
            background: linear-gradient(90deg, var(--bd-gold), transparent);
            margin: 0 0 20px;
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            color: #fff2d5;
            font-size: 14px;
            font-weight: 700;
        }

        .hero-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .blog-detail-wrap {
            width: min(1180px, calc(100% - 36px));
            margin: 0 auto;
            padding: 30px 0 56px;
        }

        .blog-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 46px;
            align-items: start;
        }

        .article-top-ornament {
            display: flex;
            align-items: center;
            gap: 14px;
            color: var(--bd-gold);
            margin: 8px 0 24px;
        }

        .article-top-ornament::before,
        .article-top-ornament::after {
            content: "";
            height: 1px;
            flex: 1;
            background: linear-gradient(90deg, transparent, var(--bd-gold-soft), transparent);
        }

        .article-top-ornament i {
            font-size: 28px;
        }

        /* .blog-article {
            font-size: 17px;
            line-height: 1.78;
        } */
         .blog-article {
    font-size: 17px;
    line-height: 1.78;
    overflow-wrap: break-word;
    word-wrap: break-word;
    word-break: break-word;
}
.blog-article img,
.blog-article table,
.blog-article iframe,
.blog-article pre,
.blog-article code {
    max-width: 100%;
}

.blog-article img {
    height: auto;
    display: block;
}

.blog-article pre {
    overflow-x: auto;
    white-space: pre-wrap;
    padding: 12px;
    border-radius: 8px;
    background: #f8f8f8;
}

.blog-article table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
}


        .blog-article h2,
        .blog-article h3 {
            color: var(--bd-maroon);
            font-family: Cinzel, serif;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: 0;
            scroll-margin-top: 96px;
        }

        .blog-article h2 {
            margin: 28px 0 11px;
            font-size: 27px;
        }

        .blog-article h3 {
            margin: 22px 0 9px;
            font-size: 21px;
        }

        .blog-article h2::before,
        .blog-article h3::before {
            content: "\F6A7";
            font-family: "bootstrap-icons";
            color: #bf2b17;
            font-size: .72em;
            margin-right: 10px;
        }

        .blog-article p {
            margin: 0 0 14px;
        }

        .blog-article a {
            color: #9a5200;
            font-weight: 800;
        }

        .blog-article ul,
        .blog-article ol {
            margin: 12px 0 18px;
            padding-left: 24px;
        }

        .blog-article li {
            margin: 7px 0;
        }

        .blog-article blockquote {
            margin: 22px 0;
            border: 1px solid var(--bd-gold-soft);
            border-radius: 8px;
            background: linear-gradient(135deg, #fff4db, #fff9ee);
            color: var(--bd-maroon);
            padding: 18px 22px 18px 54px;
            position: relative;
            font-weight: 700;
        }

        .blog-article blockquote::before {
            content: "\F6B0";
            font-family: "bootstrap-icons";
            position: absolute;
            left: 20px;
            top: 16px;
            color: var(--bd-gold);
            font-size: 22px;
        }

        .blog-tags {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: wrap;
            margin: 22px 0 18px;
            font-weight: 800;
            color: var(--bd-maroon);
        }

        .blog-tag {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 5px 13px;
            border-radius: 999px;
            border: 1px solid var(--bd-gold-soft);
            color: #8a4704;
            background: #fff6e7;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .author-box {
            display: flex;
            gap: 16px;
            align-items: center;
            border: 1px solid var(--bd-gold-soft);
            border-radius: 8px;
            background: linear-gradient(135deg, #fff8eb, #fff2dc);
            padding: 16px;
            margin-top: 14px;
            overflow: hidden;
            position: relative;
        }

        .author-box::after {
            content: "";
            position: absolute;
            right: -34px;
            bottom: -42px;
            width: 140px;
            height: 140px;
            border: 1px solid rgba(231, 163, 26, .22);
            border-radius: 50%;
        }

        .author-avatar {
            width: 70px;
            height: 70px;
            flex: 0 0 auto;
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: var(--bd-gold);
            background: radial-gradient(circle at 50% 45%, #7a170d, #360803);
            font-size: 34px;
        }

        .author-box h3 {
            margin: 0 0 4px;
            color: var(--bd-maroon);
            font-family: Cinzel, serif;
            font-size: 19px;
        }

        .author-box p {
            margin: 0;
            color: var(--bd-muted);
            font-size: 14px;
            line-height: 1.55;
        }

        .blog-sidebar {
            position: sticky;
            top: 96px;
            display: grid;
            gap: 16px;
        }

        .side-panel {
            border: 1px solid #ecd1a4;
            border-radius: 8px;
            background: rgba(255, 250, 241, .92);
            box-shadow: 0 10px 26px rgba(88, 18, 8, .10);
            padding: 18px 20px;
        }

        .side-panel h2 {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin: 0 0 14px;
            color: var(--bd-maroon);
            font-family: Cinzel, serif;
            font-size: 19px;
            font-weight: 800;
            text-align: center;
        }

        .side-panel h2::before,
        .side-panel h2::after {
            content: "";
            width: 42px;
            height: 1px;
            background: var(--bd-gold-soft);
        }

        .toc-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 10px;
        }

        .toc-list a {
            display: grid;
            grid-template-columns: 9px 1fr;
            gap: 10px;
            align-items: center;
            color: #3d2418;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .toc-list a::before {
            content: "";
            width: 7px;
            height: 7px;
            background: #9a5200;
            transform: rotate(45deg);
        }

        .share-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            text-align: center;
        }

        .share-link {
            color: #3d2418;
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
        }

        .share-icon {
            width: 43px;
            height: 43px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            margin: 0 auto 7px;
            color: #fff;
            font-size: 20px;
        }

        .share-whatsapp { background: #21c45d; }
        .share-facebook { background: #2f6fe8; }
        .share-twitter { background: #0f1115; }
        .share-email { background: #df4a51; }

        .cta-panel {
            background:
                linear-gradient(90deg, rgba(43, 8, 3, .95), rgba(88, 18, 8, .82)),
                url('{{ asset('assets/lakshmi-hero.jpg') }}') center / cover;
            color: #fff7e6;
            border-color: #6c210f;
        }

        .cta-panel h2 {
            justify-content: flex-start;
            text-align: left;
            color: #fff7e6;
            font-size: 23px;
            line-height: 1.12;
        }

        .cta-panel h2::before,
        .cta-panel h2::after {
            display: none;
        }

        .cta-panel p {
            margin: 0 0 14px;
            color: #ffe0aa;
            line-height: 1.55;
            font-size: 14px;
        }

        .cta-buttons {
            display: grid;
            gap: 10px;
        }

        .cta-buttons .btn {
            min-height: 46px;
            border-radius: 8px;
            font-weight: 900;
        }

        .btn-gold-fill {
            background: linear-gradient(180deg, #f4b233, #c97902);
            border: 1px solid #ffc55d;
            color: #fff;
        }

        .btn-gold-outline {
            background: rgba(43, 8, 3, .45);
            border: 1px solid var(--bd-gold);
            color: #ffd978;
        }

        .sidebar-faq {
            display: grid;
            gap: 10px;
        }

        .faq-item {
            border: 1px solid var(--bd-gold-soft);
            border-radius: 8px;
            background: #fff8eb;
            overflow: hidden;
        }

        .faq-question {
            width: 100%;
            border: 0;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 13px;
            color: #3b2418;
            font: inherit;
            font-size: 14px;
            font-weight: 800;
            text-align: left;
        }

        .faq-answer {
            display: none;
            padding: 0 13px 13px;
            color: var(--bd-muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .faq-item.active .faq-answer {
            display: block;
        }

        .faq-item.active .faq-toggle {
            transform: rotate(180deg);
        }

        .related-blogs {
            margin-top: 38px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 14px;
            color: var(--bd-maroon);
            font-family: Cinzel, serif;
            font-size: 25px;
            font-weight: 800;
            margin: 0 0 22px;
            text-align: center;
        }

        .section-title::before,
        .section-title::after {
            content: "";
            height: 1px;
            flex: 1;
            background: linear-gradient(90deg, transparent, var(--bd-gold-soft), transparent);
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .related-card {
            display: block;
            color: inherit;
            text-decoration: none;
            border: 1px solid #e9cca0;
            border-radius: 8px;
            background: #fff9ef;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(88, 18, 8, .10);
        }

        .related-image {
            height: 200px;
            background: center / cover no-repeat;
            position: relative;
        }

        .related-category {
            position: absolute;
            left: 12px;
            top: 10px;
            padding: 5px 10px;
            border-radius: 6px;
            background: #8a160b;
            color: #fff4dc;
            font-size: 12px;
            font-weight: 900;
        }

        .related-card-body {
            padding: 14px 16px 15px;
        }

        .related-card h3 {
            margin: 0 0 8px;
            color: #3a1c11;
            font-family: Cinzel, serif;
            font-size: 17px;
            line-height: 1.28;
        }

        .related-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            color: #96601e;
            font-size: 12px;
            font-weight: 800;
        }

        .all-blogs-link {
            display: flex;
            justify-content: center;
            margin-top: 18px;
        }

        .all-blogs-link .btn {
            min-width: 150px;
            border-color: var(--bd-gold);
            color: var(--bd-maroon);
            background: #fff8ec;
            font-weight: 900;
        }

        @media (max-width: 1024px) {
            .blog-detail-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }

            .blog-sidebar {
                position: static;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .cta-panel,
            .faq-panel {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 760px) {
            .blog-article {
    width: 100%;
    overflow: hidden;
}
            .blog-hero-detail {
                min-height: auto;
            }

            .blog-hero-image {
                width: 100%;
                opacity: .38;
            }

            .blog-hero-detail::before {
                background: linear-gradient(180deg, rgba(43, 8, 3, .96), rgba(88, 18, 8, .88));
            }

            .blog-hero-inner,
            .blog-detail-wrap {
                width: min(100% - 28px, 1180px);
            }

            .blog-hero-inner {
                padding: 32px 0 38px;
            }

            .hero-excerpt,
            .blog-article {
                font-size: 15px;
            }

            .blog-sidebar {
                grid-template-columns: 1fr;
            }

            .share-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .related-grid {
                grid-template-columns: 1fr;
            }

            .author-box {
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('body')
<main class="blog-detail-page">
    <section class="blog-hero-detail">
        <div class="blog-hero-image" style="background-image:url('{{ $heroImage }}')" aria-hidden="true"></div>
        <div class="blog-hero-inner">
            <nav class="blog-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}"><i class="bi bi-house-fill"></i> Home</a>
                <i class="bi bi-chevron-right"></i>
                <a href="{{ route('blogs') }}">Blogs</a>
                <i class="bi bi-chevron-right"></i>
                <span>{{ $categoryName }}</span>
            </nav>

            <div class="hero-copy">
                <span class="hero-chip"><i class="bi bi-sun"></i> {{ $categoryName }}</span>
                <h1>{{ $blog->title }}</h1>
                @if($blog->excerpt)
                    <p class="hero-excerpt">{{ $blog->excerpt }}</p>
                @endif
                <div class="hero-divider"></div>
                <div class="hero-meta">
                    <span><i class="bi bi-person-fill"></i> {{ $authorName }}</span>
                    @if($blog->published_at)
                        <span><i class="bi bi-calendar-event-fill"></i> {{ $blog->published_at->format('F j, Y') }}</span>
                    @endif
                    <span><i class="bi bi-clock-fill"></i> {{ $readMinutes }} min read</span>
                </div>
            </div>
        </div>
    </section>

    <div class="blog-detail-wrap">
        <div class="blog-detail-grid">
            <article>
                <div class="article-top-ornament"><i class="bi bi-fire"></i></div>

                <div class="blog-article">
                    {!! $contentHtml !!}
                </div>

                @if($blog->tags && count($blog->tags) > 0)
                    <div class="blog-tags">
                        <span>Tags:</span>
                        @foreach($blog->tags as $tag)
                            <span class="blog-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="author-box">
                    <div class="author-avatar"><i class="bi bi-fire"></i></div>
                    <div>
                        <h3>{{ $authorName }}</h3>
                        <p>{{ $authorName }} shares simple spiritual guides, pooja knowledge, hawan information and devotional practices for every family.</p>
                    </div>
                </div>
            </article>

            <aside class="blog-sidebar">
                @if(count($tocItems) > 0)
                    <section class="side-panel">
                        <h2>In This Article</h2>
                        <ul class="toc-list">
                            @foreach($tocItems as $item)
                                <li><a href="#{{ $item['id'] }}">{{ $item['title'] }}</a></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section class="side-panel">
                    <h2>Share This Guide</h2>
                    <div class="share-grid">
                        <a class="share-link" href="https://wa.me/?text={{ urlencode($shareText.' '.$shareUrl) }}" target="_blank" rel="noopener">
                            <span class="share-icon share-whatsapp"><i class="bi bi-whatsapp"></i></span>
                            WhatsApp
                        </a>
                        <a class="share-link" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener">
                            <span class="share-icon share-facebook"><i class="bi bi-facebook"></i></span>
                            Facebook
                        </a>
                        <a class="share-link" href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}" target="_blank" rel="noopener">
                            <span class="share-icon share-twitter"><i class="bi bi-twitter-x"></i></span>
                            Twitter
                        </a>
                        <a class="share-link" href="mailto:?subject={{ rawurlencode($shareText) }}&body={{ rawurlencode($shareUrl) }}">
                            <span class="share-icon share-email"><i class="bi bi-envelope-fill"></i></span>
                            Email
                        </a>
                    </div>
                </section>

                <section class="side-panel cta-panel">
                    <h2>Ready to Begin Your Bhakti Journey?</h2>
                    <p>Book personalized pooja, hawan or light a virtual diya with sankalp from home.</p>
                    <div class="cta-buttons">
                        <a class="btn btn-gold-fill" href="{{ route('personalized-pooja') }}"><i class="bi bi-flower1"></i> Book Pooja</a>
                        <a class="btn btn-gold-outline" href="{{ route('hawan') }}"><i class="bi bi-fire"></i> Book Hawan</a>
                    </div>
                </section>

                @if($blog->faqs && count($blog->faqs) > 0)
                    <section class="side-panel faq-panel">
                        <h2>Frequently Asked Questions</h2>
                        <div class="sidebar-faq">
                            @foreach($blog->faqs as $index => $faq)
                                <div class="faq-item {{ $index === 0 ? 'active' : '' }}">
                                    <button class="faq-question" type="button">
                                        <span>{{ $faq['question'] ?? '' }}</span>
                                        <i class="bi bi-chevron-down faq-toggle"></i>
                                    </button>
                                    <div class="faq-answer">
                                        {!! $faq['answer'] ?? '' !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>

        @if($relatedBlogs->count() > 0)
            <section class="related-blogs">
                <h2 class="section-title">Related Spiritual Guides</h2>
                <div class="related-grid">
                    @foreach($relatedBlogs as $related)
                        @php
                            $relatedImage = $related->featured_image ? asset('storage/'.$related->featured_image) : asset('assets/temple-hero.jpg');
                            $relatedMinutes = max(1, ceil(str_word_count(strip_tags($related->content ?? '')) / 200));
                        @endphp
                        <a class="related-card" href="{{ route('blogs.show', $related->slug) }}">
                            <div class="related-image" style="background-image:url('{{ $relatedImage }}')">
                                <span class="related-category">{{ $related->category?->name ?? 'Blog' }}</span>
                            </div>
                            <div class="related-card-body">
                                <h3>{{ str($related->title)->limit(58) }}</h3>
                                <div class="related-meta">
                                    @if($related->published_at)
                                        <span><i class="bi bi-calendar-event"></i> {{ $related->published_at->format('M j, Y') }}</span>
                                    @endif
                                    <span><i class="bi bi-clock"></i> {{ $relatedMinutes }} min read</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="all-blogs-link">
                    <a class="btn" href="{{ route('blogs') }}">View All Blogs <i class="bi bi-chevron-right"></i></a>
                </div>
            </section>
        @endif
    </div>
</main>
@endsection

@push('scripts')
    <script>
        // FAQ accordion
        document.querySelectorAll('.faq-question').forEach(function(button) {
            button.addEventListener('click', function() {
                button.closest('.faq-item').classList.toggle('active');
            });
        });

        // TOC links - scroll without adding history entries
        document.querySelectorAll('.toc-list a[href^="#"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.getAttribute('href').slice(1);
                var target = document.getElementById(id);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.replaceState(null, '', '#' + id);
                }
            });
        });
    </script>
@endpush
