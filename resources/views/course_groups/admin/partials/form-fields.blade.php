<div class="row">
    <div class="form-group col-md-6">
        <label for="webinar_id">اختر الويبينار</label>
        <select name="webinar_id" id="webinar_id" class="form-control select2" required>
            @foreach($webinars as $webinar)
                <option value="{{ $webinar->id }}" {{ old('webinar_id', $group->webinar_id ?? '') == $webinar->id ? 'selected' : '' }}>
                    {{ $webinar->title }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label for="teacher_id">اختر المدرس</label>
        <select name="teacher_id" id="teacher_id" class="form-control select2" required>
            @foreach($instructors as $instructor)
                <option value="{{ $instructor->id }}" {{ old('teacher_id', $group->instructor_id ?? '') == $instructor->id ? 'selected' : '' }}>
                    {{ $instructor->full_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="form-group col-md-6">
        <label for="meeting_duration">المدة (بالساعات)</label>
        <input type="number" step="0.5" min="0.5" name="meeting_duration" id="meeting_duration" class="form-control" value="{{ old('meeting_duration', isset($group) ? $group->meeting_duration / 60 : 1) }}" required>
    </div>
    <div class="form-group col-md-6">
        <label for="session_type">نوع الجلسة</label>
        <select name="session_type" id="session_type" class="form-control" required>
            <option value="zoom" {{ old('session_type', $group->session_type ?? '') == 'zoom' ? 'selected' : '' }}>Zoom</option>
            <option value="offline" {{ old('session_type', $group->session_type ?? '') == 'offline' ? 'selected' : '' }}>حضوري</option>
        </select>
    </div>
</div>

{{-- التواريخ والأوقات --}}
<div class="row">
    <div class="form-group col-md-6">
        <label for="meeting_start_date">تاريخ البدء</label>
        <input type="date" name="meeting_start_date" id="meeting_start_date" class="form-control" value="{{ old('meeting_start_date', isset($group) ? \Carbon\Carbon::parse($group->meeting_start_time)->format('Y-m-d') : '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label for="meeting_start_time">وقت البدء</label>
        <input type="time" name="meeting_start_time" id="meeting_start_time" class="form-control" value="{{ old('meeting_start_time', isset($group) ? \Carbon\Carbon::parse($group->meeting_start_time)->format('H:i') : '') }}" required>
    </div>
</div>

<div class="row">
    <div class="form-group col-md-6">
        <label for="meeting_end_date">تاريخ الانتهاء</label>
        <input type="date" name="meeting_end_date" id="meeting_end_date" class="form-control" value="{{ old('meeting_end_date', isset($group) ? \Carbon\Carbon::parse($group->meeting_end_time)->format('Y-m-d') : '') }}">
    </div>
    <div class="form-group col-md-6">
        <label for="meeting_end_time">وقت الانتهاء</label>
        <input type="time" name="meeting_end_time" id="meeting_end_time" class="form-control" value="{{ old('meeting_end_time', isset($group) ? \Carbon\Carbon::parse($group->meeting_end_time)->format('H:i') : '') }}">
    </div>
</div>

{{-- التكرار --}}
<div class="row">
    <div class="form-group col-md-4">
        <label for="meeting_recurring">هل الجلسة متكررة؟</label>
        <select name="meeting_recurring" id="meeting_recurring" class="form-control">
            <option value="1" {{ old('meeting_recurring', $group->meeting_recurring ?? '') == 1 ? 'selected' : '' }}>نعم</option>
            <option value="0" {{ old('meeting_recurring', $group->meeting_recurring ?? '') == 0 ? 'selected' : '' }}>لا</option>
        </select>
    </div>
    <div class="form-group col-md-4">
        <label for="recurrence_type">نوع التكرار</label>
        <select name="recurrence_type" id="recurrence_type" class="form-control">
            <option value="1" {{ old('recurrence_type', $meetingJson['recurrence']['type'] ?? '') == 1 ? 'selected' : '' }}>يومي</option>
            <option value="2" {{ old('recurrence_type', $meetingJson['recurrence']['type'] ?? '') == 2 ? 'selected' : '' }}>أسبوعي</option>
            <option value="3" {{ old('recurrence_type', $meetingJson['recurrence']['type'] ?? '') == 3 ? 'selected' : '' }}>شهري</option>
        </select>
    </div>
    <div class="form-group col-md-4">
        <label for="recurrence_interval">كل كم يوم/أسبوع/شهر؟</label>
        <input type="number" name="recurrence_interval" id="recurrence_interval" class="form-control" value="{{ old('recurrence_interval', $meetingJson['recurrence']['repeat_interval'] ?? 1) }}">
    </div>
</div>

{{-- أيام الأسبوع إذا كان أسبوعي --}}
<div class="form-group" id="weekly_days_wrapper" style="display: none;">
    <label for="weekly_days">أيام الأسبوع</label>
    @php
        $selectedDays = old('weekly_days', explode(',', $meetingJson['recurrence']['weekly_days'] ?? ''));
    @endphp
    <select name="weekly_days[]" id="weekly_days" class="form-control" multiple>
        <option value="1" {{ in_array(1, $selectedDays) ? 'selected' : '' }}>الأحد</option>
        <option value="2" {{ in_array(2, $selectedDays) ? 'selected' : '' }}>الاثنين</option>
        <option value="3" {{ in_array(3, $selectedDays) ? 'selected' : '' }}>الثلاثاء</option>
        <option value="4" {{ in_array(4, $selectedDays) ? 'selected' : '' }}>الأربعاء</option>
        <option value="5" {{ in_array(5, $selectedDays) ? 'selected' : '' }}>الخميس</option>
        <option value="6" {{ in_array(6, $selectedDays) ? 'selected' : '' }}>الجمعة</option>
        <option value="7" {{ in_array(7, $selectedDays) ? 'selected' : '' }}>السبت</option>
    </select>
</div>

{{-- يوم من الشهر إن كان شهري --}}
<div class="form-group" id="monthly_day_wrapper" style="display: none;">
    <label for="monthly_day">اليوم من الشهر</label>
    <input type="number" min="1" max="31" name="monthly_day" id="monthly_day" class="form-control" value="{{ old('monthly_day', $meetingJson['recurrence']['monthly_day'] ?? '') }}">
</div>

{{-- عدد اللقاءات --}}
<div class="form-group">
    <label for="end_times">عدد اللقاءات</label>
    <input type="number" name="end_times" id="end_times" class="form-control" value="{{ old('end_times', $meetingJson['recurrence']['end_times'] ?? 1) }}" required>
</div>

{{-- الطلاب --}}
<div class="form-group">
    <label for="student_ids">اختر الطلاب</label>
    <select name="student_ids[]" id="student_ids" class="form-control select2" multiple required>
        @foreach($allStudents as $student)
            <option value="{{ $student->id }}" {{ in_array($student->id, old('student_ids', $group->members->pluck('student_id')->toArray() ?? [])) ? 'selected' : '' }}>
                {{ $student->full_name }}
            </option>
        @endforeach
    </select>
</div>
