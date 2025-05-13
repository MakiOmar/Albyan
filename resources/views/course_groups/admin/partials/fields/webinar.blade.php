<div class="form-group col-md-4 col-12">
    <label for="webinar_id">Select Webinar</label>
    <small class="form-text text-muted">Choose the webinar for which you want to create a group.</small>
    <select name="webinar_id" id="webinar_id" class="form-control select2">
        @foreach ($webinars as $webinar)
        <option value="{{ $webinar->id }}" {{ old('webinar_id', $isEdit ? $group->webinar_id : '') == $webinar->id ? 'selected' : '' }}>

            {{ $webinar->title }}</option>
        @endforeach
    </select>
</div>