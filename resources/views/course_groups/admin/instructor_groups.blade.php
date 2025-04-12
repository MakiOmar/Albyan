@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@push('styles_top')
<style>
    .border-style {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .flex-item {
        padding: 0.5rem;
        border-left: 2px dashed #ccc; /* خط متقطع على اليسار */
        border-right: 2px dashed #ccc; /* خط متقطع على اليمين */
        flex: 1; /* توزيع العناصر بالتساوي */
        text-align: center;
    }
    
    /* إزالة الحدود اليسرى لأول عنصر واليمنى لآخر عنصر */
    .flex-item:first-child {
        border-left: none;
    }
    
    .flex-item:last-child {
        border-right: none;
    }

</style>
@endpush

@section('content')
<section class="section" id="webinar-groups-list">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="section-header">
        <h1>مجموعات المحاضرين</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/admin">لوحة التحكم</a></div>
            <div class="breadcrumb-item"><a href="/admin/course-group/webinar-groups">الدورات مع المجموعات</a></div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <label for="instructor_id">Select Instructor</label>
            <select name="teacher_id" id="teacher_id" class="form-control select2">
                <option value="">-- Select Instructor --</option>
                @foreach ($instructors as $instructor)
                    <option value="{{ $instructor->id }}">{{ $instructor->full_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- المجموعات -->
            <h5>المجموعات</h5>
            @if ( $groups->isNotEmpty() )
            <div id="groupsAccordion">
                @foreach ($groups as $group)
                <div class="card">
                    <div class="card-header d-flex justify-content-between" id="heading{{ $group->id }}">
                        <h2 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse{{ $group->id }}" aria-expanded="false" aria-controls="collapse{{ $group->id }}">
                                المجموعة: {{ $group->id }} - معرف الاجتماع: {{ $group->meeting_id }}
                            </button>
                            <button class="btn btn-primary btn-sm add-student-btn" data-group-id="{{ $group->id }}" data-toggle="modal" data-target="#addStudentModal">
                                إضافة طالب
                            </button>
                            <a href="{{ route('course-group.create-form', ['groupId' => $group->id]) }}" class="btn btn-warning btn-sm">
                                تعديل بيانات المجموعة
                            </a>
                        </h2>

                        <!-- Delete Button -->
                        <form method="POST" class="m-0" action="{{ route('course-group.destroy', ['group' => $group->id]) }}" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذه المجموعة؟')">
                                حذف المجموعة
                            </button>
                        </form>
                    </div>
                    <div id="collapse{{ $group->id }}" class="collapse" aria-labelledby="heading{{ $group->id }}" data-parent="#groupsAccordion">
                        <div class="card-body">
                            <h5 class="mb-4">{{ $group->webinar->title }}</h3>
                            <div class="d-flex justify-content-between align-items-center flex-wrap border-style">
                                <div class="flex-item px-3">
                                    <strong>وقت بدء الدورة:</strong> {{ $group->meeting_start_time }}
                                </div>
                                <div class="flex-item px-3">
                                    <strong>وقت نهاية الدورة:</strong> {{ $group->meeting_end_time }}
                                </div>
                                <div class="flex-item px-3">
                                    <strong>المحاضر:</strong> {{ $group->instructor->full_name }}
                                </div>
                                <div class="flex-item px-3">
                                    <strong>عدد الطلاب:</strong> {{ $group->members->count() }}
                                </div>
                            </div>


                            <!-- جدول الطلاب -->
                            <div class="px-3 mt-4">
                                <h6>الطلاب</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>اسم الطالب</th>
                                                <th>البريد الإلكتروني</th>
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
                                                            @csrf
                                                            @method('DELETE')
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
                </div>
                @endforeach
            </div>
            @else
            <p class="bg-info p-4 rounded text-white fw-bold">No Groups found, Please select another instructor.</p>
            @endif
        </div>
    </div>
</section>
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


@endsection
@push('scripts_bottom')
<script>
    $(document).ready(function () {
        $('#teacher_id').on('change', function () {
            const instructorId = $(this).val();
            const currentUrl = window.location.origin + window.location.pathname;

            if (instructorId) {
                // Redirect with instructor_id as query param
                window.location.href = currentUrl + '?instructor_id=' + instructorId;
            } else {
                // Remove query param if nothing selected
                window.location.href = currentUrl;
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#studentSelect').select2({
            dropdownParent: $('#addStudentModal')
        });
        // فتح النافذة وتعيين معرف المجموعة
        $('.add-student-btn').on('click', function () {
            const groupId = $(this).data('group-id');
            $('#groupId').val(groupId);
        });

        // معالجة إرسال النموذج
        $('#addStudentForm').on('submit', function (e) {
            e.preventDefault();

            const groupId = $('#groupId').val();
            const studentId = $('#studentSelect').val();

            $.ajax({
                url: `{{ route('group.student.add', ':groupId') }}`.replace(':groupId', groupId),
                method: 'POST',
                data: {
                    student_id: studentId,
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    if (response.success) {
                        // إغلاق النافذة وإعادة تعيين النموذج
                        $('#addStudentModal').modal('hide');
                        $('#addStudentForm')[0].reset();

                        // إضافة صف الطالب الجديد إلى الجدول
                        const newRow = `
                            <tr>
                                <td>${response.student_name}</td>
                                <td>${response.student_email}</td>
                                <td>
                                    <form method="POST" action="{{ route('group.student.remove', ['group' => ':groupId', 'student' => ':studentId']) }}"
                                          data-group-id="${response.group_id}" data-student-id="${response.student_id}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                    </form>
                                </td>
                            </tr>`;
                        $(`#collapse${response.group_id} .table tbody`).append(newRow);
                        // عرض رسالة نجاح
                        Swal.fire({
                            icon: 'success',
                            title: 'تم',
                            text: `تمت إضافة ${response.student_name} بنجاح`,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: response.message || 'فشلت عملية الإضافة',
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'فشلت عملية الإضافة',
                    });
                    console.log('An error occurred: ' + (xhr.responseJSON?.message || xhr.statusText));
                },
            });
        });
    });
</script>
@endpush
