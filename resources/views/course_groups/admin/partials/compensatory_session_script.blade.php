@push('scripts_bottom')
<script>
$(document).ready(function () {
    $('.add-makeup-session-btn').on('click', function () {
        const groupId = $(this).data('group-id');
        const groupName = $(this).data('group-name');
        const sessionType = $(this).data('session-type'); // "zoom" أو "offline"
        const formAction = "{{ url('admin/course-group') }}/" + groupId + "/add-compensatory-session";

        $('#makeup-session-form').attr('action', formAction);
        $('#groupName').text(groupName);

        // إظهار أو إخفاء حسب نوع الجلسة
        if (sessionType === 'zoom') {
            $('#zoom-confirm-message').show();
            $('#offline-inputs').hide();
            $('#makeup_date').prop('required', false);
            $('#makeup_time').prop('required', false);
        } else {
            $('#zoom-confirm-message').hide();
            $('#offline-inputs').show();
            $('#makeup_date').prop('required', true);
            $('#makeup_time').prop('required', true);
        }

        $('#makeupSessionModal').modal('show');
    });
});
</script>
@endpush
