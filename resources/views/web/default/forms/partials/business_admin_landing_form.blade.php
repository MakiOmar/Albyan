{{-- Registration form block for business admin landing (hero #register) --}}
<div class="csl-form-wrap">
    <h3 class="csl-hero-form-title text-center mb-3">تم فتح باب القبول الآن – المقاعد محدودة</h3>

    @if(!empty($form->heading_title))
        <h4 class="font-24 mb-2">{{ $form->heading_title }}</h4>
    @endif
    @if(!empty($form->description))
        <div class="font-14 text-gray mb-3">{!! $form->description !!}</div>
    @endif

    @if(!empty($form->end_date))
        <div class="alert alert-warning font-12 mb-3">
            {{ trans('update.this_form_will_be_expired_on_date',['date' => dateTimeFormat($form->end_date, 'j M Y')]) }}
        </div>
    @endif

    <form action="{{ url('/landing/business-admin/store') }}" method="post">
        {{ csrf_field() }}
        @include('web.default.forms.handle_field', ['fields' => $form->fields])
        @include('web.default.includes.turnstile_widget')
        <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 mt-4">
            <button type="button" class="js-clear-form btn btn-outline-secondary flex-fill">{{ trans('update.clear_form') }}</button>
            <button type="submit" class="btn btn-primary flex-fill font-weight-bold">سجل الحين</button>
        </div>
    </form>

    <div class="mt-4 d-flex flex-column gap-2">
        @if(!empty($cslWhatsappDigits))
            <a href="https://wa.me/{{ $cslWhatsappDigits }}" target="_blank" rel="noopener" class="csl-btn csl-btn-whatsapp w-100">
                تواصل مباشرة عبر الواتساب
            </a>
        @endif
        @if(!empty($cslCall))
            <a href="tel:{{ $cslCall }}" class="csl-btn csl-btn-outline w-100" style="color:#01477d !important;border-color:#01477d;">
                {{ trans('update.call_us') }}
            </a>
        @endif
    </div>
</div>
