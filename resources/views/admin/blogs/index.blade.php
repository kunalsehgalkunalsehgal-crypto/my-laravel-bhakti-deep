@extends('admin.layout')

@section('title', 'Blogs')

@section('content')
    <div class="toolbar">
        <h1>Blogs</h1>
        <a class="btn primary" href="{{ route('admin.blogs.create') }}">Add New</a>
    </div>
    <div class="panel">
        <form class="filters" method="GET">
            <input style="max-width:260px" type="search" name="search" value="{{ request('search') }}" placeholder="Search blogs">
            <select style="max-width:180px" name="status">
                <option value="">All statuses</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
                <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
            </select>
            <button class="btn" type="submit">Filter</button>
        </form>
    </div>
    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Focus Keyword</th>
                    <th>Author</th>
                    <th>Published Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $blog)
                    <tr>
                        <td>{{ str($blog->title)->limit(50) }}</td>
                        <td><span class="badge active">{{ $blog->category?->name ?? '—' }}</span></td>
                        <td><span class="badge {{ $blog->status }}">{{ ucfirst($blog->status) }}</span></td>
                        <td><small style="color:#667085;">{{ $blog->focus_keyword ?? '—' }}</small></td>
                        <td>{{ $blog->author_name ?? $blog->author?->name ?? '—' }}</td>
                        <td>{{ $blog->published_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="actions">
                                <a class="btn small" href="{{ route('admin.blogs.edit', $blog) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" onsubmit="return confirm('Delete this blog?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button class="btn small danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">No blogs found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $records->links() }}</div>
    </div>
@endsection
