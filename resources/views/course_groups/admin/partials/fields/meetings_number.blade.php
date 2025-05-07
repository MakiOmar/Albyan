<div class="form-group col-md-6 col-12">
    <label for="end_times">Number of Meetings</label>
    <small class="form-text text-muted">Enter the number of meetings (e.g., 6 meetings between the specified dates).</small>
    <input type="number" name="end_times" id="end_times" class="form-control"
        value="{{ old('end_times', $isEdit ? ($meetingJson['recurrence']['end_times'] ?? 1) : 1) }}" required>
</div>