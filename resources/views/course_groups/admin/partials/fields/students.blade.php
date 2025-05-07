<div class="row">
    <!-- Students -->
    <div class="form-group col-md-12">
        <label for="student_ids">Select Students</label>
        <small class="form-text text-muted">Choose the students who will be part of this group. You can select multiple students.</small>
        @if( !$isEdit )
        <select name="student_ids[]" id="student_ids" class="form-control" multiple>
            <option value="">Please select a webinar first</option>
        </select>
        @else

        <select name="student_ids[]" id="student_ids" class="form-control" multiple>
            @foreach ($allStudents as $student)
                <option value="{{ $student->id }}"
                    {{ $isEdit && in_array($student->id, $group->members->pluck('student_id')->toArray()) ? 'selected' : '' }}>
                    {{ $student->full_name }}
                </option>
            @endforeach
        </select>                
        @endif
    </div>
</div>