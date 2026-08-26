@extends('layouts.pandit-dashboard')

@section('title', 'Messages - BhaktiDeep')
@php $activeMenu = 'messages'; @endphp

@section('content')

<div class="pandit-page-heading">
    <div>
        <p>Direct communication with Admin</p>
        <h1>Messages</h1>
    </div>
</div>

@if(session('success'))
    <div style="background:#e8f7f1;border:1px solid #a6e7c8;border-radius:12px;padding:12px 16px;margin-bottom:16px;color:#067647;font-weight:700">
        {{ session('success') }}
    </div>
@endif

{{-- Chat Box --}}
<section class="pandit-panel" style="padding:0;overflow:hidden">

    {{-- Messages List --}}
    <div id="chatBox" style="padding:20px;display:grid;gap:12px;max-height:480px;overflow-y:auto">

        @forelse($messages as $msg)

            @if($msg->sender === 'pandit')
                {{-- Pandit message - right side --}}
                <div style="display:flex;justify-content:flex-end;gap:10px;align-items:flex-end">
                    <div style="max-width:70%">
                        <div style="background:linear-gradient(135deg,#e85d04,#c84218);color:#fff;border-radius:18px 18px 4px 18px;padding:12px 16px;font-size:14px;font-weight:600">
                            {{ $msg->message }}
                        </div>
                        <div style="display:flex;justify-content:flex-end;align-items:center;gap:8px;margin-top:4px">
                            <small style="color:var(--muted);font-size:11px">{{ $msg->created_at->format('d M, h:i A') }}</small>
                            <form method="POST" action="{{ route('pandit.messages.delete', $msg->id) }}" onsubmit="return confirm('Delete this message?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:#e85d04;cursor:pointer;font-size:12px;padding:0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--saffron));display:grid;place-items:center;color:#fff;font-size:14px;flex-shrink:0">
                        <i class="bi bi-person"></i>
                    </div>
                </div>

            @else
                {{-- Admin message - left side --}}
                <div style="display:flex;gap:10px;align-items:flex-end">
                    <div style="width:36px;height:36px;border-radius:50%;background:#151924;display:grid;place-items:center;color:#fff;font-size:14px;flex-shrink:0">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div style="max-width:70%">
                        <small style="color:var(--gold);font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.08em">Admin</small>
                        <div style="background:rgba(255,255,255,0.8);border:1px solid rgba(199,141,34,0.22);border-radius:18px 18px 18px 4px;padding:12px 16px;font-size:14px;font-weight:600;color:var(--cream);margin-top:4px">
                            {{ $msg->message }}
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:4px">
                            <small style="color:var(--muted);font-size:11px">{{ $msg->created_at->format('d M, h:i A') }}</small>
                            <form method="POST" action="{{ route('pandit.messages.delete', $msg->id) }}" onsubmit="return confirm('Delete this message?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:#e85d04;cursor:pointer;font-size:12px;padding:0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

        @empty
            <div style="text-align:center;padding:40px;color:var(--muted)">
                <i class="bi bi-chat-dots" style="font-size:40px;display:block;margin-bottom:12px;color:var(--gold)"></i>
                <p style="margin:0">No messages yet. Send a message to Admin!</p>
            </div>
        @endforelse

    </div>

    {{-- Send Message Form --}}
    <div style="border-top:1px solid rgba(199,141,34,0.2);padding:16px 20px;background:rgba(251,244,223,0.5)">
        <form method="POST" action="{{ route('pandit.messages.send') }}" style="display:flex;gap:10px;align-items:flex-end">
            @csrf
            <textarea name="message" rows="2" placeholder="Type your message to Admin..." required
                style="flex:1;border:1px solid rgba(199,141,34,0.26);border-radius:14px;background:rgba(255,255,255,0.8);color:var(--cream);font:inherit;font-weight:600;padding:11px 14px;resize:none;font-size:14px"></textarea>
            <button type="submit" class="pandit-submit-btn compact" style="min-height:52px;padding:0 20px">
                <i class="bi bi-send"></i> Send
            </button>
        </form>
    </div>

</section>

{{-- <script>
    // Auto scroll to bottom
    const chatBox = document.getElementById('chatBox');
    chatBox.scrollTop = chatBox.scrollHeight;
</script> --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const chatBox = document.getElementById('chatBox');

    // Page open hote hi bottom par scroll
    chatBox.scrollTop = chatBox.scrollHeight;

    // Current logged-in Pandit ID
    const panditId = {{ $pandit->id }};

    console.log('Pandit WebSocket Channel:', `pandit.chat.${panditId}`);


    // Private WebSocket channel listen
    window.Echo
        .private(`pandit.chat.${panditId}`)
        .listen('.pandit.message.sent', function (event) {

            console.log('Live message received:', event);

            const msg = event.message;


            // Pandit page par sirf Admin ka incoming message add karo
            if (msg.sender !== 'admin') {
                return;
            }


            const html = `
                <div style="display:flex;gap:10px;align-items:flex-end">

                    <div style="
                        width:36px;
                        height:36px;
                        border-radius:50%;
                        background:#151924;
                        display:grid;
                        place-items:center;
                        color:#fff;
                        font-size:14px;
                        flex-shrink:0;
                    ">
                        <i class="bi bi-shield-check"></i>
                    </div>


                    <div style="max-width:70%">

                        <small style="
                            color:var(--gold);
                            font-size:11px;
                            font-weight:900;
                            text-transform:uppercase;
                            letter-spacing:.08em;
                        ">
                            Admin
                        </small>


                        <div style="
                            background:rgba(255,255,255,0.8);
                            border:1px solid rgba(199,141,34,0.22);
                            border-radius:18px 18px 18px 4px;
                            padding:12px 16px;
                            font-size:14px;
                            font-weight:600;
                            color:var(--cream);
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


            chatBox.insertAdjacentHTML(
                'beforeend',
                html
            );


            // New message ke baad automatically bottom
            chatBox.scrollTop = chatBox.scrollHeight;

        });

});


// Message me HTML/script inject na ho
function escapeHtml(text)
{
    const div = document.createElement('div');

    div.textContent = text;

    return div.innerHTML;
}
</script>

@endsection
