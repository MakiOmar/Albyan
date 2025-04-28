<div class="card my-3 border border-secondary">
    <div class="card-header d-flex justify-content-between">
        <strong>المجموعة #{{ $group->id }} - الاجتماع: {{ $group->meeting_id }}</strong>
        <div>
            <a href="{{ route('course-group.create-form', ['groupId' => $group->id]) }}" class="btn btn-warning btn-sm">تعديل</a>
            <button class="btn btn-primary btn-sm add-student-btn" data-group-id="{{ $group->id }}" data-toggle="modal" data-target="#addStudentModal">إضافة طالب</button>
            <form method="POST" action="{{ route('course-group.destroy', $group->id) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد؟')">حذف</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between border-style">
            <div class="flex-item"><strong>بداية:</strong> {{ $group->meeting_start_time }}</div>
            <div class="flex-item"><strong>نهاية:</strong> {{ $group->meeting_end_time }}</div>
            <div class="flex-item"><strong>الطلاب:</strong> {{ $group->members->count() }}</div>
        </div>

        <div class="mt-3">
            <h6>الطلاب</h6>
            <div id="collapse{{ $group->id }}" class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>الإيميل</th>
                            <th>الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($group->members as $member)
                            <tr>
                                <td>{{ $member->student->full_name }}</td>
                                <td>{{ $member->student->email }}</td>
                                <td>
                                    <form method="POST" action="{{ route('group.student.remove', ['group' => $group->id, 'student' => $member->student->id]) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>