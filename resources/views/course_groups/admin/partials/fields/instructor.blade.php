<div class="form-group col-md-4 col-12">
    <label for="instructor_id">Select Instructor</label>
    <span class="d-flex justify-content-between">
        <small class="form-text text-muted">Choose the instructor for this group.</small><button type="button" class="btn btn-primary" style="position: absolute;top: 0;left: 15px;" data-toggle="modal" data-target="#instructorGroupsModal">
            عرض المواعيد
        </button>
    </span>
    <select name="teacher_id" id="teacher_id" class="form-control select2">
        <option value="">-- Select Instructor --</option>
        @foreach ($instructors as $instructor)
            <option value="{{ $instructor->id }}"
                {{ old('teacher_id', $isEdit ? $group->instructor_id : '') == $instructor->id ? 'selected' : '' }}>
                {{ $instructor->full_name }}
            </option>
        @endforeach
    </select>

</div>