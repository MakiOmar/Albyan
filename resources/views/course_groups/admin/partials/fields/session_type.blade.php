<div class="form-group col-md-6 col-12">
    <label for="session_type">Session Type</label>
    <small class="form-text text-muted">Choose if the session is Online (Zoom) or Offline (in-person).</small>
    <select name="session_type" id="session_type" class="form-control select2">
        <option value="zoom" {{ $isEdit && ($group->session_type ?? 'zoom') == 'zoom' ? 'selected' : '' }}>Zoom Online</option>
        <option value="offline" {{ $isEdit && ($group->session_type ?? 'zoom') == 'offline' ? 'selected' : '' }}>Offline (In-Person)</option>
    </select>
</div>