<div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">إضافة طالب إلى المجموعة</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addStudentForm">
                <div class="modal-body">
                    <input type="hidden" id="groupId" name="group_id">
                    <div class="form-group">
                        <label for="studentSelect">اختر الطالب</label>
                        <select class="form-control" id="studentSelect" name="student_id" required>
                            <option value="">-- اختر الطالب --</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">إضافة الطالب</button>
                </div>
            </form>
        </div>
    </div>
</div>
