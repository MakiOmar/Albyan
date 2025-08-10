<div class="webinar-card {{ getCourseCardStyleClass() }}">
    @if ( isset($index) && $featuredCount == ( $index + 1 ) )
    <svg class="position-absolute" style="top:-30px;left:-30px" width="104" height="104" viewBox="0 0 104 104" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M52.0006 9.41504C75.5192 9.41504 94.5866 28.4817 94.588 52.002C94.5893 75.5221 75.5242 94.5889 52.0056 94.5889C28.4869 94.5889 9.41958 75.5221 9.4181 52.002C9.41671 28.4817 28.4819 9.41504 52.0006 9.41504Z" stroke="#23BDEE" stroke-opacity="0.2" stroke-width="18.8302"/>
                    </svg>
    @endif
    <figure>
        <div class="image-box">
            {{--
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
                @elseif(!empty($webinar->type))
                    <span class="badge badge-primary">{{ trans('webinars.'.$webinar->type) }}</span>
                @endif

                @include('web.default.includes.product_custom_badge', ['itemTarget' => $webinar])
            </div>
            --}}
            <a href="{{ $webinar->getUrl() }}">
                @if(getCourseCardStyle() === 'dark_overlay' || getCourseCardStyle() === 'white_overlay')
                    <div class="image-overlay"></div>
                @endif
                <img src="{{ $webinar->getImage() }}" class="img-cover" alt="{{ $webinar->title }}">
            </a>


            @if($webinar->checkShowProgress())
                <div class="progress">
                    <span class="progress-bar" style="width: {{ $webinar->getProgress() }}%"></span>
                </div>
            @endif

            @if($webinar->type == 'webinar')
                <a href="{{ $webinar->addToCalendarLink() }}" target="_blank" class="webinar-notify d-flex align-items-center justify-content-center">
                    <i data-feather="bell" width="20" height="20" class="webinar-icon"></i>
                </a>
            @endif
        </div>

        <figcaption class="webinar-card-body">
            <div class="user-inline-avatar d-flex align-items-center">
                <div class="avatar bg-gray200">
                    <img src="{{ $webinar->teacher->getAvatar() }}" class="img-cover" alt="{{ $webinar->teacher->full_name }}">
                </div>
                <a href="{{ $webinar->teacher->getProfileUrl() }}" target="_blank" class="user-name ml-5 font-14">{{ $webinar->teacher->full_name }}</a>
            </div>

            <a href="{{ $webinar->getUrl() }}">
                <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($webinar->title,'title') }}</h3>
            </a>
            <div class="d-flex justify-content-between">
                @if(!empty($webinar->category))
                    <span class="d-block font-14">{{ trans('public.in') }} <a href="{{ $webinar->category->getUrl() }}" target="_blank" class="text-decoration-underline">{{ $webinar->category->title }}</a></span>
                @endif
                <div class="d-flex align-items-center">
                    <i data-feather="clock" width="20" height="20" class="webinar-icon ml-1"></i>
                    <span class="duration font-14">{{ convertMinutesToHourAndMinute($webinar->duration) }} {{ trans('home.hours') }}</span>
                </div>
            </div>

            @include(getTemplate() . '.includes.webinar.rate',['rate' => $webinar->getRate()])

            <div class="d-flex justify-content-between mt-20">
                

                <div class="vertical-line mx-15"></div>
                {{--
                <div class="d-flex align-items-center">
                    <i data-feather="calendar" width="20" height="20" class="webinar-icon"></i>
                    <span class="date-published font-14 ml-5">{{ dateTimeFormat(!empty($webinar->start_date) ? $webinar->start_date : $webinar->created_at,'j M Y') }}</span>
                </div>
                
                --}}
            </div>
            <div class="d-flex justify-content-between align-items-center">
                @php
                $user = auth()->user();
                $hasBought = $webinar->checkUserHasBought($user, true, true);
                $canSale   = ( $webinar->canSale() and ! $hasBought );
                @endphp

                @if($canSale and !empty($webinar->price) and $webinar->price > 0)
                <div class="d-flex align-items-center">
                    <form action="/cart/store" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" name="item_id" value="{{ $webinar->id }}">
                        <input type="hidden" name="item_name" value="webinar_id">
                        <input id="direct_buy" type="text" style="display:none;" name="direct_buy" value="yes">
                        <button type="button" data-action="buy_now" class="btn btn-primary {{ $canSale ? 'js-course-add-to-cart-btn' : ($webinar->cantSaleStatus($hasBought) .' disabled ') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                            {{ trans( 'webinars.add_to_cart' ) }}
                        </button>
                    </form>
                </div>
                @endif
                <div class="webinar-price-box">
                    @if(!empty($isRewardCourses) and !empty($webinar->points))
                        <span class="text-warning real font-14">{{ $webinar->points }} {{ trans('update.points') }}</span>
                    @elseif(!empty($webinar->price) and $webinar->price > 0)
                        @if($webinar->bestTicket() < $webinar->price)
                        @php
                        $bestTicket = str_replace('د.إ','درهم/إماراتي', handlePrice($webinar->bestTicket(), true, true, false, null, true) );
                        $price = str_replace('د.إ','درهم/إماراتي', handlePrice($webinar->price, true, true, false, null, true) );
                        @endphp
                            <span class="real">{{ $bestTicket }}</span>
                            <span class="off ml-10">{{ $price }}</span>
                        @else
                        @php
                        $unformatedPrice = handlePrice($webinar->price, true, true, false, null, true);
                        $price = preg_replace('/د\.\s*[^\d]+/u', ' درهم/إماراتي', $unformatedPrice);
                        @endphp
                            <span class="real">{{ $price }}</span>
                        @endif
                    @else
                        <span class="real font-14">{{ trans('public.free') }}</span>
                    @endif
                </div>
            </div>
            
        </figcaption>
    </figure>
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
