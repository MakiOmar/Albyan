@extends('admin.layouts.app')

@section('content')
@section('content')
<section class="section">
    <div class="section-header">
        <h1>عرض المجموعات حسب الويبينار</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <label for="webinar_id">اختر الدورة</label>
            <select name="webinar_id" id="webinar_id" class="form-control select2">
                <option value="">-- اختر الدورة --</option>
                @foreach($webinars as $webinar)
                    <option value="{{ $webinar->id }}">{{ $webinar->title ?? 'Webinar #' . $webinar->id }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div id="groups-container"></div>
</section>

@include('course_groups.admin.partials.add_student_modal')
@endsection

@push('scripts_bottom')
<script>
    jQuery(document).ready(function ($) {
        $('.select2').select2();

        $('#webinar_id').on('change', function () {
            const webinarId = $(this).val();

            if (!webinarId) {
                $('#groups-container').html('');
                return;
            }

            $.ajax({
                url: `/admin/course-group/ajax/webinar/${webinarId}/groups-html`,
                method: 'GET',
                beforeSend: function () {
                    $('#groups-container').html('<div class="text-center my-4"><div class="spinner-border text-primary"></div></div>');
                },
                success: function (html) {
                    $('#groups-container').html(html);

                    // Rebind select2 for student modal if loaded
                    $('#studentSelect').select2({
                        dropdownParent: $('#addStudentModal')
                    });

                    // Rebind modal open buttons
                    $('.add-student-btn').on('click', function () {
                        const groupId = $(this).data('group-id');
                        $('#groupId').val(groupId);
                    });
                },
                error: function () {
                    $('#groups-container').html('<div class="alert alert-danger">فشل في تحميل المجموعات</div>');
                }
            });
        });

        $('.add-student-btn').on('click', function () {
            const groupId = $(this).data('group-id');
            $('#groupId').val(groupId);
        });

        $(document).on('submit', '#addStudentForm',function (e) {
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
                        $('#addStudentModal').modal('hide');
                        $('#addStudentForm')[0].reset();

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
