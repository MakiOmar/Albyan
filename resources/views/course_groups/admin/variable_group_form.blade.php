@php
    $values = !empty($setting) ? $setting->value : null;

    if (!empty($values)) {
        $values = json_decode($values, true);
    }
    $isEdit = isset($group);
    $meetingJson = $isEdit ? json_decode($group->meeting_json, true) : null;
@endphp
<form id="create-group-form" action="{{ $isEdit ? route('course-group.update', $group->id) : route('course-group.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="schedule_type" value="variable">
    <div class="row">
        <!-- Select Webinar -->
        @include('course_groups.admin.partials.fields.webinar')
        <!-- Select Instructor -->
        @include('course_groups.admin.partials.fields.instructor')
        
        <!-- Duration -->
        @include('course_groups.admin.partials.fields.duration')
    </div>
        
    <div class="row">
        <!-- Start Date -->
        @include('course_groups.admin.partials.fields.start_date')
    
        <!-- Start Time -->
        @include('course_groups.admin.partials.fields.start_time')
    
        <!-- End Date -->
        @include('course_groups.admin.partials.fields.end_date')
    
        <!-- End Time -->
        @include('course_groups.admin.partials.fields.end_time')
    </div>
    
    <div class="row">
        <!-- Session Type -->
        @include('course_groups.admin.partials.fields.session_type')

        <!-- Datetime repeater -->
        @include('course_groups.admin.partials.offline_repeater')
    </div>
    <!-- Zoom config -->
    @include('course_groups.admin.partials.fields.zoom_config')

    @include('course_groups.admin.partials.fields.students')

    <button type="submit" id="create-group-submit-btn" class="btn btn-primary mt-3">
        {{ $isEdit ? 'Update Group' : 'Create Group' }}
    </button>        
</form>