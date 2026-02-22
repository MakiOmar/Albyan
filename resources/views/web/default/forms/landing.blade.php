@extends('web.default.layouts.landing')

@section('content')
    {{-- Expiry notice --}}
    @if(!empty($form->end_date))
        <div class="d-flex align-items-center mb-40 rounded-lg border border-gray200 p-15">
            <div class="size-40 d-flex-center rounded-circle bg-gray200">
                <i data-feather="calendar" class="text-gray" width="20" height="20"></i>
            </div>
            <div class="ml-5">
                <h4 class="font-14 font-weight-bold text-gray">{{ trans('update.notice') }}</h4>
                <p class="font-12 text-gray">{{ trans('update.this_form_will_be_expired_on_date',['date' => dateTimeFormat($form->end_date, 'j M Y')]) }}</p>
            </div>
        </div>
    @endif

    {{-- Heading and description (no form image; background is /store/1/1.png) --}}
    @if(!empty($form->heading_title))
        <h3 class="font-24">{{ $form->heading_title }}</h3>
    @endif
    @if(!empty($form->description))
        <div class="forms-body-welcome-message white-space-pre-wrap mt-15 font-14 text-gray">{!! $form->description !!}</div>
    @endif

    {{-- Lead form --}}
    <form action="{{ url('/landing/store') }}" method="post" class="mt-30">
        {{ csrf_field() }}

        @include('web.default.forms.handle_field', ['fields' => $form->fields])

        <div class="d-flex align-items-center justify-content-center mt-30">
            <button type="button" class="js-clear-form btn btn-danger mr-10">{{ trans('update.clear_form') }}</button>
            <button type="submit" class="btn btn-primary">{{ trans('update.submit_form') }}</button>
        </div>
    </form>

    {{-- Large WhatsApp and Call buttons (from config: LANDING_WHATSAPP_NUMBER, LANDING_CALL_NUMBER) --}}
    <div class="mt-20 d-flex flex-column flex-md-row align-items-center justify-content-center gap-3">
        @if(!empty(config('landing.whatsapp_number')))
            @php
                $whatsappDigits = preg_replace('/\D/', '', config('landing.whatsapp_number'));
            @endphp
            <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer" class="btn btn-lg d-flex align-items-center justify-content-center mb-2" style="min-width: 220px; background-color: #43c353; border-color: #43c353;">
                <i data-feather="message-circle" class="mr-2" width="22" height="22"></i>
                {{ trans('update.contact_on_whatsapp') }}
            </a>
        @endif
        @if(!empty(config('landing.call_number')))
            <a href="tel:{{ config('landing.call_number') }}" class="btn btn-secondary btn-lg d-flex align-items-center justify-content-center" style="min-width: 220px;">
                <i data-feather="phone" class="mr-2" width="22" height="22"></i>
                {{ trans('update.call_us') }}
            </a>
        @endif
    </div>
@endsection

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@push('scripts_bottom')
    <script src="/assets/default/js/admin/form_submissions_details.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/js/parts/forms.min.js"></script>
    <script>if (typeof feather !== 'undefined') feather.replace();</script>
@endpush
