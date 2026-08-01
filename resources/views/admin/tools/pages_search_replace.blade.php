@extends('admin.layouts.app')

@section('content')
    {{-- Pages search & replace under Tools --}}
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ trans('admin/main.tools') }}</div>
                <div class="breadcrumb-item">{{ trans('admin/main.tools_pages_search_replace') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted">{{ trans('admin/main.page_search_replace_hint') }}</p>

                            <form id="js-page-search-replace-form" onsubmit="return false;">
                                {{ csrf_field() }}

                                <div class="row">
                                    <div class="col-12 col-lg-6">
                                        <div class="form-group">
                                            <label class="input-label">{{ trans('admin/main.page_search_replace_search') }}</label>
                                            <input type="text" name="search" class="form-control" required placeholder="{{ trans('admin/main.page_search_replace_search_placeholder') }}" />
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <div class="form-group">
                                            <label class="input-label">{{ trans('admin/main.page_search_replace_replace') }}</label>
                                            <input type="text" name="replace" class="form-control" placeholder="{{ trans('admin/main.page_search_replace_replace_placeholder') }}" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-lg-6">
                                        <div class="form-group">
                                            <label class="input-label">{{ trans('admin/main.page_search_replace_pages') }}</label>
                                            <select name="page_ids[]" class="form-control select2" multiple data-placeholder="{{ trans('admin/main.page_search_replace_all_pages') }}">
                                                @foreach($pages as $page)
                                                    <option value="{{ $page->id }}">{{ $page->name }} ({{ $page->link }})</option>
                                                @endforeach
                                            </select>
                                            <div class="text-muted text-small mt-1">{{ trans('admin/main.page_search_replace_pages_hint') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        @if(!empty(getGeneralSettings('content_translate')))
                                            <div class="form-group">
                                                <label class="input-label">{{ trans('auth.language') }}</label>
                                                <select name="locale" class="form-control">
                                                    <option value="">{{ trans('admin/main.page_search_replace_all_languages') }}</option>
                                                    @foreach($userLanguages as $lang => $language)
                                                        <option value="{{ $lang }}">{{ $language }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="input-label d-block">{{ trans('admin/main.page_search_replace_fields') }}</label>
                                    <div class="row">
                                        @foreach($fieldOptions as $fieldKey => $fieldLabel)
                                            <div class="col-6 col-md-4 col-lg-3 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="fields[]" value="{{ $fieldKey }}" class="custom-control-input" id="field-{{ $fieldKey }}" {{ $fieldKey === 'content' ? 'checked' : '' }} />
                                                    <label class="custom-control-label" for="field-{{ $fieldKey }}">{{ $fieldLabel }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-group d-flex flex-wrap">
                                    <div class="custom-control custom-checkbox mr-4 mb-2">
                                        <input type="checkbox" name="case_sensitive" value="1" class="custom-control-input" id="case-sensitive" />
                                        <label class="custom-control-label" for="case-sensitive">{{ trans('admin/main.page_search_replace_case_sensitive') }}</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" name="whole_word" value="1" class="custom-control-input" id="whole-word" />
                                        <label class="custom-control-label" for="whole-word">{{ trans('admin/main.page_search_replace_whole_word') }}</label>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap">
                                    <button type="button" class="btn btn-primary mb-2" id="js-page-search-replace-preview">
                                        {{ trans('admin/main.page_search_replace_preview') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card d-none" id="js-page-search-replace-results-card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                            <h4 class="mb-2 mb-md-0">{{ trans('admin/main.page_search_replace_results') }}</h4>
                            <button type="button" class="btn btn-danger btn-sm mb-2 mb-md-0" id="js-page-search-replace-apply" disabled>
                                {{ trans('admin/main.page_search_replace_apply_selected') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="font-weight-bold mb-3" id="js-page-search-replace-summary"></p>
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 40px;">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="js-page-select-all" />
                                                    <label class="custom-control-label" for="js-page-select-all"></label>
                                                </div>
                                            </th>
                                            <th>{{ trans('admin/main.name') }}</th>
                                            <th>{{ trans('admin/main.link') }}</th>
                                            <th>{{ trans('auth.language') }}</th>
                                            <th>{{ trans('admin/main.page_search_replace_field') }}</th>
                                            <th class="text-center">{{ trans('admin/main.page_search_replace_occurrences') }}</th>
                                            <th>{{ trans('admin/main.page_search_replace_snippet') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="js-page-search-replace-results-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        (function ($) {
            "use strict";

            var $form = $('#js-page-search-replace-form');
            var $resultsCard = $('#js-page-search-replace-results-card');
            var $resultsBody = $('#js-page-search-replace-results-body');
            var $summary = $('#js-page-search-replace-summary');
            var $applyBtn = $('#js-page-search-replace-apply');
            var $selectAll = $('#js-page-select-all');
            var previewUrl = '{{ getAdminPanelUrl() }}/tools/pages-search-replace/preview';
            var applyUrl = '{{ getAdminPanelUrl() }}/tools/pages-search-replace/apply';
            var summaryTemplate = @json(trans('admin/main.page_search_replace_summary'));
            var noMatchesText = @json(trans('admin/main.page_search_replace_no_matches'));
            var applyTitle = @json(trans('admin/main.page_search_replace_apply_title'));
            var applyText = @json(trans('admin/main.page_search_replace_apply_warning'));
            var applyConfirm = @json(trans('admin/main.page_search_replace_apply_confirm'));
            var cancelText = @json(trans('public.cancel'));
            var allLanguagesText = @json(trans('admin/main.page_search_replace_all_languages'));
            var selectRequiredText = @json(trans('admin/main.page_search_replace_select_required'));

            function getFormPayload() {
                var fields = [];
                $form.find('input[name="fields[]"]:checked').each(function () {
                    fields.push($(this).val());
                });

                return {
                    search: $form.find('[name="search"]').val(),
                    replace: $form.find('[name="replace"]').val(),
                    fields: fields,
                    page_ids: $form.find('[name="page_ids[]"]').val() || [],
                    locale: $form.find('[name="locale"]').val() || '',
                    case_sensitive: $form.find('[name="case_sensitive"]').is(':checked') ? 1 : 0,
                    whole_word: $form.find('[name="whole_word"]').is(':checked') ? 1 : 0,
                    _token: $form.find('[name="_token"]').val(),
                };
            }

            function validateForm(payload) {
                if (!payload.search || !payload.search.trim()) {
                    $.toast({
                        heading: @json(trans('public.request_failed')),
                        text: @json(trans('admin/main.page_search_replace_search_required')),
                        bgColor: '#f63c3c',
                        textColor: 'white',
                        hideAfter: 5000,
                        position: 'bottom-right',
                        icon: 'error'
                    });
                    return false;
                }

                if (!payload.fields.length) {
                    $.toast({
                        heading: @json(trans('public.request_failed')),
                        text: @json(trans('admin/main.page_search_replace_fields_required')),
                        bgColor: '#f63c3c',
                        textColor: 'white',
                        hideAfter: 5000,
                        position: 'bottom-right',
                        icon: 'error'
                    });
                    return false;
                }

                return true;
            }

            function getSelectedMatches() {
                var selections = [];

                $resultsBody.find('.js-page-match-check:checked').each(function () {
                    selections.push({
                        page_id: $(this).data('page-id'),
                        locale: $(this).data('locale') || '',
                        field: $(this).data('field'),
                    });
                });

                return selections;
            }

            function syncApplyButton() {
                $applyBtn.prop('disabled', getSelectedMatches().length === 0);
            }

            function resetPreviewState() {
                $resultsCard.addClass('d-none');
                $resultsBody.empty();
                $selectAll.prop('checked', false);
                $applyBtn.prop('disabled', true);
            }

            function renderResults(data) {
                $resultsBody.empty();
                $selectAll.prop('checked', false);
                $applyBtn.prop('disabled', true);

                if (!data.matches || !data.matches.length) {
                    $summary.text(noMatchesText);
                    $resultsCard.removeClass('d-none');
                    return;
                }

                $summary.text(
                    summaryTemplate
                        .replace(':occurrences', data.total_occurrences)
                        .replace(':records', data.affected_records)
                );

                data.matches.forEach(function (match, index) {
                    var checkboxId = 'page-match-' + index;
                    var locale = match.locale || '';

                    $resultsBody.append(
                        '<tr>' +
                        '<td class="text-center">' +
                        '<div class="custom-control custom-checkbox">' +
                        '<input type="checkbox" class="custom-control-input js-page-match-check" id="' + checkboxId + '" ' +
                        'data-page-id="' + match.page_id + '" ' +
                        'data-locale="' + $('<div>').text(locale).html() + '" ' +
                        'data-field="' + $('<div>').text(match.field).html() + '" />' +
                        '<label class="custom-control-label" for="' + checkboxId + '"></label>' +
                        '</div>' +
                        '</td>' +
                        '<td>' + $('<div>').text(match.page_name).html() + '</td>' +
                        '<td>' + $('<div>').text(match.page_link).html() + '</td>' +
                        '<td>' + $('<div>').text(match.locale || allLanguagesText).html() + '</td>' +
                        '<td>' + $('<div>').text(match.field_label).html() + '</td>' +
                        '<td class="text-center">' + match.occurrences + '</td>' +
                        '<td><code class="font-12">' + $('<div>').text(match.snippet).html() + '</code></td>' +
                        '</tr>'
                    );
                });

                $resultsCard.removeClass('d-none');
            }

            $form.find('input, select').on('change input', function () {
                resetPreviewState();
            });

            $selectAll.on('change', function () {
                $resultsBody.find('.js-page-match-check').prop('checked', $(this).is(':checked'));
                syncApplyButton();
            });

            $resultsBody.on('change', '.js-page-match-check', function () {
                var total = $resultsBody.find('.js-page-match-check').length;
                var checked = $resultsBody.find('.js-page-match-check:checked').length;
                $selectAll.prop('checked', total > 0 && total === checked);
                syncApplyButton();
            });

            $('#js-page-search-replace-preview').on('click', function () {
                var payload = getFormPayload();

                if (!validateForm(payload)) {
                    return;
                }

                $('.loading-overlay').css('display', 'flex');

                $.post(previewUrl, payload)
                    .done(function (response) {
                        if (response.success) {
                            renderResults(response.data);
                        }
                    })
                    .fail(function (xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : @json(trans('public.request_failed'));

                        $.toast({
                            heading: @json(trans('public.request_failed')),
                            text: message,
                            bgColor: '#f63c3c',
                            textColor: 'white',
                            hideAfter: 5000,
                            position: 'bottom-right',
                            icon: 'error'
                        });
                    })
                    .always(function () {
                        $('.loading-overlay').hide();
                    });
            });

            $applyBtn.on('click', function () {
                var payload = getFormPayload();
                var selections = getSelectedMatches();

                if (!validateForm(payload)) {
                    return;
                }

                if (!selections.length) {
                    $.toast({
                        heading: @json(trans('public.request_failed')),
                        text: selectRequiredText,
                        bgColor: '#f63c3c',
                        textColor: 'white',
                        hideAfter: 5000,
                        position: 'bottom-right',
                        icon: 'error'
                    });
                    return;
                }

                Swal.fire({
                    title: applyTitle,
                    text: applyText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: applyConfirm,
                    cancelButtonText: cancelText,
                }).then(function (result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    payload.selections = selections;
                    $('.loading-overlay').css('display', 'flex');

                    $.post(applyUrl, payload)
                        .done(function (response) {
                            $.toast({
                                heading: @json(trans('public.success')),
                                text: response.message || @json(trans('public.success')),
                                bgColor: '#43d477',
                                textColor: 'white',
                                hideAfter: 5000,
                                position: 'bottom-right',
                                icon: 'success'
                            });

                            $('#js-page-search-replace-preview').trigger('click');
                        })
                        .fail(function (xhr) {
                            var message = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : @json(trans('public.request_failed'));

                            $.toast({
                                heading: @json(trans('public.request_failed')),
                                text: message,
                                bgColor: '#f63c3c',
                                textColor: 'white',
                                hideAfter: 5000,
                                position: 'bottom-right',
                                icon: 'error'
                            });
                        })
                        .always(function () {
                            $('.loading-overlay').hide();
                        });
                });
            });
        })(jQuery);
    </script>
@endpush
