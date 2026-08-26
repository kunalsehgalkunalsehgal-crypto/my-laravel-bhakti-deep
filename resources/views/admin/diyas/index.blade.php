@extends('admin.layout')

@section('title', 'Diyas')

@section('content')
    <div class="toolbar">
        <h1>Diyas</h1>
        <a class="btn primary" href="{{ route('admin.diyas.create') }}">Add New</a>
    </div>

    <div class="panel">
        <form class="filters" method="GET">
            <input style="max-width:260px" type="search" name="search" value="{{ request('search') }}" placeholder="Search diyas">
            <select style="max-width:210px" name="deity_selection_mode">
                <option value="">All deity modes</option>
                <option value="fixed" @selected(request('deity_selection_mode') === 'fixed')>Fixed Deity</option>
                <option value="user_select" @selected(request('deity_selection_mode') === 'user_select')>User Can Select Deity</option>
            </select>
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
                    <th>Seva Amount</th>
                    <th>Category</th>
                    <th>Deity Mode</th>
                    <th>Fixed Deity</th>
                    <th>Mantra Audio</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $diya)
                    <tr>
                        <td>
                            <strong>{{ $diya->name }}</strong><br>
                            <span style="color:#667085;font-size:12px">{{ $diya->slug }}</span>
                        </td>
                        <td>Rs.{{ number_format((float) $diya->seva_amount, 2) }}</td>
                        <td>{{ $diya->category ?: '-' }}</td>
                        <td>{{ $diya->isFixedDeity() ? 'Fixed Deity' : 'User Can Select Deity' }}</td>
                        <td>{{ $diya->fixedDeity?->name ?: '-' }}</td>
                        <td>{{ $diya->mantraAudio?->title ?: '-' }}</td>
                        <td><span class="badge {{ $diya->status }}">{{ ucfirst($diya->status) }}</span></td>
                        <td>
                            <div class="actions">
                                <a class="btn small" href="{{ route('admin.diyas.show', $diya) }}">View</a>
                                <a class="btn small" href="{{ route('admin.diyas.edit', $diya) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.diyas.toggle-status', $diya) }}">
                                    @csrf
                                    <button class="btn small" type="submit">{{ $diya->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.diyas.destroy', $diya) }}" onsubmit="return confirm('Delete this diya?')">
                                    @csrf @method('DELETE')
                                    <button class="btn small danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">No diyas found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $records->links() }}</div>
    </div>
@endsection
