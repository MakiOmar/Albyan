@extends('admin.layouts.app')
@php
    $pageTitle = 'جدول المواعيد';
@endphp
@section('content')
<style>
    table th, table td {
        vertical-align: middle !important;
        border: 1px solid #dee2e6 !important;
    }
    .table td {
        font-size: 0.85rem;
    }
    .session-cell {
        padding: 6px;
        border-radius: 6px;
        font-size: 0.75rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        cursor: pointer;
    }
    .session-zoom {
        background-color: #d1ecf1;
    }
    .session-offline {
        background-color: #d4edda;
    }
    .session-ending-soon {
        background-color: #9b0b17;
        color:#fff;
    }
    .badge-today {
        background-color: #ffc107;
        color: #000;
        font-size: 0.7rem;
        padding: 3px 5px;
        border-radius: 5px;
    }
    .badge-ending {
        background-color: #000;
        color: #fff;
        font-size: 0.7rem;
        padding: 3px 5px;
        border-radius: 5px;
    }
</style>

<div class="container">
    @include('partials.admin_page_title')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('schedule.index', ['week' => $weekOffset - 1, 'type' => request('type'), 'instructor_id' => request('instructor_id')]) }}" class="btn btn-primary">الأسبوع السابق</a>
            <a href="{{ route('schedule.index', ['week' => 0, 'type' => request('type'), 'instructor_id' => request('instructor_id')]) }}" class="btn btn-warning mx-2">الأسبوع الحالي</a>
            <a href="{{ route('schedule.index', ['week' => $weekOffset + 1, 'type' => request('type'), 'instructor_id' => request('instructor_id')]) }}" class="btn btn-primary">الأسبوع القادم</a>            
        </div>

        <div class="w-50">
            <form id="filter-form" method="GET" action="{{ route('schedule.index') }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="week" value="{{ $weekOffset }}">
                <div class="row w-100">
                    <div class="col-md-6">
                        <select name="instructor_id" id="instructor_id" onchange="document.getElementById('filter-form').submit()" class="form-control mr-2">
                            <option value="">كل المحاضرين</option>
                            @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}" {{ request('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                    {{ $instructor->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select name="type" onchange="document.getElementById('filter-form').submit()" class="form-control">
                            <option value="">كل الجلسات</option>
                            <option value="zoom" {{ request('type') == 'zoom' ? 'selected' : '' }}>Zoom فقط</option>
                            <option value="offline" {{ request('type') == 'offline' ? 'selected' : '' }}>Offline فقط</option>
                        </select>
                    </div>
                </div>
            </form>            
        </div>
    </div>

    <h2 class="text-center mb-4">الجدول الأسبوعي</h2>

    <table class="table table-bordered text-center">
        <thead class="thead-dark">
            <tr>
                <th>الوقت</th>
                @foreach($weekDays as $day)
                    <th>
                        {{ $day['name'] }}<br>
                        <small>{{ $day['label'] }}</small>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($timeSlots as $slot)
                @php [$slotStart, $slotEnd] = [$slot['start'], $slot['end']]; @endphp
                <tr>
                    <td style="text-align:center;padding: 0 5px; min-width:100px">{{ $slotStart }} - {{ $slotEnd }}</td>

                    @foreach($weekDays as $day)
                        @php
                            $filterType = request('type');
                            $filterInstructor = request('instructor_id');

                            $sessionsInCell = collect($sessions)->filter(function ($s) use ($day, $slotStart, $filterType, $filterInstructor) {
                                $matchesType = empty($filterType) || $s['session_type'] === $filterType;
                                $matchesInstructor = empty($filterInstructor) || $s['instructor_id'] == $filterInstructor;
                                return $s['day'] === $day['date'] && $s['time'] === $slotStart && $matchesType && $matchesInstructor;
                            });
                            $height = '80px';
                        @endphp

                        @if($sessionsInCell->isNotEmpty())
                            @php
                            $height = '180px';
                            @endphp
                            <td class="position-relative" style="height: {{ $height }}; min-width: 150px;">
                                @foreach($sessionsInCell as $index => $session)
                                    @php
                                        $offset = 0; // الطبقات كلها فوق بعض
                                        $today = \Carbon\Carbon::now('Asia/Dubai')->format('Y-m-d');
                                        $isToday = $session['day'] === $today;

                                        $endingSoon = false;
                                        if (!empty($session['last_day']) && $session['is_recurring']) {
                                            $lastDay = \Carbon\Carbon::parse($session['last_day'])->timezone('Asia/Dubai');
                                            $now = \Carbon\Carbon::now('Asia/Dubai');
                                            $daysRemaining = $now->diffInDays($lastDay, false);
                                            if ($daysRemaining <= 7) {
                                                $endingSoon = true;
                                            }
                                        }

                                        $cellClass = $session['session_type'] === 'zoom' ? 'session-zoom' : 'session-offline';
                                        if ($endingSoon) {
                                            $cellClass = 'session-ending-soon';
                                        }
                                    @endphp

                                    <div class="session-cell {{ $cellClass }} session-layer-{{ $loop->index }}" 
                                        style="position:absolute; top:0px; left:5px; right:5px; z-index:{{ $loop->index == 0 ? 10 : 1 }}; transition:0.3s">
                                        <strong>{{ $session['webinar_title'] }}</strong><br>
                                        مجموعة: {{ $session['group_id'] }}<br>
                                        المدرس: {{ $session['instructor_name'] }}<br>
                                        من: {{ \Carbon\Carbon::createFromFormat('H:i', $session['time'])->format('h:i A') }}<br>
                                        المدة: {{ $session['duration'] }} ساعة
                                        <div class="mt-1">
                                            <span class="badge {{ $session['session_type'] === 'zoom' ? 'badge-info' : 'badge-success' }}"
                                                style="cursor:pointer"
                                                data-toggle="modal"
                                                data-target="#sessionStudentsModal"
                                                onclick="loadGroupStudents({{ $session['group_id'] }}, '{{ $session['webinar_title'] }}')">
                                                {{ $session['session_type'] === 'zoom' ? 'Zoom' : 'Offline' }}
                                            </span>

                                            @if($isToday)
                                                <span class="badge-today">جلسة اليوم</span>
                                            @endif
                                            @if($endingSoon)
                                                <span class="badge-ending">تنتهي قريبا</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                {{-- ✅ نقاط التنقل --}}
                                <div class="d-flex justify-content-center mt-2 position-absolute w-100" style="bottom: 5px; z-index:20;">
                                    @foreach($sessionsInCell as $dotIndex => $session)
                                        <div class="dot-nav mx-1 rounded-circle" 
                                            style="width: 10px; height: 10px; background-color: #999; cursor:pointer;" 
                                            onmouseover="showLayer(this, {{ $dotIndex }})"></div>
                                    @endforeach
                                </div>
                            </td>

                        @else
                            <td onclick="openCreateGroupPopup('{{ $day['date'] }}', '{{ $slotStart }}', '{{ request('instructor_id') }}')" style="cursor:pointer;"></td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>

    </table>

    <!-- Modal إنشاء مجموعة -->
    <div class="modal fade" id="createGroupModal" tabindex="-1" role="dialog" aria-labelledby="createGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="quick-create-group-form" method="GET" action="{{ route('course-group.create-form') }}">
                <input type="hidden" name="selected_date" id="selected_date">
                <input type="hidden" name="selected_time" id="selected_time">
                <input type="hidden" name="selected_instructor" id="selected_instructor" value="{{ request('instructor_id') }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إنشاء مجموعة جديدة</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="selected_datetime_text">تأكيد الموعد</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">متابعة للإنشاء</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
