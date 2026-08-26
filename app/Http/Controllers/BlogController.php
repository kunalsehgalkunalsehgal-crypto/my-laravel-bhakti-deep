<?php

namespace App\Http\Controllers;

use App\Models\Admin\Blog;
use App\Models\Admin\BlogCategory;

class BlogController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::where('status', 'active')->orderBy('name')->get();
        $blogs = Blog::with('category')
            ->where('status', 'published')
            // ->latest('published_at')
            ->orderBy('published_at', 'asc')
            ->get();

        return view('pages.blogs', compact('blogs', 'categories'));
    }

    public function category(string $slug)
    {
        $activeCategory = BlogCategory::where('slug', $slug)->where('status', 'active')->firstOrFail();
        $categories = BlogCategory::where('status', 'active')->orderBy('name')->get();
        $blogs = Blog::with('category')
            ->where('status', 'published')
            ->where('blog_category_id', $activeCategory->id)
            ->latest('published_at')
            ->get();

        return view('pages.blogs', compact('blogs', 'categories', 'activeCategory'));
    }

    public function show(string $slug)
    {
        $blog = Blog::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $relatedBlogs = Blog::with('category')
            ->where('status', 'published')
            ->where('blog_category_id', $blog->blog_category_id)
            ->where('id', '!=', $blog->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.blog-detail', compact('blog', 'relatedBlogs'));
    }
}
