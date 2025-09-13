<div class="webinar-card webinar-list webinar-list-2 d-flex mt-30 {{ getCourseCardStyleClass() }}">
    <div class="image-box">
        <div class="badges-lists">
            @if($webinar->bestTicket() < $webinar->price)
                <span class="badge badge-danger">{{ trans('public.offer',['off' => $webinar->bestTicket(true)['percent']]) }}</span>
            @elseif(empty($isFeature) and !empty($webinar->feature))
                <span class="badge badge-warning">{{ trans('home.featured') }}</span>
            @elseif($webinar->type == 'webinar')
                @if($webinar->start_date > time())
                    <span class="badge badge-primary">{{  trans('panel.not_conducted') }}</span>
                @elseif($webinar->isProgressing())
                    <span class="badge badge-secondary">{{ trans('webinars.in_progress') }}</span>
                @else
                    <span class="badge badge-secondary">{{ trans('public.finished') }}</span>
                @endif
            @else
                <span class="badge badge-primary">{{ trans('webinars.'.$webinar->type) }}</span>
            @endif
        </div>

        <a href="{{ $webinar->getUrl() }}">
            @if(getCourseCardStyle() === 'dark_overlay' || getCourseCardStyle() === 'white_overlay')
                <div class="image-overlay"></div>
            @endif
            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="{{ $webinar->getImage() ?: '/assets/default/img/placeholder.svg' }}" class="img-cover" alt="{{ $webinar->title }}" width="300" height="200">
        </a>

        <div class="progress-and-bell d-flex align-items-center">

            @if($webinar->type == 'webinar')
                <a href="{{ $webinar->addToCalendarLink() }}" target="_blank" class="webinar-notify d-flex align-items-center justify-content-center">
                    <i data-feather="bell" width="20" height="20" class="webinar-icon"></i>
                </a>
            @endif

            @if($webinar->type == 'webinar')
                <div class="progress ml-10">
                    <span class="progress-bar" style="width: {{ $webinar->getProgress() }}%"></span>
                </div>
            @endif
        </div>
    </div>

    <div class="webinar-card-body w-100 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ $webinar->getUrl() }}">
                <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($webinar->title,'title') }}</h3>
            </a>
        </div>

        @if(!empty($webinar->category))
            <span class="d-block font-14 mt-10">{{ trans('public.in') }} <a href="{{ $webinar->category->getUrl() }}" target="_blank" class="text-decoration-underline">{{ $webinar->category->title }}</a></span>
        @endif

        <div class="user-inline-avatar d-flex align-items-center mt-10">
            <div class="avatar bg-gray200">
                <img src="{{ $webinar->teacher->getAvatar() }}" class="img-cover" alt="{{ $webinar->teacher->full_name }}">
            </div>
            <a href="{{ $webinar->teacher->getProfileUrl() }}" target="_blank" class="user-name ml-5 font-14">{{ $webinar->teacher->full_name }}</a>
        </div>

        @include(getTemplate() . '.includes.webinar.rate',['rate' => $webinar->getRate()])

        <div class="d-flex justify-content-between mt-auto">
            <div class="d-flex align-items-center">
                <div class="d-flex align-items-center">
                    <i data-feather="clock" width="20" height="20" class="webinar-icon"></i>
                    <span class="duration ml-5 font-14">{{ convertMinutesToHourAndMinute($webinar->duration) }} {{ trans('home.hours') }}</span>
                </div>

                <div class="vertical-line h-25 mx-15"></div>
                {{--
                <div class="d-flex align-items-center">
                    <i data-feather="calendar" width="20" height="20" class="webinar-icon"></i>
                    <span class="date-published ml-5 font-14">{{ dateTimeFormat(!empty($webinar->start_date) ? $webinar->start_date : $webinar->created_at,'j M Y') }}</span>
                </div>
                --}}
            </div>

            <div class="webinar-price-box d-flex flex-column justify-content-center align-items-center">
                @if(!empty($webinar->price) and $webinar->price > 0)
                    @if($webinar->bestTicket() < $webinar->price)
                        <span class="off">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>
                        <span class="real">{{ handlePrice($webinar->bestTicket(), true, true, false, null, true) }}</span>
                    @else
                        <span class="real">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>
                    @endif
                @else
                    <span class="real font-14">{{ trans('public.free') }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .image-box {
        position: relative;
        overflow: hidden;
    }
    
    .image-box a {
        position: relative;
        display: block;
    }
    
    /* Dark Overlay Style */
    .course-card-dark-overlay .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: {{ getCourseCardStyleSettings()['overlay_color'] ?? '#000000' }};
        opacity: {{ (getCourseCardStyleSettings()['overlay_opacity'] ?? 30) / 100 }};
        z-index: 1;
        transition: opacity {{ getCourseCardStyleSettings()['transition_duration'] ?? 0.3 }}s ease;
        pointer-events: none;
        border-radius: 15px 15px 0 0;
    }
    
    .course-card-dark-overlay .image-box:hover .image-overlay {
        opacity: 0;
    }
    
    .course-card-dark-overlay .image-box img {
        position: relative;
        z-index: 0;
    }
    
    /* White Overlay Style */
    .course-card-white-overlay .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: {{ getCourseCardStyleSettings()['overlay_color'] ?? '#FFFFFF' }};
        opacity: {{ (getCourseCardStyleSettings()['overlay_opacity'] ?? 30) / 100 }};
        z-index: 1;
        transition: opacity {{ getCourseCardStyleSettings()['transition_duration'] ?? 0.3 }}s ease;
        pointer-events: none;
        border-radius: 15px 15px 0 0;
    }
    
    .course-card-white-overlay .image-box:hover .image-overlay {
        opacity: 0;
    }
    
    .course-card-white-overlay .image-box img {
        position: relative;
        z-index: 0;
    }
    
    /* Gray Hover Style */
    .course-card-gray-hover .image-box img {
        filter: grayscale({{ getCourseCardStyleSettings()['gray_filter_intensity'] ?? 100 }}%) brightness({{ getCourseCardStyleSettings()['brightness'] ?? 0.8 }});
        transition: filter {{ getCourseCardStyleSettings()['transition_duration'] ?? 0.3 }}s ease;
        position: relative;
        z-index: 0;
    }
    
    .course-card-gray-hover .image-box:hover img {
        filter: grayscale(0%) brightness(1);
    }
    
    /* Common styles */
    .image-box img {
        position: relative;
        z-index: 0;
    }
</style>
