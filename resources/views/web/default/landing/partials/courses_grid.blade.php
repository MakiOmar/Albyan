{{-- Diploma courses grid with contact buttons on each card --}}
@if(!empty($webinars) && $webinars->isNotEmpty())
    <section class="dl-section dl-section-alt" id="dl-courses">
        <div class="dl-container">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                <div>
                    <h2 class="section-title mb-0">
                        @if(!empty($category))
                            {{ $category->title }}
                        @else
                            {{ trans('home.latest_classes') }}
                        @endif
                    </h2>
                </div>
                @if(!empty($category))
                    <a href="{{ $category->getUrl() }}" class="btn btn-border-white mt-2 mt-md-0">{{ trans('home.view_all') }}</a>
                @endif
            </div>
            <div class="row">
                @foreach($webinars as $webinar)
                    <div class="col-12 col-sm-6 col-lg-4 mt-20">
                        @include('web.default.includes.webinar.grid-card-landing', [
                            'webinar' => $webinar,
                            'diplomaLandingWhatsapp' => $diplomaLandingWhatsapp ?? null,
                            'diplomaLandingCall' => $diplomaLandingCall ?? null,
                        ])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
