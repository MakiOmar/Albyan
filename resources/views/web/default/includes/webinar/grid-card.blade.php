<div class="webinar-card">
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

            @if(!empty($webinar->category))
                <span class="d-block font-14 mt-10">{{ trans('public.in') }} <a href="{{ $webinar->category->getUrl() }}" target="_blank" class="text-decoration-underline">{{ $webinar->category->title }}</a></span>
            @endif

            @include(getTemplate() . '.includes.webinar.rate',['rate' => $webinar->getRate()])

            <div class="d-flex justify-content-between mt-20">
                <div class="d-flex align-items-center">
                    <i data-feather="clock" width="20" height="20" class="webinar-icon"></i>
                    <span class="duration font-14 ml-5">{{ convertMinutesToHourAndMinute($webinar->duration) }} {{ trans('home.hours') }}</span>
                </div>

                <div class="vertical-line mx-15"></div>
                {{--
                <div class="d-flex align-items-center">
                    <i data-feather="calendar" width="20" height="20" class="webinar-icon"></i>
                    <span class="date-published font-14 ml-5">{{ dateTimeFormat(!empty($webinar->start_date) ? $webinar->start_date : $webinar->created_at,'j M Y') }}</span>
                </div>
                
                --}}
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
                        </button>
                    </form>
                </div>
                @endif

            </div>

            <div class="webinar-price-box mt-25">
                @if(!empty($isRewardCourses) and !empty($webinar->points))
                    <span class="text-warning real font-14">{{ $webinar->points }} {{ trans('update.points') }}</span>
                @elseif(!empty($webinar->price) and $webinar->price > 0)
                    @if($webinar->bestTicket() < $webinar->price)
                        <span class="real">{{ handlePrice($webinar->bestTicket(), true, true, false, null, true) }}</span>
                        <span class="off ml-10">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>
                    @else
                        <span class="real">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>
                    @endif
                @else
                    <span class="real font-14">{{ trans('public.free') }}</span>
                @endif
            </div>
        </figcaption>
    </figure>
</div>
