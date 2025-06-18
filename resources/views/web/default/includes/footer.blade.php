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

                            <div class="form-group d-flex align-items-center m-0">
                                <div class="w-100">
                                    <input type="text" name="newsletter_email" class="form-control border-0 @error('newsletter_email') is-invalid @enderror" placeholder="{{ trans('footer.enter_email_here') }}"/>
                                    @error('newsletter_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary rounded-pill">{{ trans('footer.join') }}</button>
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
                            <img src="{{ $generalSettings['footer_logo'] }}" class="img-cover" alt="footer logo">
                        @endif
                    </a>
                </div>

                <div class="footer-social mt-2 mb-4">
                    <h3>تابعنا على</h3>
                    @include('web.default.includes.title_border')
                    @if(!empty($socials) and count($socials))
                    <div class="mt-2">
                        @foreach($socials as $social)
                            <a href="{{ $social['link'] }}" target="_blank">
                                <img src="{{ $social['image'] }}" alt="{{ $social['title'] }}" class="mr-5">
                            </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                <h3>روابط هامة</h3>
                @include('web.default.includes.title_border')
                
                <ul class="list-unstyled  mt-4">
                    <li class="pb-2"><a href="/contact">اتصل بنا</a></li>
                    <li class="pb-2"><a href="/certificate_validation">التحقق من صحة الشهادة</a></li>
                    <li class="pb-2"><a href="/pages/terms-and-conditions">الشروط والقواعد</a></li>
                    <li class="pb-2"><a href="/about">معلومات عنا</a></li>
                </ul>
            </div>

            <div class="col-md-5 info-section">
              <h3>معلومات عنا</h3>
              @include('web.default.includes.title_border')
                <p class=" mt-4">معهد البيان للخدمات التعليمية يقدم تجربة تعليمية متميزة مع نخبة من المحاضرين والخبراء في مختلف المجالات. يقدم المعهد مئات الدبلومات التدريبية الاحترافية المصممة لتلبية احتياجات سوق العمل، مع خيارات مرنة في الحضور من مقر المعهد أو الدراسة أون لاين. يمنح المعهد شهادات معتمدة محلياً ودولياً تعزز من مكانتك المهنية وينظم حفل تخرج سنوي ضخم لتكريم اعداد كبيرة من خريجي المعهدبمختلف التخصصات بحضور شخصيات هامة. انضم إلى معهد البيان للارتقاء بمسارك المهني. </p>
            </div>

                        
            <div class="col-md-4 info-section">
                <h3>اتصل بنا</h3>
                @include('web.default.includes.title_border')
                <ul class="list-unstyled mt-4">
                    <li class="pb-2">📞 <a href="tel:+971569001020">971569001020+</a></li>
                    <li class="pb-2">📞 <a href="tel:+97143931889">971043931889+</a></li>
                    <li class="pb-2">📧 <a href="mailto:info@albyaninstitute.net">info@albyaninstitute.net</a></li>
                </ul>
                <div class="map-container text-center pb-1">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14432.980440036239!2d55.3405061!3d25.2623388!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f5d12c2143dbf%3A0x59ed5335a90de4ba!2z2YXYudmH2K8g2KfZhNio2YrYp9mGINmE2YTYrtiv2YXYp9iqINin2YTYqti52YTZitmF2YrYqSAtIEFMQllBTiBJTlNUSVRVVEUgRURVQ0FUSU9OIFNVUFBPUlQgU0VSVklDRVM!5e0!3m2!1sen!2seg!4v1739955859681!5m2!1sen!2seg" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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