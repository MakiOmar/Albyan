<div class="form-group col-md-6 col-12">
    <label for="meeting_end_date">End Date</label>
    <small class="form-text text-muted">Select the date the meeting will end<br> (for recurring meetings).</small>
    <input type="date" name="meeting_end_date" id="meeting_end_date" class="form-control"
        value="{{ old('meeting_end_date', $isEdit ? \Carbon\Carbon::parse($group->meeting_end_time)->format('Y-m-d') : now()->addDay()->format('Y-m-d')) }}">
</div>