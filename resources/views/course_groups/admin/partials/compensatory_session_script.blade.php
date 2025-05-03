@push('scripts_bottom')
<script>
$(document).ready(function () {
    $('.add-makeup-session-btn').on('click', function () {
        const groupId     = $(this).data('group-id');
        const groupName   = $(this).data('group-name');
        const sessionType = $(this).data('session-type');
        const lastDateRaw = $(this).data('last-date');

        const isPanel = window.location.href.includes('/panel/my-groups');
        const formAction = isPanel 
            ? "{{ url('panel/course-group') }}/" + groupId + "/add-compensatory-session"
            : "{{ url('admin/course-group') }}/" + groupId + "/add-compensatory-session";

        $('#makeup-session-form').attr('action', formAction);
        $('#groupName').text(groupName);

        if (sessionType === 'zoom') {
            $('#zoom-confirm-message').show();
            $('#offline-inputs').hide();
        } else {
            $('#zoom-confirm-message').hide();
            $('#offline-inputs').show();

            // إعداد التاريخ بشكل افتراضي
            if (lastDateRaw) {
                const lastDateObj = new Date(lastDateRaw);
                const formattedDate = lastDateObj.toISOString().slice(0, 10); // yyyy-mm-dd
                $('#makeup_date').val(formattedDate);
            }
        }
    });
});
</script>
@endpush