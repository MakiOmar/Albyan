@extends('admin.layouts.app')

@php
    $pageContent = !empty($page) ? $page->content : old('content');
    $contentEditorMode = old('content_editor_mode', 'visual');
    if ($contentEditorMode !== 'html' && !empty($pageContent) && preg_match('/<(header|main|section|svg|footer|nav|article|aside|script|style)[\s>]|<\!--/i', $pageContent)) {
        $contentEditorMode = 'html';
    }
@endphp

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.additional_pages_title') }}</div>
            </div>
        </div>


        <div class="section-body">

            <div class="d-flex align-items-center justify-content-between">
                <div class="">
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ getAdminPanelUrl() }}/pages/{{ !empty($page) ? $page->id.'/update' : 'store' }}" method="Post">
                                {{ csrf_field() }}

                                <div class="row">
                                    <div class="col-12 col-lg-6">

                                        @if(!empty(getGeneralSettings('content_translate')))
                                            <div class="form-group">
                                                <label class="input-label">{{ trans('auth.language') }}</label>
                                                <select name="locale" class="form-control {{ !empty($page) ? 'js-edit-content-locale' : '' }}">
                                                    @foreach($userLanguages as $lang => $language)
                                                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                                    @endforeach
                                                </select>
                                                @error('locale')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        @else
                                            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                        @endif


                                        <div class="form-group">
                                            <label>{{ trans('admin/main.name') }}</label>
                                            <input type="text" name="name" class="form-control  @error('name') is-invalid @enderror"
                                                   value="{{ !empty($page) ? $page->name : old('name') }}" />
                                            @error('name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>{{ trans('admin/main.link') }}</label>
                                            <input type="text" name="link" class="form-control  @error('link') is-invalid @enderror"
                                                   value="{{ !empty($page) ? $page->link : old('link') }}"/>
                                            @error('link')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                            <div class="text-muted text-small mt-1">{{ trans('admin/main.new_page_link_hint') }}</div>
                                        </div>

                                        <div class="form-group">
                                            <label>{{ trans('admin/main.title') }}</label>
                                            <input type="text" name="title" class="form-control  @error('title') is-invalid @enderror"
                                                   value="{{ !empty($page) ? $page->title : old('title') }}" placeholder="{{ trans('admin/main.pages_title_placeholder') }}"/>
                                            @error('title')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>{{ trans('admin/main.seo_description') }}</label>
                                            <textarea name="seo_description" class="form-control  @error('seo_description') is-invalid @enderror" rows="4">{{ !empty($page) ? $page->seo_description : old('seo_description') }}</textarea>
                                            @error('seo_description')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                    </div>
                                </div>

                                <div class="form-group mt-15">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                        <label class="input-label mb-0">{{ trans('admin/main.content') }}</label>
                                        <label class="custom-switch pl-0 mb-0">
                                            <span class="custom-switch-description mb-0 mr-2">{{ trans('admin/main.page_content_editor_visual') }}</span>
                                            <input type="checkbox" id="js-page-content-editor-toggle" class="custom-switch-input" {{ $contentEditorMode === 'html' ? 'checked' : '' }} />
                                            <span class="custom-switch-indicator"></span>
                                            <span class="custom-switch-description mb-0 ml-2">{{ trans('admin/main.page_content_editor_html') }}</span>
                                        </label>
                                    </div>
                                    <input type="hidden" name="content_editor_mode" id="js-page-content-editor-mode" value="{{ $contentEditorMode }}" />
                                    <p class="text-muted text-small mb-2 js-page-editor-hint-visual {{ $contentEditorMode === 'html' ? 'd-none' : '' }}">{{ trans('admin/main.page_content_visual_hint') }}</p>
                                    <p class="text-muted text-small mb-2 js-page-editor-hint-html {{ $contentEditorMode === 'html' ? '' : 'd-none' }}">{{ trans('admin/main.page_content_html_hint') }}</p>
                                    <textarea id="js-page-content" name="content" rows="24" class="form-control @error('content') is-invalid @enderror {{ $contentEditorMode === 'html' ? 'font-monospace' : '' }}" spellcheck="false">{!! $pageContent !!}</textarea>
                                    @error('content')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                {{-- Per-page custom assets (styles, scripts, head & footer markup) --}}
                                <div class="form-group mt-20">
                                    <label class="input-label d-block">{{ trans('admin/main.page_custom_assets') }}</label>
                                    <p class="text-muted text-small mb-3">{{ trans('admin/main.page_custom_assets_hint') }}</p>

                                    <ul class="nav nav-pills" id="pageCustomAssetsTab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="page-styles-tab" data-toggle="tab" href="#page-styles" role="tab" aria-controls="page-styles" aria-selected="true">{{ trans('admin/main.page_styles') }}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="page-scripts-tab" data-toggle="tab" href="#page-scripts" role="tab" aria-controls="page-scripts" aria-selected="false">{{ trans('admin/main.page_scripts') }}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="page-head-tab" data-toggle="tab" href="#page-head" role="tab" aria-controls="page-head" aria-selected="false">{{ trans('admin/main.page_head_content') }}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="page-footer-tab" data-toggle="tab" href="#page-footer" role="tab" aria-controls="page-footer" aria-selected="false">{{ trans('admin/main.page_footer_content') }}</a>
                                        </li>
                                    </ul>

                                    <div class="tab-content border rounded p-3 mt-2" id="pageCustomAssetsTabContent">
                                        <div class="tab-pane fade show active" id="page-styles" role="tabpanel" aria-labelledby="page-styles-tab">
                                            <label class="input-label">{{ trans('admin/main.page_styles') }}</label>
                                            <textarea name="styles" class="form-control font-monospace @error('styles') is-invalid @enderror" rows="8" placeholder="{{ trans('admin/main.page_styles_placeholder') }}">{{ !empty($page) ? $page->styles : old('styles') }}</textarea>
                                            <div class="text-muted text-small mt-1">{{ trans('admin/main.page_styles_hint') }}</div>
                                            @error('styles')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="tab-pane fade" id="page-scripts" role="tabpanel" aria-labelledby="page-scripts-tab">
                                            <label class="input-label">{{ trans('admin/main.page_scripts') }}</label>
                                            <textarea name="scripts" class="form-control font-monospace @error('scripts') is-invalid @enderror" rows="8" placeholder="{{ trans('admin/main.page_scripts_placeholder') }}">{{ !empty($page) ? $page->scripts : old('scripts') }}</textarea>
                                            <div class="text-muted text-small mt-1">{{ trans('admin/main.page_scripts_hint') }}</div>
                                            @error('scripts')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="tab-pane fade" id="page-head" role="tabpanel" aria-labelledby="page-head-tab">
                                            <label class="input-label">{{ trans('admin/main.page_head_content') }}</label>
                                            <textarea name="head_content" class="form-control font-monospace @error('head_content') is-invalid @enderror" rows="8" placeholder="{{ trans('admin/main.page_head_content_placeholder') }}">{{ !empty($page) ? $page->head_content : old('head_content') }}</textarea>
                                            <div class="text-muted text-small mt-1">{{ trans('admin/main.page_head_content_hint') }}</div>
                                            @error('head_content')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="tab-pane fade" id="page-footer" role="tabpanel" aria-labelledby="page-footer-tab">
                                            <label class="input-label">{{ trans('admin/main.page_footer_content') }}</label>
                                            <textarea name="footer_content" class="form-control font-monospace @error('footer_content') is-invalid @enderror" rows="8" placeholder="{{ trans('admin/main.page_footer_content_placeholder') }}">{{ !empty($page) ? $page->footer_content : old('footer_content') }}</textarea>
                                            <div class="text-muted text-small mt-1">{{ trans('admin/main.page_footer_content_hint') }}</div>
                                            @error('footer_content')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group custom-switches-stacked">
                                    <label class="input-label">{{ trans('admin/main.status') }}:</label>
                                    <label class="custom-switch pl-0">
                                        <label class="custom-switch-description mb-0 mr-2">{{ trans('admin/main.draft') }}</label>
                                        <input type="hidden" name="status" value="draft">
                                        <input type="checkbox" name="status" id="pageStatus" value="publish" {{ (!empty($page) and $page->status == 'publish') ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                        <span class="custom-switch-indicator"></span>
                                        <label class="custom-switch-description mb-0 cursor-pointer" for="pageStatus">{{ trans('admin/main.publish') }}</label>
                                    </label>
                                </div>

                                <div class="form-group custom-switches-stacked">
                                    <label class="input-label">{{ trans('admin/main.robot') }}:</label>
                                    <label class="custom-switch pl-0">
                                        <label class="custom-switch-description mb-0 mr-2">{{ trans('admin/main.no_follow') }}</label>
                                        <input type="hidden" name="robot" value="0">
                                        <input type="checkbox" name="robot" id="pageRobot" value="1" {{ (!empty($page) and $page->robot) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                        <span class="custom-switch-indicator"></span>
                                        <label class="custom-switch-description mb-0 cursor-pointer" for="pageRobot">{{ trans('admin/main.follow') }}</label>
                                    </label>
                                </div>

                                <div class=" mt-4">
                                    <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
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
        (function ($) {
            "use strict";

            var $textarea = $('#js-page-content');
            var $modeInput = $('#js-page-content-editor-mode');
            var $toggle = $('#js-page-content-editor-toggle');
            var summernoteActive = false;
            var initialMode = @json($contentEditorMode);

            function isHtmlMode() {
                return $toggle.is(':checked');
            }

            function updateHints(htmlMode) {
                $('.js-page-editor-hint-visual').toggleClass('d-none', htmlMode);
                $('.js-page-editor-hint-html').toggleClass('d-none', !htmlMode);
            }

            function initSummernote() {
                if (summernoteActive || !jQuery().summernote || typeof window.makeSummernote !== 'function') {
                    return;
                }

                window.makeSummernote($textarea, 400);
                summernoteActive = true;
            }

            function destroySummernote() {
                if (!summernoteActive) {
                    return;
                }

                var code = $textarea.summernote('code');
                $textarea.summernote('destroy');
                $textarea.val(code);
                summernoteActive = false;
            }

            function setEditorMode(htmlMode) {
                if (htmlMode) {
                    if (summernoteActive) {
                        destroySummernote();
                    }

                    $textarea.addClass('font-monospace');
                    $modeInput.val('html');
                    $toggle.prop('checked', true);
                } else {
                    var code = $textarea.val();
                    $textarea.removeClass('font-monospace');
                    initSummernote();

                    if (summernoteActive && code) {
                        $textarea.summernote('code', code);
                    }

                    $modeInput.val('visual');
                    $toggle.prop('checked', false);
                }

                updateHints(htmlMode);
            }

            $toggle.on('change', function () {
                if (isHtmlMode()) {
                    setEditorMode(true);
                    return;
                }

                Swal.fire({
                    title: @json(trans('admin/main.page_content_switch_to_visual_title')),
                    text: @json(trans('admin/main.page_content_switch_to_visual_warning')),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: @json(trans('admin/main.page_content_switch_to_visual_confirm')),
                    cancelButtonText: @json(trans('public.cancel')),
                }).then(function (result) {
                    if (result.isConfirmed) {
                        setEditorMode(false);
                    } else {
                        $toggle.prop('checked', true);
                    }
                });
            });

            $('form').on('submit', function () {
                if (summernoteActive) {
                    $textarea.val($textarea.summernote('code'));
                    destroySummernote();
                }

                $modeInput.val(isHtmlMode() ? 'html' : 'visual');
            });

            setEditorMode(initialMode === 'html');
        })(jQuery);
    </script>
@endpush
