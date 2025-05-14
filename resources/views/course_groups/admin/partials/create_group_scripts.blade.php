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
            $('#student_ids').val(selectedStudentIds).trigger('change');
        @endif
    });
    document.getElementById('create-group-form').addEventListener('submit', function(e) {
        const btn = document.getElementById('create-group-submit-btn');
        const form = this;

        // تحقق من صحة النموذج قبل التعطيل
        if (!form.checkValidity()) {
            // ✅ النموذج غير صالح، لا تعطل الزر
            e.preventDefault();
            e.stopPropagation();
            form.classList.add('was-validated'); // يظهر الأخطاء إن وجدت
            return;
        }

        // ✅ النموذج صالح
        btn.disabled = true;
        btn.innerText = 'جاري الإرسال...';
    });

    document.addEventListener('DOMContentLoaded', function () {
        const recurrenceType = document.getElementById('recurrence_type');
        if ( recurrenceType ) {
            const weeklyDaysWrapper = document.getElementById('weekly_days_wrapper');
            const monthlyDayWrapper = document.getElementById('monthly_day_wrapper');

            recurrenceType.addEventListener('change', function () {
                weeklyDaysWrapper.style.display = this.value === '2' ? 'block' : 'none'; // Show for weekly
                monthlyDayWrapper.style.display = this.value === '3' ? 'block' : 'none'; // Show for monthly
            });
            // ✅ Trigger change event on load
            recurrenceType.dispatchEvent(new Event('change'));
        }

    });

    $(document).ready(function () {
        const params = new URLSearchParams(window.location.search);
        const selectedDate = params.get('selected_date');
        const selectedTime = params.get('selected_time');
        const selectedInstructor = params.get('selected_instructor');

        if (selectedDate) {
            $('#meeting_start_date').val(selectedDate);
        }
        if (selectedTime) {
            $('#meeting_start_time').val(selectedTime);
        }
        if (selectedInstructor) {
            $('#teacher_id').val(selectedInstructor).trigger('change');
        }
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
        const startDateValue = document.getElementById('meeting_start_date').value;
        const endDateValue = document.getElementById('meeting_end_date').value;
        const startTimeValue = document.getElementById('meeting_start_time').value;
        const endTimeValue = document.getElementById('meeting_end_time').value;
        const recurrenceType = document.getElementById('recurrence_type').value;
        const interval = parseInt(document.getElementById('recurrence_interval').value || 1);
        const weeklyDays = $('#weekly_days').val() || [];
        let monthlyDay = 1;
        if (document.getElementById('recurrence_type').value === '3') {
            const monthlyDayInput = document.getElementById('monthly_day');
            if (monthlyDayInput && monthlyDayInput.value) {
                monthlyDay = parseInt(monthlyDayInput.value);
            }
        }


        if (!startDateValue || !endDateValue || !startTimeValue || !endTimeValue || !interval) return;

        const start = new Date(`${startDateValue}T${startTimeValue}`);
        const end = new Date(`${endDateValue}T${endTimeValue}`);
        let count = 0;

        if (recurrenceType == '1') {
            // Daily
            const diffDays = Math.floor((end - start) / (1000 * 60 * 60 * 24));
            count = Math.floor(diffDays / interval) + 1;
        } else if (recurrenceType == '2') {
            // Weekly
            let current = new Date(start);
            while (current <= end) {
                const dayOfWeek = current.getDay() + 1; // Sunday = 0
                if (weeklyDays.includes(dayOfWeek.toString())) {
                    count++;
                }
                current.setDate(current.getDate() + 1);
            }
        } else if (recurrenceType == '3') {
            // Monthly
            let current = new Date(start);
            while (current <= end) {
                if (current.getDate() == monthlyDay) {
                    count++;
                }
                current.setMonth(current.getMonth() + interval);
            }
        }

        document.getElementById('end_times').value = count;
    }
    document.addEventListener('DOMContentLoaded', function () {
        const startInput = document.getElementById('meeting_start_time');
        const endInput = document.getElementById('meeting_end_time');
        const recurrenceType = document.getElementById('recurrence_type');
        const intervalInput = document.getElementById('recurrence_interval');

        // تأكد من وجود كل الحقول المطلوبة
        if (startInput && endInput && recurrenceType && intervalInput) {
            const triggerFields = ['meeting_start_time', 'meeting_end_time', 'recurrence_type', 'recurrence_interval', 'weekly_days', 'monthly_day'];

            triggerFields.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('change', calculateEndTimes);
                    el.addEventListener('input', calculateEndTimes);
                }
            });

            // ✅ نفذ فقط بعد التأكد من وجود كل شيء
            @if(!$isEdit)
                calculateEndTimes();
            @endif
        } else {
            console.warn('⚠️ One or more required fields for calculateEndTimes are missing.');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {

        const meetingRecurring = document.getElementById('meeting_recurring');
        const recurrenceType = document.getElementById('recurrence_type');
        // If no value is selected or value is empty, set to weekly (2)
        if (!recurrenceType.value) {
            recurrenceType.value = '2';
        }
        // Create and dispatch the change event
        const event = new Event('change');
        recurrenceType.dispatchEvent(event);
        if ( ! meetingRecurring  ) {
            return;
        }
        const meetingEndTimeWrapper = document.getElementById('meeting_end_time').closest('.form-group');
        const meetingEndTimeInput = document.getElementById('meeting_end_time');
        const recurrenceFieldsWrapper = [
            document.getElementById('recurrence_interval').closest('.form-group'),
            document.getElementById('recurrence_type').closest('.form-group'),
            document.getElementById('end_times').closest('.form-group')
        ];
        const weeklyDaysWrapper = document.getElementById('weekly_days_wrapper');
        const monthlyDayWrapper = document.getElementById('monthly_day_wrapper');

        function toggleRecurrenceFields() {
            const showRecurring = meetingRecurring.value == '1';

            recurrenceFieldsWrapper.forEach(el => {
                if (el) el.style.display = showRecurring ? 'block' : 'none';
            });

            if (showRecurring) {
                toggleRecurrenceTypeFields();
            } else {
                weeklyDaysWrapper.style.display = 'none';
                monthlyDayWrapper.style.display = 'none';
            }
        }

        function toggleRecurrenceTypeFields() {
            if (recurrenceType.value == '1') { // Daily
                weeklyDaysWrapper.style.display = 'none';
                monthlyDayWrapper.style.display = 'none';
            } else if (recurrenceType.value == '2') { // Weekly
                weeklyDaysWrapper.style.display = 'block';
                monthlyDayWrapper.style.display = 'none';
            } else if (recurrenceType.value == '3') { // Monthly
                weeklyDaysWrapper.style.display = 'none';
                monthlyDayWrapper.style.display = 'block';
            }
        }
        function toggleEndTimeField() {
            if (meetingRecurring.value == '1') {
                meetingEndTimeWrapper.style.display = 'block';
                meetingEndTimeInput.required = true;
            } else {
                meetingEndTimeWrapper.style.display = 'none';
                meetingEndTimeInput.required = false;
                meetingEndTimeInput.value = '';
            }
        }

        meetingRecurring.addEventListener('change', toggleRecurrenceFields);
        meetingRecurring.addEventListener('change', toggleEndTimeField);
        recurrenceType.addEventListener('change', toggleRecurrenceTypeFields);

        // ✅ تشغيل وقت تحميل الصفحة
        toggleRecurrenceFields();
        toggleEndTimeField();
    });
    jQuery(document).ready(function ($) {
        let index = 1;

        // عند تغيير نوع الإدخال (تاريخ / يوم)
        $('#manual_occurrences_type').on('change', function () {
            const type = $(this).val();
            $('.manual_occurrences_type').val(type);
            if (type === 'day') {
                $('.occurrence-day').show();
                $('.occurrence-date').hide();
            } else {
                $('.occurrence-date').show();
                $('.occurrence-day').hide();
            }
        });

        // إضافة صف جديد
        $('#addOccurrence').click(function () {
            const type = $('#manual_occurrences_type').val();

            const dateInput = `
                <div class="col-md-4 occurrence-date" ${type === 'day' ? 'style="display:none;"' : ''}>
                    <label>التاريخ</label>
                    <input type="date" name="manual_occurrences[${index}][date]" class="form-control">
                </div>`;

            const daySelect = `
                <div class="col-md-4 occurrence-day" ${type === 'date' ? 'style="display:none;"' : ''}>
                    <label>اليوم</label>
                    <select name="manual_occurrences[${index}][day]" class="form-control">
                        <option value="Saturday">السبت</option>
                        <option value="Sunday">الأحد</option>
                        <option value="Monday">الاثنين</option>
                        <option value="Tuesday">الثلاثاء</option>
                        <option value="Wednesday">الأربعاء</option>
                        <option value="Thursday">الخميس</option>
                        <option value="Friday">الجمعة</option>
                    </select>
                </div>`;

            $('#manual-occurrences').append(`
                <div class="row occurrence-row mb-2 position-relative">
                    <input type="hidden" name="manual_occurrences[${index}][type]" value="${type}">
                    ${type === 'date' ? dateInput : daySelect}

                    <div class="col-md-4">
                        <label>الوقت</label>
                        <input type="time" name="manual_occurrences[${index}][time]" class="form-control" value="10:00">
                    </div>
                    <div class="col-md-4">
                        <label>المدة (بالساعات)</label>
                        <input type="number" step="0.1" name="manual_occurrences[${index}][duration]" class="form-control" placeholder="مثال: 1.5" min="0.1" value="1" required>
                    </div>
                    <div class="position-absolute" style="left:-30px;bottom: 1.5px;">
                        <button type="button" class="btn btn-danger remove-occurrence">X</button>
                    </div>
                </div>
            `);

            index++;
        });

        // حذف صف
        $(document).on('click', '.remove-occurrence', function () {
            $(this).closest('.occurrence-row').remove();
        });
        $('#manual_occurrences_type').trigger('change')
    });
</script>
@endpush