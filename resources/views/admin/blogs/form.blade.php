@extends('admin.layout')

@section('title', ($mode === 'create' ? 'Add ' : 'Edit ').'Blog')

@section('content')
    <h1>{{ $mode === 'create' ? 'Add New Blog' : 'Edit Blog' }}</h1>

    <form id="blogForm" class="panel" method="POST" enctype="multipart/form-data"
        action="{{ $mode === 'create' ? route('admin.blogs.store') : route('admin.blogs.update', $record) }}">
        @csrf
        @if($mode !== 'create') @method('PUT') @endif

        <!-- BASIC DETAILS -->
        <div style="margin-bottom:28px;">
            <h2 style="font-size:16px;margin-bottom:14px;color:#18212f;">Basic Details</h2>
            <div class="form-grid">
                <div>
                    <label>Category *</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" @selected(old('category_id', $record->blog_category_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Title *</label>
                    <input type="text" name="title" id="titleInput" value="{{ old('title', $record->title) }}" required>
                    @error('title') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Slug</label>
                    <input type="text" name="slug" id="slugInput" value="{{ old('slug', $record->slug) }}">
                    <small style="color:#667085;">Auto-generated from title</small>
                    @error('slug') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Focus Keyword</label>
                    <input type="text" name="focus_keyword" value="{{ old('focus_keyword', $record->focus_keyword) }}" placeholder="e.g., hawan benefits">
                    @error('focus_keyword') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div style="margin-bottom:28px;">
            <h2 style="font-size:16px;margin-bottom:14px;color:#18212f;">Content</h2>
            <div class="form-grid">
                <div class="full">
                    <label>Excerpt</label>
                    <textarea name="excerpt" style="min-height:80px;">{{ old('excerpt', $record->excerpt) }}</textarea>
                    @error('excerpt') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div class="full">
                    <label>Content *</label>
                    <textarea id="contentEditor" name="content">{{ old('content', $record->content) }}</textarea>
                    @error('content') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- IMAGE & MEDIA -->
        <div style="margin-bottom:28px;">
            <h2 style="font-size:16px;margin-bottom:14px;color:#18212f;">Image & Media</h2>
            <div class="form-grid">
                <div class="full">
                    <label>Featured Image</label>
                    <input type="file" name="featured_image" accept="image/*">
                    @if($record->featured_image)
                        <p style="margin-top:8px;"><a href="{{ asset('storage/'.$record->featured_image) }}" target="_blank" style="color:#8a3ffc;">View current image</a></p>
                    @endif
                    @error('featured_image') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div class="full">
                    <label>Featured Image Alt Text</label>
                    <input type="text" name="image_alt" value="{{ old('image_alt', $record->image_alt) }}" placeholder="Describe the image for accessibility">
                    @error('image_alt') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div class="full">
                    <label>OG Image (Social Sharing)</label>
                    <input type="file" name="og_image" accept="image/*">
                    @if($record->og_image)
                        <p style="margin-top:8px;"><a href="{{ asset('storage/'.$record->og_image) }}" target="_blank" style="color:#8a3ffc;">View current OG image</a></p>
                    @endif
                    @error('og_image') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- SEO SETTINGS -->
        <div style="margin-bottom:28px;">
            <h2 style="font-size:16px;margin-bottom:14px;color:#18212f;">SEO Settings</h2>
            <div class="form-grid">
                <div class="full">
                    <label>Meta Title <span style="color:#667085;font-size:12px;">(<span id="metaTitleCount">0</span>/60)</span></label>
                    <input type="text" name="meta_title" id="metaTitleInput" value="{{ old('meta_title', $record->meta_title) }}" maxlength="60">
                    <small style="color:#667085;">Recommended: 50-60 characters</small>
                    @error('meta_title') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div class="full">
                    <label>Meta Description <span style="color:#667085;font-size:12px;">(<span id="metaDescCount">0</span>/160)</span></label>
                    <textarea name="meta_description" id="metaDescInput" style="min-height:60px;" maxlength="160">{{ old('meta_description', $record->meta_description) }}</textarea>
                    <small style="color:#667085;">Recommended: 150-160 characters</small>
                    @error('meta_description') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div class="full">
                    <label>Canonical URL</label>
                    <input type="url" name="canonical_url" value="{{ old('canonical_url', $record->canonical_url) }}" placeholder="https://example.com/blog/post">
                    @error('canonical_url') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div class="full">
                    <label>Tags (comma separated)</label>
                    <input type="text" name="tags" value="{{ old('tags', is_array($record->tags) ? implode(', ', $record->tags) : $record->tags) }}" placeholder="e.g., hawan, ritual, spiritual">
                    @error('tags') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- SOCIAL SHARING -->
        <div style="margin-bottom:28px;">
            <h2 style="font-size:16px;margin-bottom:14px;color:#18212f;">Social Sharing</h2>
            <div class="form-grid">
                <div class="full">
                    <label>OG Title</label>
                    <input type="text" name="og_title" value="{{ old('og_title', $record->og_title) }}" placeholder="Title for social media">
                    @error('og_title') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div class="full">
                    <label>OG Description</label>
                    <textarea name="og_description" style="min-height:60px;">{{ old('og_description', $record->og_description) }}</textarea>
                    @error('og_description') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- FAQs -->
        <div style="margin-bottom:28px;">
            <h2 style="font-size:16px;margin-bottom:14px;color:#18212f;">FAQs</h2>
            <input type="hidden" id="faqsJson" name="faqs" value="">
            <div id="faqContainer"></div>
            <button type="button" onclick="openFaqModal()" class="btn" style="margin-top:10px;">+ Add FAQ</button>
        </div>

        <!-- SCHEMA MARKUP -->
        <div style="margin-bottom:28px;">
            <h2 style="font-size:16px;margin-bottom:14px;color:#18212f;">Schema Markup</h2>
            <div class="form-grid">
                <div class="full">
                    <label>Schema Markup JSON (Auto-generated)</label>
                    <textarea name="schema_markup" style="min-height:150px;font-family:monospace;font-size:12px;">{{ old('schema_markup', json_encode($record->schema_markup ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                    <small style="color:#667085;">Automatically generated from blog data. Edit manually if needed.</small>
                    @error('schema_markup') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- PUBLISH SETTINGS -->
        <div style="margin-bottom:28px;">
            <h2 style="font-size:16px;margin-bottom:14px;color:#18212f;">Publish Settings</h2>
            <div class="form-grid">
                <div>
                    <label>Author Name</label>
                    <input type="text" name="author_name" value="{{ old('author_name', $record->author_name) }}" placeholder="e.g., Spiritual Guide">
                    @error('author_name') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="draft" @selected(old('status', $record->status) === 'draft')>Draft</option>
                        <option value="published" @selected(old('status', $record->status) === 'published')>Published</option>
                        <option value="scheduled" @selected(old('status', $record->status) === 'scheduled')>Scheduled</option>
                    </select>
                    @error('status') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Published At</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', $record->published_at?->format('Y-m-d\TH:i')) }}">
                    @error('published_at') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="actions" style="margin-top:28px;">
            <button class="btn primary" type="submit">Save Blog</button>
            <a class="btn" href="{{ route('admin.blogs.index') }}">Cancel</a>
        </div>
    </form>

    <!-- FAQ Modal - OUTSIDE form -->
    <div id="faqModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:8px;padding:24px;width:90%;max-width:500px;">
            <h3 style="margin-top:0;">Add FAQ</h3>
            <div style="margin-bottom:14px;">
                <label>Question *</label>
                <input type="text" id="faqQuestion" style="width:100%;border:1px solid #d9e0ea;border-radius:7px;padding:10px;font:inherit;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:14px;">
                <label>Answer *</label>
                <textarea id="faqAnswer" style="width:100%;border:1px solid #d9e0ea;border-radius:7px;padding:10px;font:inherit;min-height:100px;box-sizing:border-box;resize:vertical;"></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn" onclick="closeFaqModal()">Cancel</button>
                <button type="button" class="btn primary" onclick="saveFaq()">Save FAQ</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
<script>
// var faqs = @json(is_array($record->faqs ?? []) ? $record->faqs : []);
var faqs = @json($record->faqs ?? []) || [];
// FAQ functions - global scope so onclick works
function openFaqModal() {
    document.getElementById('faqModal').style.display = 'block';
    document.getElementById('faqQuestion').value = '';
    document.getElementById('faqAnswer').value = '';
    document.getElementById('faqQuestion').focus();
}

function closeFaqModal() {
    document.getElementById('faqModal').style.display = 'none';
}

function saveFaq() {
    var q = document.getElementById('faqQuestion').value.trim();
    var a = document.getElementById('faqAnswer').value.trim();
    if (!q || !a) { alert('Please fill in both question and answer'); return; }
    faqs.push({ question: q, answer: a });
    renderFaqs();
    closeFaqModal();
}

function removeFaq(index) {
    faqs.splice(index, 1);
    renderFaqs();
}

function renderFaqs() {
    var container = document.getElementById('faqContainer');
    var html = '';
    for (var i = 0; i < faqs.length; i++) {
        html += '<div style="border:1px solid #d9e0ea;border-radius:7px;padding:14px;margin-bottom:10px;">';
        html += '<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">';
        html += '<div style="flex:1;"><strong>Q: ' + faqs[i].question + '</strong>';
        html += '<p style="margin:4px 0 0;color:#667085;font-size:13px;">A: ' + faqs[i].answer.substring(0, 100) + (faqs[i].answer.length > 100 ? '...' : '') + '</p></div>';
        html += '<button type="button" class="btn small danger" onclick="removeFaq(' + i + ')">Remove</button>';
        html += '</div></div>';
    }
    container.innerHTML = html;
    document.getElementById('faqsJson').value = JSON.stringify(faqs);
}

// Close modal on outside click
document.getElementById('faqModal').addEventListener('click', function(e) {
    if (e.target === this) closeFaqModal();
});

// Init on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // CKEditor
    ClassicEditor.create(document.getElementById('contentEditor'), {
        toolbar: [  'undo','redo','|',
        'heading','|',
        'bold','italic','underline','strikethrough','|',
        'fontSize','fontColor','fontBackgroundColor','|',
        'bulletedList','numberedList','todoList','|',
        'alignment','outdent','indent','|',
        'link','blockQuote','codeBlock','|',
        'insertTable','imageUpload','mediaEmbed','horizontalLine','|',
        'htmlEmbed']
    }).catch(function(err) { console.error(err); });

    // Auto slug
    document.getElementById('titleInput').addEventListener('input', function() {
        document.getElementById('slugInput').value = this.value.toLowerCase().trim()
            .replace(/[^\w\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');
    });

    // Char counters
    var metaTitle = document.getElementById('metaTitleInput');
    var metaDesc = document.getElementById('metaDescInput');
    document.getElementById('metaTitleCount').textContent = metaTitle.value.length;
    document.getElementById('metaDescCount').textContent = metaDesc.value.length;
    metaTitle.addEventListener('input', function() { document.getElementById('metaTitleCount').textContent = this.value.length; });
    metaDesc.addEventListener('input', function() { document.getElementById('metaDescCount').textContent = this.value.length; });

    // Render existing FAQs
    renderFaqs();
});
</script>
@endpush
