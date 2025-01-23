@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@php
    $values = !empty($setting) ? $setting->value : null;

    if (!empty($values)) {
        $values = json_decode($values, true);
    }
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

    <form action="{{ route('course-group.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- Select Webinar -->
            <div class="form-group col-md-3 col-12">
                <label for="webinar_id">Select Webinar</label>
                <small class="form-text text-muted">Choose the webinar for which you want to create a group.</small>
                <select name="webinar_id" id="webinar_id" class="form-control">
                    @foreach ($webinars as $webinar)
                        <option value="{{ $webinar->id }}">{{ $webinar->title }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Duration -->
            <div class="form-group col-md-3 col-12">
                <label for="meeting_duration">Duration (minutes)</label>
                <small class="form-text text-muted">Enter the duration of the meeting in minutes.</small>
                <input type="number" name="meeting_duration" id="meeting_duration" class="form-control" value="30" required>
            </div>

            <!-- Recurring Meeting -->
            <div class="form-group col-md-3 col-12">
                <label for="meeting_recurring">Recurring</label>
                <small class="form-text text-muted">Is this a recurring meeting?</small>
                <select name="meeting_recurring" id="meeting_recurring" class="form-control">
                    <option value="0">No</option>
                    <option value="1" selected>Yes</option>
                </select>
            </div>
            <div class="form-group col-md-3 col-12">
                <label for="recurrence_interval">Recurrence Interval</label>
                <small class="form-text text-muted">Enter the number of intervals (e.g., every 2 days for daily recurrence).</small>
                <input type="number" name="recurrence_interval" id="recurrence_interval" class="form-control" value="1" required>
            </div>
        </div>

        <div class="row">
            <!-- Start Time -->
            <div class="form-group col-md-6 col-12">
                <label for="meeting_start_time">Start Time</label>
                <small class="form-text text-muted">Set the date and time for the meeting to start.</small>
                <input type="datetime-local" name="meeting_start_time" id="meeting_start_time" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}" required>
            </div>

            <!-- End Time -->
            <div class="form-group col-md-6 col-12">
                <label for="meeting_end_time">End Time</label>
                <small class="form-text text-muted">Specify when the meeting should end. Required for recurring meetings.</small>
                <input type="datetime-local" name="meeting_end_time" id="meeting_end_time" class="form-control" value="{{ \Carbon\Carbon::now()->addDay()->format('Y-m-d\TH:i') }}" required>
            </div>
        </div>

        <div class="row">
            <!-- Participant Video -->
            <div class="form-group col-md-4 col-12">
                <label for="participant_video">Enable Participant Video</label>
                <small class="form-text text-muted">Choose whether participants' videos should be enabled when they join the meeting.</small>
                <select name="participant_video" id="participant_video" class="form-control">
                    <option value="0" selected>No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <!-- Host Video -->
            <div class="form-group col-md-4 col-12">
                <label for="host_video">Enable Host Video</label>
                <small class="form-text text-muted">Choose whether the host's video should be enabled when the meeting starts.</small>
                <select name="host_video" id="host_video" class="form-control">
                    <option value="0" selected>No</option>
                    <option value="1">Yes</option>
                </select>
            </div>

            <!-- Audio Option -->
            <div class="form-group col-md-4 col-12">
                <label for="audio_option">Audio Option</label>
                <small class="form-text text-muted">Select how participants can connect to audio: by computer, telephone, or both.</small>
                <select name="audio_option" id="audio_option" class="form-control">
                    <option value="both">Both (Computer and Telephone)</option>
                    <option value="voip">Computer Audio Only</option>
                    <option value="telephony">Telephone Only</option>
                </select>
            </div>
        </div>

        <div class="row">
            <!-- Recurrence Type -->
            <div class="form-group col-md-6 col-12">
                <label for="recurrence_type">Recurrence Type</label>
                <small class="form-text text-muted">Choose how often the meeting should repeat: daily, weekly, or monthly.</small>
                <select name="recurrence_type" id="recurrence_type" class="form-control">
                    <option value="1">Daily</option>
                    <option value="2">Weekly</option>
                    <option value="3">Monthly</option>
                </select>
            </div>

            <!-- Weekly Days -->
            <div class="form-group col-md-6 col-12">
                <div class="form-group col-12" id="weekly_days_wrapper" style="display: none;">
                    <label for="weekly_days">Select Days of the Week (Weekly Recurrence)</label>
                    <small class="form-text text-muted">Choose the days on which the meeting should occur. Hold Ctrl (or Cmd) to select multiple days.</small>
                    <select name="weekly_days[]" id="weekly_days" class="form-control" multiple>
                        <option value="1">Sunday</option>
                        <option value="2">Monday</option>
                        <option value="3">Tuesday</option>
                        <option value="4">Wednesday</option>
                        <option value="5">Thursday</option>
                        <option value="6">Friday</option>
                        <option value="7">Saturday</option>
                    </select>
                </div>
                <!-- Monthly Day -->
                <div class="form-group col-12" id="monthly_day_wrapper" style="display: none;">
                    <label for="monthly_day">Day of the Month (Monthly Recurrence)</label>
                    <small class="form-text text-muted">Enter the specific day of the month (e.g., 15 for the 15th day) when the meeting should occur.</small>
                    <input type="number" name="monthly_day" id="monthly_day" class="form-control" min="1" max="31">
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Students -->
            <div class="form-group col-md-12">
                <label for="student_ids">Select Students</label>
                <small class="form-text text-muted">Choose the students who will be part of this group. You can select multiple students.</small>
                <select name="student_ids[]" id="student_ids" class="form-control" multiple>
                    <option value="">Please select a webinar first</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Create Group</button>
    </form>
    
    
</div>
@endsection
@push('scripts_bottom')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const recurrenceType = document.getElementById('recurrence_type');
        const weeklyDaysWrapper = document.getElementById('weekly_days_wrapper');
        const monthlyDayWrapper = document.getElementById('monthly_day_wrapper');

        recurrenceType.addEventListener('change', function () {
            weeklyDaysWrapper.style.display = this.value === '2' ? 'block' : 'none'; // Show for weekly
            monthlyDayWrapper.style.display = this.value === '3' ? 'block' : 'none'; // Show for monthly
        });
    });

    $(document).ready(function () {
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
        $('#webinar_id').trigger('change');
    });
</script>
@endpush

