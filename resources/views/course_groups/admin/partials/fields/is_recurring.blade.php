<div class="form-group col-md-6 col-12">
    <label for="meeting_recurring">Recurring</label>
    <small class="form-text text-muted">
        Is this a recurring meeting?
        <i class="fas fa-info-circle ml-1" 
           data-toggle="tooltip" 
           data-placement="top"
           title="Recurring meetings repeat automatically at regular intervals.&#013;&#013;Choose 'Yes' if this meeting should repeat daily, weekly, or monthly.&#013;Choose 'No' for a one-time meeting."></i>
    </small>
    <select name="meeting_recurring" id="meeting_recurring" class="form-control">
        <option value="1" {{ old('meeting_recurring', $isEdit ? $group->meeting_recurring : '') == '1' ? 'selected' : '' }}>Yes</option>
        <option value="0" {{ old('meeting_recurring', $isEdit ? $group->meeting_recurring : '') == '0' ? 'selected' : '' }}>No</option>
    </select>
</div>

<style>
    .tooltip {
        z-index: 99999 !important;
    }
    .tooltip-inner {
        max-width: 300px;
        text-align: left;
        white-space: pre-line;
        background-color: #2c3e50;
    }
    .bs-tooltip-auto[x-placement^=top] .arrow::before, 
    .bs-tooltip-top .arrow::before {
        border-top-color: #2c3e50;
    }
</style>