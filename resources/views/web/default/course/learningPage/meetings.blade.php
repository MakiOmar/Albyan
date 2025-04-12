<div class="content-tab p-15 pb-50">
    @foreach ($groups as $group)
        @php
            $meetings = json_decode($group->meeting_json, false);
            $occurrences = $meetings->occurrences ?? [];
        @endphp

        @if (!empty($occurrences))
            @foreach ($occurrences as $occurrence)
                <div class="card mb-3">
                    <div class="card-body">
                        <p class="card-text">
                            <strong>{{ trans( 'public.start_time' ) }}:</strong> {{ \Carbon\Carbon::parse($occurrence->start_time)->setTimezone($meetings->timezone)->format('Y-m-d h:i A') }}<br>
                            <strong>{{ trans( 'public.the_duration' ) }}:</strong> {{ $occurrence->duration }} {{ trans( 'public.minutes' ) }}<br>
                            <strong>{{ trans( 'public.time_zone' ) }}:</strong> {{ $meetings->timezone }}
                        </p>
                        <a href="{{ $meetings->join_url }}" class="btn btn-primary" target="_blank">{{ trans( 'public.join_meeting' ) }}</a>
                    </div>
                </div>
            @endforeach
        @else
            <div class="card mb-3">
                <div class="card-body">
                    <p class="card-text">
                        <strong>{{ trans( 'public.time_zone' ) }}:</strong> {{ $meetings->timezone }}
                    </p>
                    <a href="{{ $meetings->join_url }}" class="btn btn-primary" target="_blank">{{ trans( 'public.join_meeting' ) }}</a>
                </div>
            </div>
        @endif
    @endforeach
</div>
