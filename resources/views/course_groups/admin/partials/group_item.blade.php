@php
    $meetingType = json_decode($group->meeting_json, true)['recurrence']['type'] ?? 'regular';
@endphp
@push('styles_top')
    <style>
        .meeting-details{
            display: flex;
            justify-content: space-between;
            align-items: center;
            width:100%;
            padding: 10px;
            background-color: #f6f6f6
        }
    </style>
@endpush
<div class="card my-3 border border-secondary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>المجموعة #{{ $group->id }} - الاجتماع: {{ $group->meeting_id }}</strong>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ $meetingType === 'variable' 
                        ? route('course-group.create-variable-form', ['groupId' => $group->id]) 
                        : route('course-group.create-form', ['groupId' => $group->id]) }}"
            class="btn btn-warning btn-sm">
                تعديل
            </a>

            <button class="btn btn-primary btn-sm add-student-btn" data-group-id="{{ $group->id }}" data-toggle="modal" data-target="#addStudentModal">إضافة طالب</button>
            <button class="btn btn-success btn-sm add-makeup-session-btn" 
                data-group-id="{{ $group->id }}"
                data-group-name="{{ $group->webinar->title ?? 'دورة بدون اسم' }}"
                data-session-type="{{ $group->session_type }}"
                data-last-date="{{ optional(collect(json_decode($group->meeting_json, true)['occurrences'] ?? [])->last())['start_time'] }}"
                data-toggle="modal" 
                data-target="#makeupSessionModal">
                إضافة جلسة تعويضية
            </button>
            <form method="POST" action="{{ route('course-group.destroy', $group->id) }}" class="d-inline m-0">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد؟')">حذف</button>
            </form>
        </div>
    </div>

    <div class="card-body">
        <div class="bg-primary text-white p-10 d-flex justify-content-between border-style mb-3">
            <div><strong>بداية:</strong> {{ $group->meeting_start_time }}</div>
            <div><strong>نهاية:</strong> {{ $group->meeting_end_time }}</div>
            <div><strong>الطلاب:</strong> {{ $group->members->count() }}</div>
        </div>
        @if ( ! $group->members->isEmpty() )
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
        @endif
        <div class="px-3 mt-4">
            <hr>
            <h3 class="text-primary"><i class="fa fa-bookmark"></i> قائمة المحاضرات</h3>
            <div class="student-meetings pt-2">
                @include('web.default.course.learningPage.components.group_meetings', ['group' => $group, 'occurrences' => $occurrences, 'user' => auth()->user()])
            </div>
        </div>
    </div>
</div>