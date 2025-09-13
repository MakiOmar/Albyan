@extends(getTemplate().'.layouts.app')
@push('styles_top')
<style>
    #certificateModal{
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        margin:
        auto;
        background-color: #fff;
        z-index: 999;
        max-width: 500px;
        max-height: 500px;
        border-radius:20px;
        -webkit-box-shadow:
        0px 0px 5px 2px rgba(0,0,0,0.29);
        -moz-box-shadow: 0px 0px 5px 2px rgba(0,0,0,0.29);
        box-shadow: 0px 0px 5px 2px rgba(0,0,0,0.29);
        text-align: center;
        padding:
        20px;
    }
</style>
@endpush
@section('content')
    <div class="container">
        <div class="row login-container">
            <div class="col-12 col-md-6 pl-0">
                <img src="{{ getPageBackgroundSettings('certificate_validation') }}" class="img-cover" alt="Login">
            </div>

            <div class="col-12 col-md-6">

                <div class="login-card">
                    <h1 class="font-20 font-weight-bold">{{ trans('site.certificate_validation') }}</h1>
                    <p class="font-14 text-gray mt-15">{{ trans('site.certificate_validation_hint') }}</p>


                    <form method="post" action="{{ route('certificates.search') }}" class="mt-35" id="certificateSearchForm">
                        {{ csrf_field() }}


                        <div class="form-group">
                            <label class="input-label" for="code">{{ trans('public.certificate_id') }}:</label>
                            <input type="tel" name="certificate_id" class="form-control" id="certificate_id" aria-describedby="certificate_idHelp">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label class="input-label">{{ trans('site.captcha') }}</label>
                            <div class="row align-items-center">
                                <div class="col">
                                    <input type="text" name="captcha" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col d-flex align-items-center">
                                    <img id="captchaImageComment" class="captcha-image" src="">

                                    <button type="button" id="refreshCaptcha" class="btn-transparent ml-15">
                                        <i data-feather="refresh-ccw" width="24" height="24" class=""></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <input type="submit" class="btn btn-primary btn-block mt-20" value="{{ trans('cart.validate') }}">

                    </form>

                </div>
            </div>
        </div>
    </div>

    <div id="certificateModal" class="d-none">
        <h3 class="section-title after-line">{{ trans('site.certificate_is_valid') }}</h3>
        <div class="mt-25 d-flex flex-column align-items-center">
            <img src="/assets/default/img/check.png" alt="{{ trans('site.certificate_valid_icon') }}" width="120" height="117">
            <p class="mt-10">{{ trans('site.certificate_is_valid_hint') }}</p>
            <div class="w-75">

                <div class="mt-15 d-flex justify-content-between">
                    <span class="text-gray font-weight-bold">{{ trans('quiz.student') }}:</span>
                    <span class="text-gray modal-student"></span>
                </div>

                <div class="mt-10 d-flex justify-content-between">
                    <span class="text-gray font-weight-bold">{{ trans('public.date') }}:</span>
                    <span class="text-gray"><span class="modal-date"></span></span>
                </div>

                <div class="mt-10 d-flex justify-content-between">
                    <span class="text-gray font-weight-bold">{{ trans('main.webinar_title') }}:</span>
                    <span class="text-gray"><span class="modal-webinar"></span></span>
                </div>
            </div>
        </div>

        <div class="mt-30 d-flex align-items-center justify-content-end">
            <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
        </div>
    </div>

@endsection
{{--
@push('scripts_bottom')
    <script>
        var certificateNotFound = '{{ trans('site.certificate_not_found') }}';
        var close = '{{ trans('public.close') }}';
    </script>

    <script src="/assets/default/js/parts/certificate_validation.min.js"></script>
@endpush
--}}

@push('scripts_bottom')
    <script>
        var certificateNotFound = '{{ trans('site.certificate_not_found') }}';
        var close = '{{ trans('public.close') }}';

        $(document).ready(function () {
            function loadCaptcha() {
                $('#captchaImageComment').attr('src', '/captcha?'+Math.random());
            }

            loadCaptcha();

            $('#refreshCaptcha').on('click', function () {
                loadCaptcha();
            });

            $('#certificateSearchForm').on('submit', function (e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let data = form.serialize();

                // تنظيف الأخطاء السابقة
                form.find('.invalid-feedback').text('');
                form.find('.is-invalid').removeClass('is-invalid');

                $.post(url, data, function (response) {
                    if (response.code === 422) {
                        // أخطاء التحقق
                        $.each(response.errors, function (field, messages) {
                            let input = form.find('[name="' + field + '"]');
                            input.addClass('is-invalid');
                            input.next('.invalid-feedback').text(messages[0]);
                        });
                        loadCaptcha();
                    } else {
                        // عرض بيانات الشهادة
                        let cert = response.certificates[0];
                        if (cert) {
                            $('.modal-student').text(cert.student_name ?? '');
                            $('.modal-date').text(cert.created_at ?? '');
                            $('.modal-webinar').text(cert.webinar_title ?? '');

                            $('#certificateModal').removeClass('d-none');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: certificateNotFound,
                                confirmButtonText: close,
                            });
                        }
                        loadCaptcha();
                    }
                }).fail(function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'حدث خطأ ربما رقم الشهادة أو رمز الأمان غير صحيح',
                        confirmButtonText: close,
                    });
                    loadCaptcha();
                });
            });

            // إغلاق المودال
            $('.close-swl').on('click', function () {
                $('#certificateModal').addClass('d-none');
            });
        });
    </script>
@endpush
