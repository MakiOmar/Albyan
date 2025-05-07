<div class="form-group col-md-3 col-12">
    <label for="meeting_start_date">Start Date</label>
    <small class="form-text text-muted">Select the date the meeting <br>will start.</small>
    <input type="date" name="meeting_start_date" id="meeting_start_date" class="form-control"
        value="{{ old('meeting_start_date', $isEdit ? \Carbon\Carbon::parse($group->meeting_start_time)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
</div>