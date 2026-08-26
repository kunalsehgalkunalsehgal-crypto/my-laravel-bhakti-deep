@extends('admin.layout')
@section('title', 'Chat — ' . $pandit->full_name)
@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap">
    <a class="btn small" href="{{ route('admin.pandit-messages.index') }}">← Back</a>
    <h1 style="margin:0">{{ $pandit->full_name }}</h1>
    <span class="badge">{{ ucfirst(str_replace('_',' ',$pandit->status)) }}</span>
</div>

@if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

<div class="panel" style="padding:0;overflow:hidden">

    {{-- Messages --}}
    <div id="chatBox" style="padding:20px;display:grid;gap:12px;max-height:500px;overflow-y:auto">

        @forelse($messages as $msg)

            @if($msg->sender === 'admin')
                {{-- Admin message - right side --}}
                <div style="display:flex;justify-content:flex-end;gap:10px;align-items:flex-end">
                    <div style="max-width:65%">
                        <div style="background:var(--brand);color:#fff;border-radius:18px 18px 4px 18px;padding:12px 16px;font-size:14px">
                            {{ $msg->message }}
                        </div>
                        <div style="display:flex;justify-content:flex-end;align-items:center;gap:8px;margin-top:4px">
                            <small style="color:var(--muted);font-size:11px">{{ $msg->created_at->format('d M, h:i A') }}</small>
                            <form method="POST" action="{{ route('admin.pandit-messages.delete', $msg->id) }}" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:var(--bad);cursor:pointer;font-size:12px;padding:0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div style="width:34px;height:34px;border-radius:50%;background:var(--brand);display:grid;place-items:center;color:#fff;font-size:13px;flex-shrink:0">
                        A
                    </div>
                </div>

            @else
                {{-- Pandit message - left side --}}
                <div style="display:flex;gap:10px;align-items:flex-end">
                    <div style="width:34px;height:34px;border-radius:50%;background:#e85d04;display:grid;place-items:center;color:#fff;font-size:13px;flex-shrink:0">
                        P
                    </div>
                    <div style="max-width:65%">
                        <small style="color:#e85d04;font-size:11px;font-weight:700">{{ $pandit->full_name }}</small>
                        <div style="background:#f6f8fb;border:1px solid var(--line);border-radius:18px 18px 18px 4px;padding:12px 16px;font-size:14px;margin-top:4px">
                            {{ $msg->message }}
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:4px">
                            <small style="color:var(--muted);font-size:11px">{{ $msg->created_at->format('d M, h:i A') }}</small>
                            <form method="POST" action="{{ route('admin.pandit-messages.delete', $msg->id) }}" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:var(--bad);cursor:pointer;font-size:12px;padding:0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

        @empty
            <div style="text-align:center;padding:40px;color:var(--muted)">
                <p>No messages yet.</p>
            </div>
        @endforelse

    </div>

    {{-- Reply Form --}}
    <div style="border-top:1px solid var(--line);padding:16px 20px;background:#fff">
        <form method="POST" action="{{ route('admin.pandit-messages.reply', $pandit->id) }}" style="display:flex;gap:10px;align-items:flex-end">
            @csrf
            <textarea name="message" rows="2" placeholder="Type your reply..." required
                style="flex:1;border:1px solid var(--line);border-radius:7px;padding:10px 12px;font:inherit;resize:none;font-size:14px"></textarea>
            <button type="submit" class="btn primary" style="min-height:52px;padding:0 20px">
                <i class="bi bi-send"></i> Reply
            </button>
        </form>
    </div>

</div>

{{-- <script>
    const chatBox = document.getElementById('chatBox');
    chatBox.scrollTop = chatBox.scrollHeight;
</script> --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const chatBox = document.getElementById('chatBox');

    // Page open hote hi bottom scroll
    chatBox.scrollTop = chatBox.scrollHeight;

    // Jis Pandit ki chat Admin ne open ki hai
    const panditId = {{ $pandit->id }};

    // Pandit ka name
    const panditName = @json($pandit->full_name);

    console.log(
        'Admin listening:',
        `pandit.chat.${panditId}`
    );


    window.Echo
        .private(`pandit.chat.${panditId}`)
        .listen('.pandit.message.sent', function (event) {

            console.log('Live message received:', event);

            const msg = event.message;

            let html = '';


            // ==========================
            // PANDIT MESSAGE
            // Left side
            // ==========================
            if (msg.sender === 'pandit') {

                html = `
                    <div style="
                        display:flex;
                        gap:10px;
                        align-items:flex-end;
                    ">

                        <div style="
                            width:34px;
                            height:34px;
                            border-radius:50%;
                            background:#e85d04;
                            display:grid;
                            place-items:center;
                            color:#fff;
                            font-size:13px;
                            flex-shrink:0;
                        ">
                            P
                        </div>

                        <div style="max-width:65%">

                            <small style="
                                color:#e85d04;
                                font-size:11px;
                                font-weight:700;
                            ">
                                ${escapeHtml(panditName)}
                            </small>

                            <div style="
                                background:#f6f8fb;
                                border:1px solid var(--line);
                                border-radius:18px 18px 18px 4px;
                                padding:12px 16px;
                                font-size:14px;
                                margin-top:4px;
                            ">
                                ${escapeHtml(msg.message)}
                            </div>

                            <div style="
                                display:flex;
                                align-items:center;
                                gap:8px;
                                margin-top:4px;
                            ">

                                <small style="
                                    color:var(--muted);
                                    font-size:11px;
                                ">
                                    ${msg.created_at}
                                </small>

                            </div>

                        </div>

                    </div>
                `;
            }


            // ==========================
            // ADMIN MESSAGE
            // Right side
            // ==========================
            else if (msg.sender === 'admin') {

                html = `
                    <div style="
                        display:flex;
                        justify-content:flex-end;
                        gap:10px;
                        align-items:flex-end;
                    ">

                        <div style="max-width:65%">

                            <div style="
                                background:var(--brand);
                                color:#fff;
                                border-radius:18px 18px 4px 18px;
                                padding:12px 16px;
                                font-size:14px;
                            ">
                                ${escapeHtml(msg.message)}
                            </div>

                            <div style="
                                display:flex;
                                justify-content:flex-end;
                                align-items:center;
                                gap:8px;
                                margin-top:4px;
                            ">

                                <small style="
                                    color:var(--muted);
                                    font-size:11px;
                                ">
                                    ${msg.created_at}
                                </small>

                            </div>

                        </div>

                        <div style="
                            width:34px;
                            height:34px;
                            border-radius:50%;
                            background:var(--brand);
                            display:grid;
                            place-items:center;
                            color:#fff;
                            font-size:13px;
                            flex-shrink:0;
                        ">
                            A
                        </div>

                    </div>
                `;
            }


            // Message screen par add
            if (html !== '') {

                chatBox.insertAdjacentHTML(
                    'beforeend',
                    html
                );

                // Automatically bottom scroll
                chatBox.scrollTop =
                    chatBox.scrollHeight;
            }

        });

});


function escapeHtml(text)
{
    const div = document.createElement('div');

    div.textContent = text;

    return div.innerHTML;
}
</script>

@endsection
