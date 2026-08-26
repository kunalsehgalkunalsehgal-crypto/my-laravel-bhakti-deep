@extends('admin.layout')
@section('title', 'Pandit — ' . $pandit->full_name)
@section('content')

<div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;flex-wrap:wrap">
    <a class="btn small" href="{{ route('admin.pandits.index') }}">← Back</a>
    <h1 style="margin:0">{{ $pandit->full_name }}</h1>
    @php
        $colors = ['verified'=>'active','under_review'=>'','needs_correction'=>'warn','rejected'=>'failed','suspended'=>'failed'];
    @endphp
    <span class="badge {{ $colors[$pandit->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$pandit->status)) }}</span>
</div>

{{-- ACTION FORM --}}
<div class="panel">
    <strong style="display:block;margin-bottom:12px">Update Status</strong>
    <form method="POST" action="{{ route('admin.pandits.status', $pandit->id) }}">
        @csrf
        <div class="form-grid" style="grid-template-columns:200px 1fr auto;align-items:end">
            <div>
                <label>Status</label>
                <select name="status">
                    @foreach(['under_review','needs_correction','verified','rejected','suspended'] as $s)
                        <option value="{{ $s }}" {{ $pandit->status == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Admin Remark (required for Needs Correction)</label>
                <input type="text" name="admin_remark" value="{{ $pandit->admin_remark }}" placeholder="Write remark for pandit...">
            </div>
            <div>
                <button class="btn primary" type="submit">Save</button>
            </div>
        </div>
    </form>
    @if($pandit->reviewed_at)
        <p style="margin:10px 0 0;font-size:13px;color:var(--muted)">Last reviewed: {{ $pandit->reviewed_at->format('d M Y, h:i A') }}</p>
    @endif
</div>

{{-- TABS --}}
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px">
    @foreach(['profile','qualification','services','languages','availability','online','documents','bank'] as $tab)
        <a href="#tab-{{ $tab }}"
           onclick="showTab('{{ $tab }}')"
           id="btn-{{ $tab }}"
           class="btn small"
           style="border-radius:999px">
            {{ ucfirst($tab) }}
        </a>
    @endforeach
</div>

{{-- PROFILE TAB --}}
<div id="tab-profile" class="tab-panel panel">
    <strong style="display:block;margin-bottom:12px">Profile</strong>
    @php
        $fields = [
            'Full Name' => $pandit->full_name,
            'Pandit Name' => $pandit->pandit_name,
            'Email' => $pandit->email,
            'Mobile' => $pandit->mobile,
            'WhatsApp' => $pandit->whatsapp_number,
            'DOB' => $pandit->date_of_birth?->format('d M Y'),
            'Gender' => $pandit->gender,
            'Address' => $pandit->full_address,
            'City' => $pandit->city,
            'State' => $pandit->state,
            'Country' => $pandit->country,
            'Experience' => $pandit->total_experience_years . ' yrs',
            'Institution' => $pandit->institution_name,
            'Position' => $pandit->position_role,
            'Independent' => $pandit->is_independent ? 'Yes' : 'No',
            'About' => $pandit->about,
        ];
    @endphp
    <div class="form-grid">
        @foreach($fields as $label => $value)
            <div>
                <label>{{ $label }}</label>
                <div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb;font-size:14px">{{ $value ?? '—' }}</div>
            </div>
        @endforeach
    </div>
    @if($pandit->profile_photo)
        <div style="margin-top:14px">
            <label>Profile Photo</label>
            <img src="{{ asset('storage/'.$pandit->profile_photo) }}" style="width:100px;height:100px;object-fit:cover;border-radius:12px;border:1px solid var(--line)">
        </div>
    @endif
</div>

{{-- QUALIFICATION TAB --}}
<div id="tab-qualification" class="tab-panel panel" style="display:none">
    <strong style="display:block;margin-bottom:12px">Qualification</strong>
    @if($pandit->qualification)
        @php $q = $pandit->qualification; @endphp
        <div class="form-grid">
            @foreach([
                'Highest Qualification' => $q->highest_qualification,
                'Course' => $q->course_name,
                'Institute' => $q->institute_name,
                'Guru' => $q->guru_name,
                'Year' => $q->completion_year,
                'Certificate No' => $q->certificate_number,
                'Sampradaya' => $q->sampradaya,
                'Ritual Tradition' => $q->ritual_tradition,
                'Sanskrit Level' => $q->sanskrit_level,
            ] as $label => $value)
                <div>
                    <label>{{ $label }}</label>
                    <div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb;font-size:14px">{{ $value ?? '—' }}</div>
                </div>
            @endforeach
        </div>
        @if($q->knowledge_areas)
            <div style="margin-top:12px">
                <label>Knowledge Areas</label>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px">
                    @foreach(is_array($q->knowledge_areas) ? $q->knowledge_areas : json_decode($q->knowledge_areas, true) ?? [] as $area)
                        <span class="badge">{{ $area }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <p style="color:var(--muted)">No qualification added.</p>
    @endif
</div>

{{-- SERVICES TAB --}}
<div id="tab-services" class="tab-panel panel" style="display:none">
    <strong style="display:block;margin-bottom:12px">Services</strong>
    @if($pandit->services->count())
        <table>
            <thead><tr><th>Type</th><th>Service</th><th>Experience</th><th>Performed</th><th>Duration</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($pandit->services as $s)
                <tr>
                    <td>{{ $s->service_type }}</td>
                    <td>{{ $s->service_name }}</td>
                    <td>{{ $s->experience_years }} yrs</td>
                    <td>{{ $s->approx_performed }}</td>
                    <td>{{ $s->duration_minutes }} min</td>
                    <td>
                        <form method="POST" action="{{ route('admin.pandits.services.status', [$pandit->id, $s->id]) }}" style="display:flex;gap:8px;align-items:center">
                            @csrf
                            <select name="status" style="min-width:120px">
                                @foreach(['pending','approved','rejected'] as $status)
                                    <option value="{{ $status }}" {{ $s->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <button class="btn small" type="submit">Save</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color:var(--muted)">No services added.</p>
    @endif
</div>

{{-- LANGUAGES TAB --}}
<div id="tab-languages" class="tab-panel panel" style="display:none">
    <strong style="display:block;margin-bottom:12px">Languages</strong>
    @if($pandit->languages->count())
        <div style="display:flex;flex-wrap:wrap;gap:8px">
            @foreach($pandit->languages as $l)
                <span class="badge">{{ $l->language }}</span>
            @endforeach
        </div>
    @else
        <p style="color:var(--muted)">No languages added.</p>
    @endif
</div>

{{-- AVAILABILITY TAB --}}
<div id="tab-availability" class="tab-panel panel" style="display:none">
    <strong style="display:block;margin-bottom:12px">Availability Slots</strong>
    @if($pandit->availabilitySlots->count())
        <table>
            <thead><tr><th>Day</th><th>From</th><th>To</th><th>Available</th></tr></thead>
            <tbody>
                @foreach($pandit->availabilitySlots as $slot)
                <tr>
                    <td>{{ $slot->day }}</td>
                    <td>{{ $slot->start_time }}</td>
                    <td>{{ $slot->end_time }}</td>
                    <td>{{ $slot->is_available ? 'Yes' : 'No' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color:var(--muted)">No slots added.</p>
    @endif
</div>

{{-- ONLINE SETUP TAB --}}
<div id="tab-online" class="tab-panel panel" style="display:none">
    <strong style="display:block;margin-bottom:12px">Online Setup</strong>
    @if($pandit->onlineSetup)
        @php $o = $pandit->onlineSetup; @endphp
        <div class="form-grid">
            <div><label>Online Hawan</label><div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb">{{ $o->online_hawan ? 'Yes' : 'No' }}</div></div>
            <div><label>Online Pooja</label><div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb">{{ $o->online_pooja ? 'Yes' : 'No' }}</div></div>
            <div><label>Stable Internet</label><div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb">{{ $o->stable_internet ? 'Yes' : 'No' }}</div></div>
            <div><label>Platforms</label><div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb">{{ $o->platforms ? implode(', ', $o->platforms) : '—' }}</div></div>
            <div><label>Devices</label><div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb">{{ $o->devices ? implode(', ', $o->devices) : '—' }}</div></div>
            <div><label>Equipment</label><div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb">{{ $o->equipment ? implode(', ', $o->equipment) : '—' }}</div></div>
        </div>
    @else
        <p style="color:var(--muted)">No online setup added.</p>
    @endif
</div>

{{-- DOCUMENTS TAB --}}
<div id="tab-documents" class="tab-panel panel" style="display:none">
    <strong style="display:block;margin-bottom:12px">Documents</strong>
    @if($pandit->document)
        @php $d = $pandit->document; @endphp
        <div class="form-grid">
            <div><label>ID Type</label><div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb">{{ $d->government_id_type ?? '—' }}</div></div>
            <div><label>PAN Number</label><div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb">{{ $d->pan_number ?? '—' }}</div></div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:14px">
            @foreach(['government_id_file','pan_card_file','address_proof','qualification_certificate','mantra_chanting_sample','hawan_performance_video'] as $file)
                @if($d->$file)
                    <a class="btn small" href="{{ asset('storage/'.$d->$file) }}" target="_blank">{{ ucfirst(str_replace('_',' ',$file)) }}</a>
                @endif
            @endforeach
        </div>
    @else
        <p style="color:var(--muted)">No documents uploaded.</p>
    @endif
</div>

{{-- BANK TAB --}}
<div id="tab-bank" class="tab-panel panel" style="display:none">
    <strong style="display:block;margin-bottom:12px">Bank Details</strong>
    @if($pandit->bankDetail)
        @php $b = $pandit->bankDetail; @endphp
        <div class="form-grid">
            @foreach([
                'Account Holder' => $b->account_holder_name,
                'Bank Name' => $b->bank_name,
                'Account Number' => $b->account_number,
                'IFSC' => $b->ifsc_code,
                'UPI ID' => $b->upi_id,
                'PAN' => $b->pan_number,
            ] as $label => $value)
                <div>
                    <label>{{ $label }}</label>
                    <div style="padding:9px 11px;border:1px solid var(--line);border-radius:7px;background:#f9fafb;font-size:14px">{{ $value ?? '—' }}</div>
                </div>
            @endforeach
        </div>
    @else
        <p style="color:var(--muted)">No bank details added.</p>
    @endif
</div>

@push('scripts')
<script>
    function showTab(name) {
        document.querySelectorAll('.tab-panel').forEach(el => el.style.display = 'none');
        document.querySelectorAll('[id^="btn-"]').forEach(el => el.style.fontWeight = '');
        document.getElementById('tab-' + name).style.display = 'block';
        document.getElementById('btn-' + name).style.fontWeight = '900';
    }
    showTab('profile');
</script>
@endpush

@endsection
