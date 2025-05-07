<div class="form-group col-md-3 col-12">
    <label for="meeting_start_time">Start Time</label>
    <small class="form-text text-muted">Select the time the meeting <br>will start.</small>
    <input type="time" name="meeting_start_time" id="meeting_start_time" class="form-control"
        value="{{ old('meeting_start_time', $isEdit ? \Carbon\Carbon::parse($group->meeting_start_time)->format('H:i') : '10:00') }}" required>
</div>