<!-- Students Modal -->
<div class="modal fade" id="sessionStudentsModal" tabindex="-1" role="dialog" aria-labelledby="sessionStudentsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">الطلاب في المجموعة <span id="modalGroupTitle"></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="studentsListContainer">
          <p class="text-muted">جاري التحميل...</p>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection

@push('scripts_bottom')
<script>
function loadGroupStudents(groupId, groupTitle) {
    $('#modalGroupTitle').text(groupTitle);
    $('#studentsListContainer').html('<p class="text-muted">جاري التحميل...</p>');

    $.ajax({
        url: '/admin/course-group/ajax/groups/' + groupId + '/students',
        method: 'GET',
        success: function(data) {
            let editButton = `</ul>
                        <div class="mt-3">
                            <a href="${data.edit_url}" class="btn btn-warning">تعديل المجموعة</a>
                        </div>`;
            if (!data.students || data.students.length === 0) {
                let html = `<p class="text-danger">لا يوجد طلاب في هذه المجموعة.</p>`;
                html += editButton;
                $('#studentsListContainer').html(html);
            } else {
                let html = `<p><strong>عدد الطلاب:</strong> ${data.students.length}</p><ul class="list-group">`;
                $.each(data.students, function(index, student) {
                    html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                ${student.full_name}
                                <small class="text-muted">${student.email}</small>
                            </li>`;
                });
                html += editButton;

                $('#studentsListContainer').html(html);
            }
        },
        error: function(xhr) {
            $('#studentsListContainer').html('<p class="text-danger">حدث خطأ أثناء تحميل الطلاب.</p>');
            console.error(xhr.responseText);
        }
    });
}
</script>
<script>
    function showLayer(dot, index) {
        // إخفاء كل الطبقات في نفس الخلية
        const cell = dot.closest('td');
        const layers = cell.querySelectorAll('.session-cell');
        layers.forEach(el => el.style.zIndex = 1);

        // إظهار الطبقة المقابلة للنقطة
        const target = cell.querySelector('.session-layer-' + index);
        if (target) target.style.zIndex = 10;
    }
    jQuery(document).ready(function($){
        
        $('#instructor_id').select2();
    });
    function openCreateGroupPopup(date, time) {
        $('#selected_date').val(date);
        $('#selected_time').val(time);
        $('#selected_instructor').val('{{ request('instructor_id') }}'); // ✅ قيمة الفلتر الحالي للمحاضر
        $('#selected_datetime_text').text('إنشاء جلسة في ' + date + ' الساعة ' + time);
        $('#createGroupModal').modal('show');
    }

</script>
@endpush