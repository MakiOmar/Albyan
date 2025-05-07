<div class="form-group col-md-6 col-12">
    <label for="recurrence_interval">Recurrence Interval</label>
    <small class="form-text text-muted">Enter the number of intervals (e.g., every 2 days for daily recurrence).</small>
    <input type="number" name="recurrence_interval" id="recurrence_interval" class="form-control" value="{{ old('recurrence_interval', $isEdit ? ($meetingJson['recurrence']['repeat_interval'] ?? 1) : 1) }}" required>
</div>