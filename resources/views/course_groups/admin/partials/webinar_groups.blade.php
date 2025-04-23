@if ($groupsByInstructor->isNotEmpty())
    <div class="accordion" id="accordionByInstructor">
        @foreach ($groupsByInstructor as $instructorId => $groups)
            @php
                $instructor = $groups->first()->instructor;
                $instructorName = $instructor?->full_name ?? 'غير معروف';
            @endphp

            <div class="card">
                <div class="card-header" id="headingInstructor{{ $instructorId }}">
                    <h2 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseInstructor{{ $instructorId }}" aria-expanded="true" aria-controls="collapseInstructor{{ $instructorId }}">
                            المحاضر: {{ $instructorName }}
                        </button>
                    </h2>
                </div>

                <div id="collapseInstructor{{ $instructorId }}" class="collapse show" aria-labelledby="headingInstructor{{ $instructorId }}" data-parent="#accordionByInstructor">
                    <div class="card-body">
                        @foreach ($groups as $group)
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
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info">لا توجد مجموعات متاحة لهذا الويبينار.</div>
@endif
