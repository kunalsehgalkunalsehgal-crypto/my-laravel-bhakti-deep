<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Blog;
use App\Models\Admin\BlogCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminBlogController extends BaseAdminResourceController
{
    protected string $modelClass = Blog::class;
    protected string $routePrefix = 'admin.blogs';
    protected string $viewTitle = 'Blogs';
    protected string $permission = 'manage-blogs';
    protected array $relations = ['category', 'author'];
    protected array $search = ['title', 'excerpt'];

    protected array $fields = [
        'category_id' => ['label' => 'Blog Category', 'type' => 'select', 'options_callback' => 'categories', 'rules' => ['required', 'exists:blog_categories,id']],
        'title' => ['label' => 'Title', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['nullable', 'string', 'max:255'], 'unique' => true],
        'focus_keyword' => ['label' => 'Focus Keyword', 'rules' => ['nullable', 'string', 'max:100']],
        'excerpt' => ['label' => 'Excerpt', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:500']],
        'content' => ['label' => 'Content', 'type' => 'richtext', 'rules' => ['nullable', 'string']],
        'featured_image' => ['label' => 'Featured Image', 'type' => 'file', 'path' => 'blogs', 'rules' => ['nullable', 'image', 'max:2048']],
        'image_alt' => ['label' => 'Featured Image Alt Text', 'rules' => ['nullable', 'string', 'max:255']],
        'tags' => ['label' => 'Tags (comma separated)', 'rules' => ['nullable', 'string']],
        'author_name' => ['label' => 'Author Name', 'rules' => ['nullable', 'string', 'max:255']],
        'canonical_url' => ['label' => 'Canonical URL', 'rules' => ['nullable', 'url']],
        'meta_title' => ['label' => 'Meta Title', 'rules' => ['nullable', 'string', 'max:60']],
        'meta_description' => ['label' => 'Meta Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:160']],
        'og_title' => ['label' => 'OG Title', 'rules' => ['nullable', 'string', 'max:255']],
        'og_description' => ['label' => 'OG Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:255']],
        'og_image' => ['label' => 'OG Image', 'type' => 'file', 'path' => 'blogs/og', 'rules' => ['nullable', 'image', 'max:2048']],
        'faqs' => ['label' => 'FAQs', 'type' => 'hidden', 'rules' => ['nullable', 'json']],
        'schema_markup' => ['label' => 'Schema Markup JSON', 'type' => 'textarea', 'rules' => ['nullable', 'json']],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'published' => 'Published', 'scheduled' => 'Scheduled'], 'rules' => ['required', 'in:draft,published,scheduled']],
        'published_at' => ['label' => 'Published At', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']],
    ];

    public function index(Request $request)
    {
        $query = Blog::with('category');
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhere('excerpt', 'like', '%'.$request->search.'%')
                  ->orWhere('focus_keyword', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $records = $query->latest()->paginate(15)->withQueryString();
        return view('admin.blogs.index', compact('records'));
    }

    public function create()
    {
        return view('admin.blogs.form', $this->viewData(['record' => new $this->modelClass(), 'mode' => 'create']));
    }

    public function edit(string $id)
    {
        $record = Blog::findOrFail($id);
        $record->category_id = $record->blog_category_id;
        return view('admin.blogs.form', $this->viewData(compact('record') + ['mode' => 'edit']));
    }

    protected function prepareData(Request $request, array $data, ?Model $record = null): array
    {
        $data = parent::prepareData($request, $data, $record);
        
        $data['author_admin_id'] = $record?->author_admin_id ?: Auth::guard('admin')->id();
        $data['blog_category_id'] = $data['category_id'] ?? null;
        unset($data['category_id']);

        if (isset($data['tags']) && is_string($data['tags'])) {
            $data['tags'] = array_filter(array_map('trim', explode(',', $data['tags'])));
        }

        if (isset($data['faqs']) && is_string($data['faqs'])) {
            $data['faqs'] = json_decode($data['faqs'], true) ?? [];
        }

        if (isset($data['schema_markup']) && is_string($data['schema_markup'])) {
            $data['schema_markup'] = json_decode($data['schema_markup'], true);
        }

        if (($data['status'] ?? null) === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title'] ?? '');
        }

        if (empty($data['meta_title'])) {
            $data['meta_title'] = substr($data['title'] ?? '', 0, 60);
        }

        if (empty($data['og_title'])) {
            $data['og_title'] = $data['title'] ?? '';
        }

        if (empty($data['og_description'])) {
            $data['og_description'] = $data['excerpt'] ?? '';
        }

        $data['schema_markup'] = $this->generateBlogSchema($data, $record);

        return $data;
    }

    protected function generateBlogSchema(array $data, ?Model $record = null): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $data['title'] ?? '',
            'description' => $data['excerpt'] ?? '',
            'image' => $data['featured_image'] ? asset('storage/'.$data['featured_image']) : null,
            'author' => [
                '@type' => 'Person',
                'name' => $data['author_name'] ?? Auth::guard('admin')->user()?->name ?? 'BhaktiDeep',
            ],
            'datePublished' => $data['published_at'] ?? now()->toIso8601String(),
            'dateModified' => now()->toIso8601String(),
            'url' => $data['canonical_url'] ?? route('blogs.show', $data['slug'] ?? ''),
            'keywords' => implode(',', $data['tags'] ?? []),
        ];

        if (!empty($data['faqs'])) {
            $faqs = $data['faqs'];
            if (is_string($faqs)) {
                $faqs = json_decode($faqs, true) ?? [];
            }
            
            if (is_array($faqs) && count($faqs) > 0) {
                $schema['mainEntity'] = [
                    '@type' => 'FAQPage',
                    'mainEntity' => array_map(function ($faq) {
                        return [
                            '@type' => 'Question',
                            'name' => $faq['question'] ?? '',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => $faq['answer'] ?? '',
                            ],
                        ];
                    }, $faqs),
                ];
            }
        }

        return array_filter($schema);
    }

    protected function viewData(array $data = []): array
    {
        return parent::viewData($data + [
            'categories' => BlogCategory::where('status', 'active')->orderBy('name')->pluck('name', 'id')->all(),
        ]);
    }
}
