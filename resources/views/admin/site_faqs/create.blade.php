@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/site-faqs">{{ trans('admin/main.site_faqs') }}</a></div>
                <div class="breadcrumb-item">{{ !empty($siteFaq) ? trans('admin/main.edit') : trans('admin/main.create') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <h2 class="section-title ml-4">{{ !empty($siteFaq) ? trans('admin/main.edit') : trans('admin/main.create') }} {{ trans('admin/main.site_faq') }}</h2>

                        <div class="card-body">
                            <form action="{{ getAdminPanelUrl() }}/site-faqs/{{ !empty($siteFaq) ? $siteFaq->id.'/update' : 'store' }}" method="post">
                                @csrf

                                <div class="row">
                                    <div class="col-12 col-lg-6">
                                        @if(!empty(getGeneralSettings('content_translate')))
                                            <div class="form-group">
                                                <label class="input-label">{{ trans('auth.language') }}</label>
                                                <select name="locale" class="form-control {{ !empty($siteFaq) ? 'js-edit-content-locale' : '' }}">
                                                    @foreach($userLanguages as $lang => $language)
                                                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                                    @endforeach
                                                </select>
                                                @error('locale')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @else
                                            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                        @endif

                                        <div class="form-group mt-15">
                                            <label class="input-label">{{ trans('admin/main.title') }}</label>
                                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ !empty($siteFaq) ? $siteFaq->title : old('title') }}" maxlength="255"/>
                                            @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-15">
                                    <label class="input-label">{{ trans('admin/main.answer') }}</label>
                                    <textarea id="js-site-faq-answer" name="answer" class="summernote form-control @error('answer') is-invalid @enderror">{!! !empty($siteFaq) ? $siteFaq->answer : old('answer') !!}</textarea>
                                    @error('answer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group custom-switches-stacked">
                                    <label class="custom-switch pl-0">
                                        <input type="hidden" name="status" value="disable">
                                        <input type="checkbox" name="status" id="siteFaqStatus" value="active" {{ (!empty($siteFaq) && $siteFaq->status == 'active') ? 'checked' : '' }} class="custom-switch-input"/>
                                        <span class="custom-switch-indicator"></span>
                                        <label class="custom-switch-description mb-0 cursor-pointer" for="siteFaqStatus">{{ trans('admin/main.active') }}</label>
                                    </label>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('textarea.summernote').summernote();
        });
    </script>
@endpush
