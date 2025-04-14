@extends('admin.layouts.app')

@push('libraries_top')

@endpush
@push('styles_top')
<style>
    #groupsModalContent .fade{
        opacity: 1;
    }
</style>
@endpush
@php
    $values = !empty($setting) ? $setting->value : null;

    if (!empty($values)) {
        $values = json_decode($values, true);
    }
    $isEdit = isset($group);
    $meetingJson = $isEdit ? json_decode($group->meeting_json, true) : null;
@endphp


@section('content')
<div class="container">
    <h1>Create a New Group</h1>

    <!-- Display Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="create-group-form" action="{{ $isEdit ? route('course-group.update', $group->id) : route('course-group.store') }}" method="POST">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row">
            <!-- Select Webinar -->
            <div class="form-group col-md-4 col-12">
                <label for="webinar_id">Select Webinar</label>
                <small class="form-text text-muted">Choose the webinar for which you want to create a group.</small>
                <select name="webinar_id" id="webinar_id" class="form-control select2">
                    @foreach ($webinars as $webinar)
                    <option value="{{ $webinar->id }}" {{ $isEdit && $group->webinar_id == $webinar->id ? 'selected' : '' }}>
                        {{ $webinar->title }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Select Instructor -->
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
                    <option value="{{ $instructor->id }}" {{ $isEdit && $group->instructor_id == $instructor->id ? 'selected' : '' }}>
                        {{ $instructor->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Duration -->
            <div class="form-group col-md-4 col-12">
                <label for="meeting_duration">Duration (hours)</label>
                <small class="form-text text-muted">Enter the duration of the meeting in hours.</small>
                <input type="number" name="meeting_duration" id="meeting_duration" class="form-control" value="{{ old('meeting_duration', $isEdit ? $group->meeting_duration / 60 : 30) }}" required>
            </div>
        </div>

        <div class="row d-none">
            <!-- Recurring Meeting -->
            <div class="form-group col-md-4 col-12">
                <label for="meeting_recurring">Recurring</label>
                
                <small class="form-text text-muted">Is this a recurring <br>meeting?</small>
                <select name="meeting_recurring" id="meeting_recurring" class="form-control">
                    <option value="1" {{ $isEdit && $group->meeting_recurring == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ $isEdit && $group->meeting_recurring == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="form-group col-md-4 col-12">
                <label for="recurrence_interval">Recurrence Interval</label>
                <small class="form-text text-muted">Enter the number of intervals (e.g., every 2 days for daily recurrence).</small>
                <input type="number" name="recurrence_interval" id="recurrence_interval" class="form-control" value="{{ old('recurrence_interval', $isEdit ? ($meetingJson['recurrence']['repeat_interval'] ?? 1) : 1) }}" required value="1">
            </div>
        </div>        
        <div class="row">
            <!-- Start Time -->
            <div class="form-group col-md-4 col-12">
                <label for="meeting_start_time">Start Time</label>
                <small class="form-text text-muted">Set the date and time <br>for the meeting to start.</small>
                <input type="datetime-local" name="meeting_start_time" id="meeting_start_time" class="form-control" value="{{ old('meeting_start_time', $isEdit ? \Carbon\Carbon::parse($group->meeting_start_time)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
                     </div>

            <!-- End Time -->
            <div class="form-group col-md-4 col-12">
                <label for="meeting_end_time">End Time</label>
                <small class="form-text text-muted">Specify when the meeting should end. Required for recurring meetings.</small>
                <input type="datetime-local" name="meeting_end_time" id="meeting_end_time" class="form-control" value="{{ old('meeting_end_time', $isEdit ? \Carbon\Carbon::parse($group->meeting_end_time)->format('Y-m-d\TH:i') : now()->addDay()->format('Y-m-d\TH:i')) }}" required>
            </div>
            <div class="form-group col-md-4 col-12">
                <label for="end_times">Number of meetings</label>
                <small class="form-text text-muted">Enter the number of meetings (e.g., 6 for six meetings between the specificed dates).</small>
                <input type="number" name="end_times" id="end_times" class="form-control" value="{{ old('end_times', $isEdit ? ($meetingJson['recurrence']['end_times'] ?? 1) : 1) }}" required>
            </div>
        </div>

        <div class="row d-none">
            <!-- Participant Video -->
            <div class="form-group col-md-4 col-12">
                <label for="participant_video">Enable Participant Video</label>
                <small class="form-text text-muted">Choose whether participants' videos should be enabled when they join the meeting.</small>
                <select name="participant_video" id="participant_video" class="form-control">
                    <option value="0" {{ $isEdit && isset($meetingJson['settings']['participant_video']) && $meetingJson['settings']['participant_video'] == false ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $isEdit && isset($meetingJson['settings']['participant_video']) && $meetingJson['settings']['participant_video'] == true ? 'selected' : '' }}>Yes</option>
                </select>
            </div>
            <!-- Host Video -->
            <div class="form-group col-md-4 col-12">
                <label for="host_video">Enable Host Video</label>
                <small class="form-text text-muted">Choose whether the host's video should be enabled when the meeting starts.</small>
                <select name="host_video" id="host_video" class="form-control">
                    <option value="0" {{ $isEdit && isset($meetingJson['settings']['host_video']) && $meetingJson['settings']['host_video'] == false ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $isEdit && isset($meetingJson['settings']['host_video']) && $meetingJson['settings']['host_video'] == true ? 'selected' : '' }}>Yes</option>
                </select>
            </div>

            <!-- Audio Option -->
            <div class="form-group col-md-4 col-12">
                <label for="audio_option">Audio Option</label>
                <small class="form-text text-muted">Select how participants can connect to audio: by computer, telephone, or both.</small>
                <select name="audio_option" id="audio_option" class="form-control">
                    <option value="both" {{ $isEdit && ($meetingJson['settings']['audio'] ?? '') == 'both' ? 'selected' : '' }}>Both</option>
                    <option value="voip" {{ $isEdit && ($meetingJson['settings']['audio'] ?? '') == 'voip' ? 'selected' : '' }}>Computer Audio Only</option>
                    <option value="telephony" {{ $isEdit && ($meetingJson['settings']['audio'] ?? '') == 'telephony' ? 'selected' : '' }}>Telephone Only</option>

                </select>
            </div>
        </div>

        <div class="row">
            <!-- Recurrence Type -->
            <div class="form-group col-md-6 col-12">
                <label for="recurrence_type">Recurrence Type</label>
                <small class="form-text text-muted">Choose how often the meeting should repeat: daily, weekly, or monthly.</small>
                <select name="recurrence_type" id="recurrence_type" class="form-control">
                    <option value="1" {{ $isEdit && ($meetingJson['recurrence']['type'] ?? '') == 1 ? 'selected' : '' }}>Daily</option>
                    <option value="2" {{ $isEdit && ($meetingJson['recurrence']['type'] ?? '') == 2 ? 'selected' : '' }}>Weekly</option>
                    <option value="3" {{ $isEdit && ($meetingJson['recurrence']['type'] ?? '') == 3 ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>

            <!-- Weekly Days -->
            <div class="form-group col-md-6 col-12">
                <div class="form-group col-12" id="weekly_days_wrapper" style="display: none;">
                    <label for="weekly_days">Select Days of the Week (Weekly Recurrence)</label>
                    <small class="form-text text-muted">Choose the days on which the meeting should occur. Hold Ctrl (or Cmd) to select multiple days.</small>
                    @php
                        $selectedDays = $isEdit && isset($meetingJson['recurrence']['weekly_days']) ? explode(',', $meetingJson['recurrence']['weekly_days']) : [];
                    @endphp
                    <select name="weekly_days[]" id="weekly_days" class="form-control" multiple>
                        <option value="1" {{ in_array(1, $selectedDays) ? 'selected' : '' }}>Sunday</option>
                        <option value="2" {{ in_array(2, $selectedDays) ? 'selected' : '' }}>Monday</option>
                        <option value="3" {{ in_array(3, $selectedDays) ? 'selected' : '' }}>Tuesday</option>
                        <option value="4" {{ in_array(4, $selectedDays) ? 'selected' : '' }}>Wednesday</option>
                        <option value="5" {{ in_array(5, $selectedDays) ? 'selected' : '' }}>Thursday</option>
                        <option value="6" {{ in_array(6, $selectedDays) ? 'selected' : '' }}>Friday</option>
                        <option value="7" {{ in_array(7, $selectedDays) ? 'selected' : '' }}>Saturday</option>
                    </select>
                </div>
                <!-- Monthly Day -->
                <div class="form-group col-12" id="monthly_day_wrapper" style="display: none;">
                    <label for="monthly_day">Day of the Month (Monthly Recurrence)</label>
                    <small class="form-text text-muted">Enter the specific day of the month (e.g., 15 for the 15th day) when the meeting should occur.</small>
                    <input type="number" name="monthly_day" id="monthly_day" class="form-control" min="1" max="31" value="{{ old('monthly_day', $isEdit ? ($meetingJson['recurrence']['monthly_day'] ?? '') : '') }}">
                </div>
            </div>
        </div>

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
        <button type="submit" id="create-group-submit-btn" class="btn btn-primary mt-3">
            {{ $isEdit ? 'Update Group' : 'Create Group' }}
        </button>        
    </form>
    
    
</div>
<!-- Modal -->
<div class="modal fade" id="instructorGroupsModal" tabindex="-1" aria-labelledby="instructorGroupsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="instructorGroupsModalLabel">مجموعات المدرّس</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="groupsModalContent">
          <!-- سيتم تحميل المحتوى هنا -->
        </div>
      </div>
    </div>
  </div>
  
@endsection
@push('scripts_bottom')
<script>
    $(document).ready(function () {
        // Initialize select2 (بعض الحقول قد تحتاج لإعادة التهيئة)
        $('#student_ids').select2({
            placeholder: "اختر الطلاب",
            width: '100%',
            minimumResultsForSearch: 0 // ✅ هذا السطر يضمن ظهور البحث دائمًا
        });

        @if($isEdit)
            // إعادة ضبط القيم المختارة
            let selectedStudentIds = @json($group->members->pluck('student_id'));
            console.log(selectedStudentIds);
            $('#student_ids').val(selectedStudentIds).trigger('change');
        @endif
    });
    document.getElementById('create-group-form').addEventListener('submit', function(e) {
        const btn = document.getElementById('create-group-submit-btn');
        btn.disabled = true;
        btn.innerText = 'Submitting...';
    });
    document.addEventListener('DOMContentLoaded', function () {
        const recurrenceType = document.getElementById('recurrence_type');
        const weeklyDaysWrapper = document.getElementById('weekly_days_wrapper');
        const monthlyDayWrapper = document.getElementById('monthly_day_wrapper');

        recurrenceType.addEventListener('change', function () {
            weeklyDaysWrapper.style.display = this.value === '2' ? 'block' : 'none'; // Show for weekly
            monthlyDayWrapper.style.display = this.value === '3' ? 'block' : 'none'; // Show for monthly
        });
        // ✅ Trigger change event on load
        recurrenceType.dispatchEvent(new Event('change'));
    });

    $(document).ready(function () {
        var ajaxInit = false;
        const getGroupsRoute = "{{ route('instructor.groups', ['instructor' => 'INSTRUCTOR_ID']) }}";
            $('#teacher_id').on('change', function () {
                if ( ajaxInit ) {
                    return;
                }
                ajaxInit = true;
                const instructorId = $(this).val();
                
                if (!instructorId) return;

                const url = getGroupsRoute.replace('INSTRUCTOR_ID', instructorId);
                $('.loading-overlay').css('display', 'flex');
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        console.log(response);
                        $('#groupsModalContent').html(response);
                        const modal = new bootstrap.Modal(document.getElementById('instructorGroupsModal'));
                        modal.show();
                    },
                    complete: function(){
                        $('.loading-overlay').css('display', 'none');
                        ajaxInit = false;
                    },
                    error: function (xhr, status, error) {
                        console.error('حدث خطأ أثناء تحميل المجموعات:', error);
                        ajaxInit = false;
                    }
                });
            });
        $('#webinar_id').on('change', function () {
            const webinarId = $(this).val();
            const $studentSelect = $('#student_ids');

            // Clear current options and show loading
            $studentSelect.html('<option value="">Loading...</option>');

            if (webinarId) {
                $.ajax({
                    url: `/admin/course-group/ajax/webinar/${webinarId}/students`,
                    method: 'GET',
                    success: function (data) {
                        console.log(data);
                        $studentSelect.empty();

                        if (data.length > 0) {
                            data.forEach(student => {
                                $studentSelect.append(`<option value="${student.id}">${student.full_name}</option>`);
                            });
                        } else {
                            $studentSelect.html('<option value="">No students found</option>');
                        }
                    },
                    error: function () {
                        swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while loading students. Please try again.',
                        });
                        $studentSelect.html('<option value="">Error loading students</option>');
                    }
                });
            } else {
                $studentSelect.html('<option value="">Please select a webinar first</option>');
            }
        });
        @if(!$isEdit)
            $('#webinar_id').trigger('change');
        @endif
    });
</script>
<script>
    function calculateEndTimes() {
        const start = document.getElementById('meeting_start_time').value;
        const end = document.getElementById('meeting_end_time').value;
        const recurrenceType = document.getElementById('recurrence_type').value;
        const interval = parseInt(document.getElementById('recurrence_interval').value || 1);
        const weeklyDays = $('#weekly_days').val() || [];
        const monthlyDay = parseInt(document.getElementById('monthly_day').value || 1);
    
        if (!start || !end || !interval) return;
    
        const startDate = new Date(start);
        const endDate = new Date(end);
        let count = 0;
    
        if (recurrenceType == '1') {
            // Daily
            const diffDays = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24));
            count = Math.floor(diffDays / interval) + 1;
        } else if (recurrenceType == '2') {
            // Weekly
            const dayMillis = 24 * 60 * 60 * 1000;
            const totalDays = Math.floor((endDate - startDate) / dayMillis);
            let current = new Date(startDate);
    
            while (current <= endDate) {
                const dayOfWeek = current.getDay() + 1; // JS: Sunday=0, we want Sunday=1
                if (weeklyDays.includes(dayOfWeek.toString())) {
                    count++;
                }
                current.setDate(current.getDate() + 1);
            }
        } else if (recurrenceType == '3') {
            // Monthly
            let current = new Date(startDate);
            while (current <= endDate) {
                if (current.getDate() == monthlyDay) {
                    count++;
                }
                current.setMonth(current.getMonth() + interval);
            }
        }
    
        document.getElementById('end_times').value = count;
    }
    </script>
<script>
    const triggerFields = ['meeting_start_time', 'meeting_end_time', 'recurrence_type', 'recurrence_interval', 'weekly_days', 'monthly_day'];

    triggerFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', calculateEndTimes);
            el.addEventListener('input', calculateEndTimes);
        }
    });

    // Trigger on page load for edit mode
    @if($isEdit)
        window.addEventListener('load', calculateEndTimes);
    @endif
</script>
    
@endpush

