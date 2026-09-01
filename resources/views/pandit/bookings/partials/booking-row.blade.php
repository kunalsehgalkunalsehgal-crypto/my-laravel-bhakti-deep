<article class="pandit-booking-row">
    <div>
        <span class="pandit-status-pill">{{ $session['label'] }}</span>
        <h3>{{ $session['service_name'] }}</h3>
        <p>{{ $session['booking_id'] }} - {{ $session['yajman'] }}</p>
    </div>
    <div>
        <span>Date</span>
        <strong>{{ $session['booking_date']?->format('d M Y') ?? 'Pending' }}</strong>
    </div>
    <div>
        <span>Slot</span>
        <strong>{{ $session['slot'] ?: 'Pending' }}</strong>
    </div>
    <div>
        <span>Package</span>
        <strong>{{ $session['package_name'] ?: $session['label'] }}</strong>
    </div>
    <div>
        <span>Payment Status</span>
        <strong>{{ ucfirst(str_replace('_', ' ', $session['payment_status'] ?? 'pending')) }}</strong>
    </div>
    <div>
        <span>Status</span>
        <strong>{{ ucfirst(str_replace('_', ' ', $session['status'])) }}</strong>
    </div>
    <div>
        <span>Dakshina</span>
        <strong>Rs {{ number_format($session['dakshina']) }}</strong>
    </div>
    <div class="pandit-booking-actions">
        <a href="{{ $session['detail_url'] }}" class="secondary">
            <i class="bi bi-eye"></i> View Details
        </a>
        @if($session['can_accept'])
            <form method="POST" action="{{ $session['accept_url'] }}">
                @csrf
                <button type="submit" class="primary">
                    <i class="bi bi-check2-circle"></i> Accept
                </button>
            </form>
        @elseif($session['can_start_meeting'])
            <a href="{{ $session['meeting_start_url'] }}" class="primary">
                <i class="bi bi-camera-video"></i> Start
            </a>
        @else
            <span class="pandit-muted-action">Live link pending</span>
        @endif
        @if($session['can_cancel'])
            <form method="POST" action="{{ $session['cancel_url'] }}" data-pandit-cancel-form>
                @csrf
                <input type="hidden" name="reason">
                <button type="submit" class="secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
            </form>
        @endif
    </div>
</article>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-pandit-cancel-form]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const ok = confirm('Full refund will go to user.\nPandit gets no payout.\nSame Pandit slot stays blocked until original slot ends.');
                    if (!ok) return;
                    const reason = prompt('Please enter cancellation reason.');
                    if (!reason || !reason.trim()) return;
                    form.querySelector('[name="reason"]').value = reason.trim();
                    form.submit();
                });
            });
        </script>
    @endpush
@endonce
