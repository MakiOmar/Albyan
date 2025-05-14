<div class="form-group col-md-6 col-12">
    <label for="recurrence_type">Recurrence Type</label>
    <small class="form-text text-muted">
        Choose how often the meeting should repeat: daily, weekly, or monthly.
        <i class="fas fa-info-circle ml-1" 
           data-toggle="tooltip" 
           title="Select recurrence pattern:&#013;&#013;• Daily: Repeats every X days&#013;• Weekly: Repeats every X weeks on same day&#013;• Monthly: Repeats every X months on same date"></i>
    </small>
    <select name="recurrence_type" id="recurrence_type" class="form-control">
        <option value="">حدد نوع التكرار</option>
        <option value="1" {{ old('recurrence_type', $isEdit ? ($meetingJson['recurrence']['type'] ?? '') : '') == '1' ? 'selected' : '' }}>Daily</option>
        <option value="2" {{ (old('recurrence_type', $isEdit ? ($meetingJson['recurrence']['type'] ?? '2') : '2') == '2') ? 'selected' : '' }}>Weekly</option>
        <option value="3" {{ old('recurrence_type', $isEdit ? ($meetingJson['recurrence']['type'] ?? '') : '') == '3' ? 'selected' : '' }}>Monthly</option>
    </select>
</div>