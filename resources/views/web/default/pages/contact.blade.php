@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/leaflet/leaflet.css">
@endpush


@section('content')

    <section class="search-top-banner opacity-04 position-relative">
            {{--
        <img src="{{ $contactSettings['background'] }}" class="img-cover" alt="{{ $pageTitle ?? '' }}"/>
            --}}
        <div class="container h-100">
            <div class="row p-4 h-100 justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <div class="top-search-categories-form">
                        <h1 class="font-30 mb-15">{{ trans('site.contact_us') }}</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        {{--
        <section class="">
            
            @if(!empty($contactSettings['latitude']) and !empty($contactSettings['longitude']))
                <div class="contact-map" id="contactMap"
                     data-latitude="{{ $contactSettings['latitude'] }}"
                     data-longitude="{{ $contactSettings['longitude'] }}"
                     data-zoom="{{ $contactSettings['map_zoom'] ?? 12 }}"
                ></div>
            @endif

            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="contact-items mt-30 rounded-lg py-20 py-md-40 px-15 px-md-30 text-center">
                        <div class="contact-icon-box box-info p-20 d-flex align-items-center justify-content-center mx-auto">
                            <i data-feather="map-pin" width="50" height="50" class="text-white"></i>
                        </div>

                        <h3 class="mt-30 font-16 font-weight-bold text-dark-blue">{{ trans('site.our_address') }}</h3>
                        @if(!empty($contactSettings['address']))
                            <p class="font-weight-500 font-14 text-gray mt-10">{!! nl2br($contactSettings['address']) !!}</p>
                        @else
                            <p class="font-weight-500 text-gray font-14 mt-10">{{ trans('site.not_defined') }}</p>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="contact-items mt-30 rounded-lg py-20 py-md-40 px-15 px-md-30 text-center">
                        <div class="contact-icon-box box-green p-20 d-flex align-items-center justify-content-center mx-auto">
                            <i data-feather="phone" width="50" height="50" class="text-white"></i>
                        </div>

                        <h3 class="mt-30 font-16 font-weight-bold text-dark-blue">{{ trans('site.phone_number') }}</h3>
                        @if(!empty($contactSettings['phones']))
                            <p class="font-weight-500 text-gray font-14 mt-10">{!! nl2br(str_replace(',','<br/>',$contactSettings['phones'])) !!}</p>
                        @else
                            <p class="font-weight-500 text-gray font-14 mt-10">{{ trans('site.not_defined') }}</p>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="contact-items mt-30 rounded-lg py-20 py-md-40 px-15 px-md-30 text-center">
                        <div class="contact-icon-box box-red p-20 d-flex align-items-center justify-content-center mx-auto">
                            <i data-feather="mail" width="50" height="50" class="text-white"></i>
                        </div>

                        <h3 class="mt-30 font-16 font-weight-bold text-dark-blue">{{ trans('public.email') }}</h3>
                        @if(!empty($contactSettings['emails']))
                            <p class="font-weight-500 text-gray font-14 mt-10">{!! nl2br(str_replace(',','<br/>',$contactSettings['emails'])) !!}</p>
                        @else
                            <p class="font-weight-500 text-gray font-14 mt-10">{{ trans('site.not_defined') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        --}}
        <div class="row">
            @include('web.default.pages.includes.about_text')
        </div>
        <div class="row">
            <div class="col-12 col-md-6 p-3">
                <section class="mt-30 mt-md-50">
                    <h2 class="font-16 font-weight-bold text-secondary">{{ trans('site.send_your_message_directly') }}</h2>
        
                    @if(!empty(session()->has('msg')))
                        <div class="alert alert-success my-25 d-flex align-items-center">
                            <i data-feather="check-square" width="50" height="50" class="mr-2"></i>
                            {{ session()->get('msg') }}
                        </div>
                    @endif
        
                    <form action="/contact/store" method="post" class="mt-20">
                        {{ csrf_field() }}
        
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label font-weight-500" for="contact-name">{{ trans('site.your_name') }}</label>
                                    {{-- Name: at least two words (validated server-side) --}}
                                    <input type="text" name="name" id="contact-name" value="{{ old('name') }}" autocomplete="name" maxlength="255" class="form-control @error('name')  is-invalid @enderror" required/>
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label font-weight-500" for="contact-email">{{ trans('public.email') }}</label>
                                    <input type="email" name="email" id="contact-email" value="{{ old('email') }}" autocomplete="email" maxlength="255" class="form-control @error('email')  is-invalid @enderror" required/>
                                    @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
        
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label font-weight-500" for="contact-phone">{{ trans('site.phone_number') }}</label>
                                    <input type="tel" name="phone" id="contact-phone" value="{{ old('phone') }}" autocomplete="tel" minlength="6" maxlength="40" class="form-control @error('phone')  is-invalid @enderror" required/>
                                    @error('phone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="input-label font-weight-500" for="contact-subject">{{ trans('site.subject') }}</label>
                                    <input type="text" name="subject" id="contact-subject" value="{{ old('subject') }}" minlength="2" maxlength="255" class="form-control @error('subject')  is-invalid @enderror" required/>
                                    @error('subject')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="input-label font-weight-500" for="contact-message">{{ trans('site.message') }}</label>
                                    {{-- Message: minimum 100 characters (server-side) --}}
                                    <textarea name="message" id="contact-message" rows="10" minlength="100" maxlength="10000" class="form-control @error('message')  is-invalid @enderror" required>{{ old('message') }}</textarea>
                                    @error('message')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
        
                        <div class="row">
                            <div class="col-12">
                                @include('web.default.includes.turnstile_widget')
                            </div>
                        </div>
        
                        <button type="submit" class="btn btn-primary mt-20">{{ trans('site.send_message') }}</button>
                    </form>
                </section>
            </div>
            <div class="col-12 col-md-6 p-3">
                <section class="mt-30 mt-md-50">
                    <h2 class="font-16 font-weight-bold text-secondary mb-2">اتصل بنا</h2>
                    <ul class="list-unstyled mb-2">
                        <li>📞 <a href="tel:+971569001020">971569001020+</a></li>
                        <li>📞 <a href="tel:+97143931889">971043931889+</a></li>
                        <li>📧 <a href="mailto:info@albayaninstitute.net">info@albayaninstitute.net</a></li>
                    </ul>
                    <div class="map-container text-center pb-1">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14432.980440036239!2d55.3405061!3d25.2623388!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f5d12c2143dbf%3A0x59ed5335a90de4ba!2z2YXYudmH2K8g2KfZhNio2YrYp9mGINmE2YTYrtiv2YXYp9iqINin2YTYqti52YTZitmF2YrYqSAtIEFMQllBTiBJTlNUSVRVVEUgRURVQ0FUSU9OIFNVUFBPUlQgU0VSVklDRVM!5e0!3m2!1sen!2seg!4v1739955859681!5m2!1sen!2seg" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Albyan Institute Location Map - Dubai, UAE"></iframe>
                    </div>
                </section>
            </div>
        </div>
        

    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/leaflet/leaflet.min.js"></script>
    <script>
        var leafletApiPath = '{{ getLeafletApiPath() }}';
    </script>
    <script src="/assets/default/js/parts/contact.min.js"></script>
@endpush
