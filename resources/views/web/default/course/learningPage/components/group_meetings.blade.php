@php
    $meetings = json_decode($group->meeting_json, false);
    $occurrences = $meetings->occurrences ?? [];
    $isZoom = $group->session_type === 'zoom';
    $timezone = $isZoom ? ($meetings->timezone ?? config('app.timezone')) : (auth()->user()->timezone ?? config('app.timezone'));
@endphp

@if (!empty($occurrences))
    @foreach ($occurrences as $occurrence)
        <div class="card mb-3">
            <div class="card-body">
                <p class="card-text">
                    <strong>{{ trans('public.start_time') }}:</strong>
                    {{ \Carbon\Carbon::parse($occurrence->start_time)->setTimezone($timezone)->format('Y-m-d h:i A') }}<br>

                    <strong>{{ trans('public.the_duration') }}:</strong>
                    {{ $occurrence->duration }} {{ trans('public.minutes') }}<br>

                    <strong>{{ trans('public.time_zone') }}:</strong>
                    {{ $timezone }}
                </p>

                @if ($isZoom)
                    <a href="{{ $meetings->join_url ?? '#' }}" class="btn btn-primary" target="_blank">
                        {{ trans('public.join_meeting') }}
                    </a>
                @else
                    <span class="badge badge-secondary">{{ trans('public.offline_session') }}</span>
                @endif
            </div>
        </div>
    @endforeach
@else
    <div class="card mb-3">
        <div class="card-body">
            @if ($isZoom)
                <p class="card-text">
                    <strong>{{ trans('public.time_zone') }}:</strong> {{ $timezone }}
                </p>
                <a href="{{ $meetings->join_url ?? '#' }}" class="btn btn-primary" target="_blank">
                    {{ trans('public.join_meeting') }}
                </a>
            @else
                <span class="badge badge-secondary">{{ trans('public.offline_session') }}</span>
            @endif
        </div>
    </div>
@endif
