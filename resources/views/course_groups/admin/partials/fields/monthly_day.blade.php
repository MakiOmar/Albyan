<div class="form-group col-12" id="monthly_day_wrapper" style="display: none;">
    <label for="monthly_day">Day of the Month (Monthly Recurrence)</label>
    <small class="form-text text-muted">Enter the specific day of the month (e.g., 15 for the 15th day) when the meeting should occur.</small>
    <input type="number" name="monthly_day" id="monthly_day" class="form-control" min="1" max="31" value="{{ old('monthly_day', $isEdit ? ($meetingJson['recurrence']['monthly_day'] ?? '') : '') }}">
</div>