<div class="form-group col-12" id="weekly_days_wrapper" style="display: none;">
    <label for="weekly_days">Select Days of the Week (Weekly Recurrence)</label>
    <small class="form-text text-muted">Choose the days on which the meeting should occur. Hold Ctrl (or Cmd) to select multiple days.</small>
    @php
        $selectedDays = old('weekly_days', $isEdit && isset($meetingJson['recurrence']['weekly_days']) ? explode(',', $meetingJson['recurrence']['weekly_days']) : []);
        $selectedDays = array_map('intval', $selectedDays); // لتجنب تعارض types عند in_array
    @endphp

    <select name="weekly_days[]" id="weekly_days" class="form-control" multiple>
        <option value="1" {{ in_array(1, $selectedDays) ? 'selected' : '' }}>Sunday</option>
        <option value="2" {{ in_array(2, $selectedDays) ? 'selected' : '' }}>Monday</option>
        <option value="3" {{ in_array(3, $selectedDays) ? 'selected' : '' }}>Tuesday</option>
        <option value="4" {{ in_array(4, $selectedDays) ? 'selected' : '' }}>Wednesday</option>
        <option value="5" {{ in_array(5, $selectedDays) ? 'selected' : '' }}>Thursday</option>
        <option value="6" {{ in_array(6, $selectedDays) ? 'selected' : '' }}>Friday</option>
        <option value="7" {{ in_array(7, $selectedDays) ? 'selected' : '' }}>Saturday</option>
    </select>
</div>