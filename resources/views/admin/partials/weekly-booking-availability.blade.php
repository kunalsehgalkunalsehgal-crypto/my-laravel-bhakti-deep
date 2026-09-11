@php
    $fallbackSlots = $record instanceof \App\Models\Admin\Pooja ? ['7:00 AM - 8:00 AM', '12:00 PM - 1:00 PM', '6:00 PM - 7:00 PM'] : [];
    $weeklyAvailability = \App\Support\WeeklyBookingAvailability::normalize(old('available_slots', $record->available_slots ?: $fallbackSlots));
    $days = \App\Support\WeeklyBookingAvailability::DAYS;
@endphp

<div class="full" style="border:1px solid #eadfd3;border-radius:10px;padding:18px;margin-top:8px;" data-weekly-availability>
    <label style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
        <input type="hidden" name="available_slots[accept_new_bookings]" value="0">
        <input style="width:auto" type="checkbox" name="available_slots[accept_new_bookings]" value="1" @checked($weeklyAvailability['accept_new_bookings'])>
        Accept New Bookings
    </label>

    @foreach($days as $day)
        @php $dayData = $weeklyAvailability['days'][$day] ?? ['available' => false, 'slots' => []]; @endphp
        <div style="border-top:1px solid #f1e7dc;padding-top:14px;margin-top:14px;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <strong>{{ $day }}</strong>
                <select name="available_slots[days][{{ $day }}][available]" style="max-width:180px;">
                    <option value="1" @selected($dayData['available'])>Available</option>
                    <option value="0" @selected(! $dayData['available'])>Unavailable</option>
                </select>
            </div>

            <div data-slots style="display:grid;gap:10px;margin-top:12px;">
                @forelse($dayData['slots'] as $index => $slot)
                    <div data-slot-row style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
                        <label style="margin:0;">From<input type="time" name="available_slots[days][{{ $day }}][slots][{{ $index }}][from]" value="{{ $slot['from'] }}"></label>
                        <label style="margin:0;">To<input type="time" name="available_slots[days][{{ $day }}][slots][{{ $index }}][to]" value="{{ $slot['to'] }}"></label>
                        <button class="btn small" type="button" data-remove-slot>Remove</button>
                    </div>
                @empty
                    <small data-empty-slot style="color:#8a6d52;">No slots added.</small>
                @endforelse
            </div>

            <button class="btn small" type="button" data-add-slot data-day="{{ $day }}" style="margin-top:10px;">Add Time Slot</button>
        </div>
    @endforeach
    @error('available_slots') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
</div>

@once
    @push('scripts')
        <script>
        document.addEventListener('click', function (event) {
            const add = event.target.closest('[data-add-slot]');
            const remove = event.target.closest('[data-remove-slot]');

            if (add) {
                const wrap = add.parentElement.querySelector('[data-slots]');
                const day = add.dataset.day;
                const index = Date.now();
                wrap.querySelector('[data-empty-slot]')?.remove();
                wrap.insertAdjacentHTML('beforeend', `<div data-slot-row style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
                    <label style="margin:0;">From<input type="time" name="available_slots[days][${day}][slots][${index}][from]" value="08:00"></label>
                    <label style="margin:0;">To<input type="time" name="available_slots[days][${day}][slots][${index}][to]" value="10:00"></label>
                    <button class="btn small" type="button" data-remove-slot>Remove</button>
                </div>`);
            }

            if (remove) {
                const wrap = remove.closest('[data-slots]');
                remove.closest('[data-slot-row]').remove();
                if (!wrap.querySelector('[data-slot-row]')) {
                    wrap.innerHTML = '<small data-empty-slot style="color:#8a6d52;">No slots added.</small>';
                }
            }
        });
        </script>
    @endpush
@endonce
