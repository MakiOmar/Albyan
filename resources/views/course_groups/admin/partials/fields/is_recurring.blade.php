<div class="form-group col-md-6 col-12">
    <label for="meeting_recurring">Recurring</label>
    
    <small class="form-text text-muted">Is this a recurring meeting?</small>
    <select name="meeting_recurring" id="meeting_recurring" class="form-control">
        <option value="1" {{ old('meeting_recurring', $isEdit ? $group->meeting_recurring : '') == '1' ? 'selected' : '' }}>Yes</option>
        <option value="0" {{ old('meeting_recurring', $isEdit ? $group->meeting_recurring : '') == '0' ? 'selected' : '' }}>No</option>
    </select>

</div>