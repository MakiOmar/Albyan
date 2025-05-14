<div class="form-group col-md-6 col-12">
    <label for="recurrence_interval">Recurrence Interval</label>
    <small id="recurrence_interval_help" class="form-text text-muted">
        Enter the number of intervals (e.g., every 2 days for daily recurrence).
        <i class="fas fa-info-circle ml-1" 
           data-toggle="tooltip" 
           title="Determines how often the meeting repeats. Examples:&#013;&#013;• Daily: 2 = every 2 days&#013;• Weekly: 3 = every 3 weeks&#013;• Monthly: 6 = every 6 months"></i>
    </small>
    <input type="number" 
           name="recurrence_interval" 
           id="recurrence_interval" 
           class="form-control"
           min="1"
           value="{{ old('recurrence_interval', $isEdit ? ($meetingJson['recurrence']['repeat_interval'] ?? 1) : 1) }}" 
           required
           aria-describedby="recurrence_interval_help">
</div>