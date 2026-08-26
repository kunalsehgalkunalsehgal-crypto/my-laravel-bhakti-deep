@extends('admin.layout')

@section('title', 'Hawans')

@section('content')
    <div class="toolbar">
        <h1>Hawans</h1>
        <a class="btn primary" href="{{ route('admin.hawans.create') }}">Add New</a>
    </div>
    <div class="panel">
        <form class="filters" method="GET">
            <input style="max-width:260px" type="search" name="search" value="{{ request('search') }}" placeholder="Search hawans">
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
                @forelse($records as $hawan)
                    @php
                        $enabledTypes = $hawan->enabledHawanTypes();
                        $displayPrice = collect($enabledTypes)->min('price') ?? $hawan->base_price;
                    @endphp
                    <tr>
                        <td>{{ $hawan->name }}</td>
                        <td>Rs.{{ number_format((int) $displayPrice) }}</td>
                        <td>{{ $hawan->duration ?: '-' }}</td>
                        <td>{{ $hawan->is_featured ? 'Yes' : 'No' }}</td>
                        <td><span class="badge {{ $hawan->status }}">{{ ucfirst($hawan->status) }}</span></td>
                        <td>
                            <div class="actions">
                                <a class="btn small" href="{{ route('admin.hawans.edit', $hawan) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.hawans.destroy', $hawan) }}" onsubmit="return confirm('Delete this hawan?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button class="btn small danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No hawans found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $records->links() }}</div>
    </div>
@endsection
