<div class="tab-pane mt-3 fade" id="groups" role="tabpanel" aria-labelledby="groups-tab">
    <h2>مجموعاتي</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>عنوان الدورة</th>
                <th>تاريخ البدء</th>
                <th>تاريخ الانتهاء</th>
                <th>نوع التكرار</th>
                <th>التكرار</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
                @php            
                    $start = \Carbon\Carbon::parse($group->meeting_start_time);
                    $end = $start->copy()->addMinutes($group->meeting_duration);
                
                    $startFormatted = $start->format('h:i A'); // 03:00 PM
                    $endFormatted = $end->format('h:i A');     // 04:00 PM
                @endphp
                <tr>
                    <td>{{ $group->webinar->title ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($group->meeting_start_time)->format('Y-m-d') }}</td>
                    <td>{{ \Carbon\Carbon::parse($group->meeting_end_time)->format('Y-m-d') }}</td>
                    <td>
                        @if($group->meeting_recurring)
                            @switch(optional(json_decode($group->meeting_json))->recurrence->type)
                                @case(1) يومي @break
                                @case(2) أسبوعي @break
                                @case(3) شهري @break
                                @default - 
                            @endswitch
                        @else
                            غير متكرر
                        @endif
                    </td>
                    <td>
                        @if($group->meeting_recurring)
                            @php
                                $recurrence = optional(json_decode($group->meeting_json))->recurrence;
                            @endphp

                            @if($recurrence->type == 1)
                                يوميًا
                            @elseif($recurrence->type == 2)
                                @php
                                    $daysMap = [
                                        1 => 'الأحد',
                                        2 => 'الإثنين',
                                        3 => 'الثلاثاء',
                                        4 => 'الأربعاء',
                                        5 => 'الخميس',
                                        6 => 'الجمعة',
                                        7 => 'السبت',
                                    ];
                                    
                                    $weeklyDays = is_string($recurrence->weekly_days)
                                        ? explode(',', $recurrence->weekly_days)
                                        : ($recurrence->weekly_days ?? []);

                                    $days = collect($weeklyDays)->map(fn($day) => $daysMap[(int)$day] ?? '')->implode(', ');

                                @endphp
                                {{ $days }}
                            @elseif($recurrence->type == 3)
                                اليوم {{ $recurrence->monthly_day ?? '-' }} من كل شهر
                            @endif
                            <p>من {{ $startFormatted }} إلى {{ $endFormatted }}</p>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>