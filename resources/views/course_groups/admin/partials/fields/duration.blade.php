<div class="form-group col-md-4 col-12">
    <label for="meeting_duration">Duration (hours)</label>
    <small class="form-text text-muted">Enter the duration of the meeting in hours.</small>
    <input type="text" name="meeting_duration" id="meeting_duration" class="form-control" value="{{ old('meeting_duration', $isEdit ? $group->meeting_duration / 60 : 1) }}" required>
</div>