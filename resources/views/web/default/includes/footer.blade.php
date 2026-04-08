@php
    $socials = getSocials();
    if (!empty($socials) and count($socials)) {
        $socials = collect($socials)->sortBy('order')->toArray();
    }

    $footerColumns = getFooterColumns();
@endphp

<footer class="footer bg-secondary position-relative user-select-none">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class=" footer-subscribe d-block d-md-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <strong>{{ trans('footer.join_us_today') }}</strong>
                        <span class="d-block mt-5 text-white">{{ trans('footer.subscribe_content') }}</span>
                    </div>
                    <div class="subscribe-input bg-white p-10 flex-grow-1 mt-30 mt-md-0">
                        <form action="/newsletters" method="post">
                            {{ csrf_field() }}

                            <div class="form-group d-flex flex-wrap align-items-center m-0">
                                <div class="w-100 flex-grow-1">
                                    <input type="email" name="newsletter_email" autocomplete="email" class="form-control border-0 @error('newsletter_email') is-invalid @enderror" placeholder="{{ trans('footer.enter_email_here') }}"/>
                                    @error('newsletter_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="w-100 mt-10">
                                    @include('web.default.includes.turnstile_widget')
                                </div>
                                <button type="submit" class="btn btn-primary rounded-pill mt-10 mt-md-0">{{ trans('footer.join') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $columns = ['first_column','second_column','third_column','forth_column'];
    @endphp
    <style>
        .info-section {
            padding: 20px;
            border-radius: 5px;
            color: white;
        }
        .info-section a{
            color: white;
        }

        .info-section h3 {
            padding-bottom: 10px;
        }
        .info-section li {
            padding-bottom: 8px;
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col-md-3 info-section">
                <div class="footer-logo" style="width: auto!important">
                    <a href="/">
                        @if(!empty($generalSettings['footer_logo']))
                            {{-- width/height reserve aspect ratio for CLS (max display width 200px via CSS) --}}
                            <img src="{{ $generalSettings['footer_logo'] }}" width="200" height="59" style="width: auto!important;max-width: 200px!important;height:auto" alt="footer logo">
                        @endif
                    </a>
                </div>

                <div class="footer-social mt-2 mb-4">
                    <h1>تابعنا على</h1>
                    @include('web.default.includes.title_border')
                    @if(!empty($socials) and count($socials))
                    <div class="mt-2">
                        @foreach($socials as $social)
                            <a href="{{ $social['link'] }}" target="_blank" class="mr-15 border border-white rounded-circle m-1 p-1">
                                <img src="{{ $social['image'] }}" alt="{{ $social['title'] }}">
                            </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                <h1>روابط هامة</h1>
                @include('web.default.includes.title_border')
                
                <ul class="list-unstyled  mt-4">
                    <li class="pb-2"><a href="/contact">اتصل بنا</a></li>
                    <li class="pb-2"><a href="/certificate_validation">التحقق من صحة الشهادة</a></li>
                    <li class="pb-2"><a href="/pages/privacy-policy">الشروط والقواعد</a></li>
                    <li class="pb-2"><a href="/about">معلومات عنا</a></li>
                </ul>
            </div>

            <div class="col-md-4 info-section">
              <h1>معلومات عنا</h1>
              @include('web.default.includes.title_border')
                <p class=" mt-4">معهد البيان للخدمات التعليمية يقدم تجربة تعليمية متميزة مع نخبة من المحاضرين والخبراء في مختلف المجالات. يقدم المعهد مئات الدبلومات التدريبية الاحترافية المصممة لتلبية احتياجات سوق العمل، مع خيارات مرنة في الحضور من مقر المعهد أو الدراسة أون لاين. يمنح المعهد شهادات معتمدة محلياً ودولياً تعزز من مكانتك المهنية وينظم حفل تخرج سنوي ضخم لتكريم اعداد كبيرة من خريجي المعهدبمختلف التخصصات بحضور شخصيات هامة. انضم إلى معهد البيان للارتقاء بمسارك المهني. </p>
            </div>

                        
            <div class="col-md-5 info-section">
                <h1>اتصل بنا</h1>
                @include('web.default.includes.title_border')
                <ul class="list-unstyled mt-4">
                <li class="pb-2 d-flex align-items-center">
                    <!-- Phone Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px; width: 16px; margin-right: 0.5rem;" fill="none" viewBox="0 0 24 24" stroke="white">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5a2 2 0 012-2h1.6a1 1 0 01.95.684l1.3 3.9a1 1 0 01-.24 1.054L7.6 10.6a16.007 16.007 0 006.8 6.8l1.962-1.962a1 1 0 011.054-.24l3.9 1.3a1 1 0 01.684.95V19a2 2 0 01-2 2h-.5C10.506 21 3 13.494 3 4.5V5z" />
                    </svg>&nbsp;
                    <a href="tel:+971569001020" class="text-white">971569001020+</a>
                </li>

                <li class="pb-2 d-flex align-items-center">
                    <!-- Phone Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px; width: 16px; margin-right: 0.5rem;" fill="none" viewBox="0 0 24 24" stroke="white">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5a2 2 0 012-2h1.6a1 1 0 01.95.684l1.3 3.9a1 1 0 01-.24 1.054L7.6 10.6a16.007 16.007 0 006.8 6.8l1.962-1.962a1 1 0 011.054-.24l3.9 1.3a1 1 0 01.684.95V19a2 2 0 01-2 2h-.5C10.506 21 3 13.494 3 4.5V5z" />
                    </svg>&nbsp;
                    <a href="tel:+97143931889" class="text-white">97143931889+</a>
                </li>

                <li class="pb-2 d-flex align-items-center">
                    <!-- Email Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px; width: 16px; margin-right: 0.5rem;" fill="none" viewBox="0 0 24 24" stroke="white">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m0-2H3a2 2 0 00-2 2v8a2 2 0 002 2h18a2 2 0 002-2V8a2 2 0 00-2-2z" />
                    </svg>&nbsp;
                    <a href="mailto:info@albyaninstitute.net" class="text-white">info@albyaninstitute.net</a>
                </li>
                </ul>

                <div class="map-container text-center pb-1">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14432.980440036239!2d55.3405061!3d25.2623388!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f5d12c2143dbf%3A0x59ed5335a90de4ba!2z2YXYudmH2K8g2KfZhNio2YrYp9mGINmE2YTYrtiv2YXYp9iqINin2YTYqti52YTZitmF2YrYqSAtIEFMQllBTiBJTlNUSVRVVEUgRURVQ0FUSU9OIFNVUFBPUlQgU0VSVklDRVM!5e0!3m2!1sen!2seg!4v1739955859681!5m2!1sen!2seg" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Albyan Institute Location Map - Dubai, UAE"></iframe>
                </div>
            </div>
            
        </div>
        {{--
        <div class="mt-40 border-blue py-25 d-flex align-items-center justify-content-between">
            <div class="footer-logo">
                <a href="/">
                    @if(!empty($generalSettings['footer_logo']))
                        <img src="{{ $generalSettings['footer_logo'] }}" class="img-cover" alt="footer logo">
                    @endif
                </a>
            </div>

            <div class="footer-social">
                @if(!empty($socials) and count($socials))
                    @foreach($socials as $social)
                        <a href="{{ $social['link'] }}" target="_blank">
                            <img src="{{ $social['image'] }}" alt="{{ $social['title'] }}" class="mr-15">
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
        --}}
    </div>

    @if(getOthersPersonalizationSettings('platform_phone_and_email_position') == 'footer')
        <div class="footer-copyright-card">
            <div class="container d-flex align-items-center justify-content-between py-15">
                <div class="font-14 text-white">{{ trans('update.platform_copyright_hint') }}</div>

                <div class="d-flex align-items-center justify-content-center">
                    @if(!empty($generalSettings['site_phone']))
                        <div class="d-flex align-items-center text-white font-14">
                            <i data-feather="phone" width="20" height="20" class="mr-10"></i>
                            <a class="text-white" style="direction: ltr" href="tel:{{ $generalSettings['site_phone'] }}"><span>{{ $generalSettings['site_phone'] }}</span></a>
                        </div>
                    @endif

                    @if(!empty($generalSettings['site_email']))
                        <div class="border-left mx-5 mx-lg-15 h-100"></div>

                        <div class="d-flex align-items-center text-white font-14">
                            <i data-feather="mail" width="20" height="20" class="mr-10"></i>
                            {{ $generalSettings['site_email'] }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

</footer>