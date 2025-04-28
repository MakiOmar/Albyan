@extends('admin.layouts.app')

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
        padding: 8px;
        border-radius: 8px;
        color: #000;
    }
    .session-zoom {
        background-color: #d1ecf1;
    }
    .session-offline {
        background-color: #d4edda;
    }
    .session-ending-soon {
        background-color: #f8d7da;
    }
    .badge-today {
        background-color: #ffc107;
        color: #000;
        font-size: 0.7rem;
        padding: 3px 5px;
        border-radius: 5px;
    }
</style>

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('schedule.index', ['week' => $weekOffset - 1, 'type' => request('type')]) }}" class="btn btn-primary">الأسبوع السابق</a>
            <a href="{{ route('schedule.index', ['week' => 0, 'type' => request('type')]) }}" class="btn btn-warning mx-2">الأسبوع الحالي</a>
            <a href="{{ route('schedule.index', ['week' => $weekOffset + 1, 'type' => request('type')]) }}" class="btn btn-primary">الأسبوع القادم</a>
        </div>

        <div>
            <form id="filter-form" method="GET" action="{{ route('schedule.index') }}" class="d-flex">
                <input type="hidden" name="week" value="{{ $weekOffset }}">
                <select name="type" onchange="document.getElementById('filter-form').submit()" class="form-control">
                    <option value="">كل الجلسات</option>
                    <option value="zoom" {{ request('type') == 'zoom' ? 'selected' : '' }}>Zoom فقط</option>
                    <option value="offline" {{ request('type') == 'offline' ? 'selected' : '' }}>Offline فقط</option>
                </select>
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
            @php $skipCells = []; @endphp

            @foreach($timeSlots as $slot)
                @php [$slotStart, $slotEnd] = [$slot['start'], $slot['end']]; @endphp
                <tr>
                    <td style="text-align:center;padding: 0 5px; min-width:100px">{{ $slotStart }} - {{ $slotEnd }}</td>

                    @foreach($weekDays as $day)
                        @php
                            $cellKey = $day['date'] . '_' . $slotStart;
                            if (in_array($cellKey, $skipCells)) {
                                continue;
                            }

                            $session = collect($sessions)->first(function ($s) use ($day, $slotStart) {
                                return $s['day'] === $day['date'] && $s['time'] === $slotStart;
                            });

                            if ($session) {
                                // فلترة حسب نوع الجلسة إذا تم اختيار فلتر
                                $filterType = request('type');
                                if (!empty($filterType) && $session['session_type'] !== $filterType) {
                                    $session = null;
                                }
                            }

                            if ($session) {
                                $rowspan = ceil($session['duration']);
                                for ($i = 1; $i < $rowspan; $i++) {
                                    $skipTime = \Carbon\Carbon::parse($slotStart)->addHours($i)->format('H:i');
                                    $skipCells[] = $day['date'] . '_' . $skipTime;
                                }

                                $today = \Carbon\Carbon::now('Asia/Dubai')->format('Y-m-d');
                                $endingSoon = \Carbon\Carbon::parse($session['day'])->addWeeks(1)->greaterThanOrEqualTo(\Carbon\Carbon::now('Asia/Dubai'));
                                $isToday = $session['day'] == $today;

                                $cellClass = $session['session_type'] == 'zoom' ? 'session-zoom' : 'session-offline';
                                if ($endingSoon) {
                                    $cellClass = 'session-ending-soon';
                                }
                            }
                        @endphp

                        @if($session)
                            <td rowspan="{{ $rowspan }}" class="session-cell {{ $cellClass }}">
                                <strong>{{ $session['webinar_title'] }}</strong><br>
                                مجموعة: {{ $session['group_id'] }}<br>
                                من: {{ \Carbon\Carbon::createFromFormat('H:i', $session['time'])->format('h:i A') }}<br>
                                المدة: {{ $session['duration'] }} ساعة<br>

                                <div class="mt-1">
                                    <span class="badge {{ $session['session_type'] == 'zoom' ? 'badge-info' : 'badge-success' }}">
                                        {{ $session['session_type'] == 'zoom' ? 'Zoom' : 'Offline' }}
                                    </span>

                                    @if($isToday)
                                        <span class="badge-today">جلسة اليوم</span>
                                    @endif
                                </div>
                            </td>
                        @else
                            <td onclick="openCreateGroupPopup('{{ $day['date'] }}', '{{ $slotStart }}')" style="cursor:pointer;">
                                <div style="height: 60px;"></div>
                            </td>
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
@endsection

@push('scripts_bottom')
<script>
function openCreateGroupPopup(date, time) {
    $('#selected_date').val(date);
    $('#selected_time').val(time);
    $('#selected_datetime_text').text('إنشاء جلسة في ' + date + ' الساعة ' + time);
    $('#createGroupModal').modal('show');
}
</script>
@endpush