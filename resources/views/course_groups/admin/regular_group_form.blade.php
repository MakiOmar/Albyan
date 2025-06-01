<form id="create-group-form" action="{{ $isEdit ? route('course-group.update', $group->id) : route('course-group.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="schedule_type" value="regular">
    <div class="row">
        <!-- Select Webinar -->
        @include('course_groups.admin.partials.fields.webinar')
        <!-- Select Instructor -->
        @include('course_groups.admin.partials.fields.instructor')
        
        <!-- Duration -->
        @include('course_groups.admin.partials.fields.duration')
    </div>

    <div class="row">
        <!-- Recurring Meeting -->
        @include('course_groups.admin.partials.fields.is_recurring')
        <!-- Recurring meeting interval -->
        @include('course_groups.admin.partials.fields.recurring_interval')
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
        <!-- Number of Meetings -->
        @include('course_groups.admin.partials.fields.meetings_number')
    
        <!-- Session Type -->
        @include('course_groups.admin.partials.fields.session_type')
        
    </div>
    <!-- Zoom config -->
    @include('course_groups.admin.partials.fields.zoom_config')


    <div class="row">
        <!-- Recurrence Type -->
        @include('course_groups.admin.partials.fields.recurrence_type')

        <div class="form-group col-md-6 col-12">
            <!-- Weekly Days -->
            @include('course_groups.admin.partials.fields.weekly_days')
            <!-- Monthly Day -->
            @include('course_groups.admin.partials.fields.monthly_day')
        </div>
    </div>

    @include('course_groups.admin.partials.fields.students')

    <button type="submit" id="create-group-submit-btn" class="btn btn-success mt-3">
        {{ $isEdit ? 'Update Group' : 'Create Group' }}
    </button>        
</form>