@php
    $meetings = json_decode($group->meeting_json, false);
    $occurrences = $meetings->occurrences ?? [];
    $isZoom = $group->session_type === 'zoom';
    $timezone = 'Asia/Dubai';
@endphp

@if (!empty($occurrences))
    @foreach ($occurrences as $occurrence)
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <p class="card-text meeting-details">
                    <span>
                        <strong>{{ trans('public.start_time') }}:</strong>
                        {{ \Carbon\Carbon::parse($occurrence->start_time)->setTimezone($timezone)->format('Y-m-d h:i A') }}<br>
                    </span>
                    <span>
                        <strong>{{ trans('public.the_duration') }}:</strong>
                        {{ $occurrence->duration }} {{ trans('public.minutes') }}<br>
                    </span>
                    <span>
                        <strong>{{ trans('public.time_zone') }}:</strong>
                        {{ $timezone }}
                    </span>
                </p>
                @if(  $user->isTeacher() || $user->isUser() )
                    @php
                        $joinUrl = '#';
                        if ( isset( $meetings->join_url ) ) {
                            if (  $user->isTeacher() ) {
                                $joinUrl = $meetings->join_url;
                            } else {
                                $joinUrl = $meetings->start_url;
                            }
                        }
                        if ( isset( $occurrence->join_url ) ) {
                            if (  $user->isTeacher() ) {
                                $joinUrl = $occurrence->join_url;
                            } else {
                                $joinUrl = $occurrence->start_url;
                            }
                        }
                    @endphp
                    @if ($isZoom)
                        <a href="{{ $joinUrl }}" class="btn btn-primary" target="_blank">
                            {{ trans('public.join_meeting') }}
                        </a>
                    @else
                        <span class="badge badge-secondary">{{ trans('public.offline_session') }}</span>
                    @endif
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
