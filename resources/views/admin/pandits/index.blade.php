@extends('admin.layout')
@section('title', 'Pandits')
@section('content')

<h1>Pandit Management</h1>

<div class="toolbar">
    <form class="filters" method="GET">
        <input style="width:220px" name="search" placeholder="Search name, email..." value="{{ request('search') }}">
        <select style="width:160px" name="status" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(['under_review','needs_correction','verified','rejected','suspended'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <button class="btn" type="submit">Search</button>
        <a class="btn" href="{{ route('admin.pandits.index') }}">Reset</a>
    </form>
</div>

<div class="panel" style="padding:0">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>City</th>
                <th>Status</th>
                <th>Registered</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pandits as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->full_name }}</td>
                <td>{{ $p->email }}</td>
                <td>{{ $p->mobile }}</td>
                <td>{{ $p->city }}</td>
                <td>
                    @php
                        $colors = [
                            'verified'         => 'active',
                            'under_review'     => '',
                            'needs_correction' => 'warn',
                            'rejected'         => 'failed',
                            'suspended'        => 'failed',
                        ];
                    @endphp
                    <span class="badge {{ $colors[$p->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span>
                </td>
                <td>{{ $p->created_at->format('d M Y') }}</td>
                <td>
                    <a class="btn small" href="{{ route('admin.pandits.show', $p->id) }}">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:var(--muted)">No pandits found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">{{ $pandits->links() }}</div>

@endsection
