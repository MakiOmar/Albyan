<div class="form-group col-md-3 col-12 d-none">
    <label for="meeting_end_time">End Time</label>
    <small class="form-text text-muted">Select the time the meeting will end (for recurring meetings).</small>
    <input type="time" name="meeting_end_time" id="meeting_end_time" class="form-control"
        value="{{ old('meeting_end_time', $isEdit ? \Carbon\Carbon::parse($group->meeting_end_time)->format('H:i') : '22:00') }}">
</div>