@php
    $start = \Carbon\Carbon::createFromTime(9, 0);
    $end = \Carbon\Carbon::createFromTime(22, 0);
    $times = [];

    while ($start <= $end) {
        $times[] = $start->format('H:i');
        $start->addMinutes(30);
    }

    $selectedTime = old('meeting_start_time', $isEdit ? \Carbon\Carbon::parse($group->meeting_start_time)->format('H:i') : '09:00');
@endphp

<div class="form-group col-md-3 col-12">
    <label for="meeting_start_time">Meeting Start Time</label>
    <small class="form-text text-muted">Select the time the meeting <br>will start.</small>
    <select name="meeting_start_time" id="meeting_start_time" class="form-control" required>
        @foreach ($times as $time)
            <option value="{{ $time }}" {{ $selectedTime == $time ? 'selected' : '' }}>
                {{ \Carbon\Carbon::createFromFormat('H:i', $time)->format('h:i A') }}
            </option>
        @endforeach
    </select>
</div>
