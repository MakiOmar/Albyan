<div class="form-group col-md-6 col-12">
    <label for="recurrence_type">Recurrence Type</label>
    <small class="form-text text-muted">Choose how often the meeting should repeat: daily, weekly, or monthly.</small>
    <select name="recurrence_type" id="recurrence_type" class="form-control">
        <option value="1" {{ $isEdit && ($meetingJson['recurrence']['type'] ?? '') == 1 ? 'selected' : '' }}>Daily</option>
        <option value="2" {{ $isEdit && ($meetingJson['recurrence']['type'] ?? '') == 2 ? 'selected' : '' }}>Weekly</option>
        <option value="3" {{ $isEdit && ($meetingJson['recurrence']['type'] ?? '') == 3 ? 'selected' : '' }}>Monthly</option>
    </select>
</div>