@extends('admin.layout')

@section('title', 'Poojas')

@section('content')
    <div class="toolbar">
        <h1>Poojas</h1>
        <a class="btn primary" href="{{ route('admin.poojas.create') }}">Add New</a>
    </div>
    <div class="panel">
        <form class="filters" method="GET">
            <input style="max-width:260px" type="search" name="search" value="{{ request('search') }}" placeholder="Search poojas">
            <select style="max-width:180px" name="status">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <button class="btn" type="submit">Filter</button>
        </form>
    </div>
    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $pooja)
                    <tr>
                        <td>{{ $pooja->name }}</td>
                        <td>Rs.{{ number_format((int) $pooja->base_price) }}</td>
                        <td>{{ $pooja->duration ?: '-' }}</td>
                        <td>{{ $pooja->is_featured ? 'Yes' : 'No' }}</td>
                        <td><span class="badge {{ $pooja->status }}">{{ ucfirst($pooja->status) }}</span></td>
                        <td>
                            <div class="actions">
                                <a class="btn small" href="{{ route('admin.poojas.edit', $pooja) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.poojas.destroy', $pooja) }}" onsubmit="return confirm('Delete this pooja?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button class="btn small danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No poojas found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $records->links() }}</div>
    </div>
@endsection
