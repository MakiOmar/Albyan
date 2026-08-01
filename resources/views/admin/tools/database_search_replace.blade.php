@extends('admin.layouts.app')

@section('content')
    {{-- Full-database search & replace under Tools --}}
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ trans('admin/main.tools') }}</div>
                <div class="breadcrumb-item">{{ trans('admin/main.tools_database_search_replace') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            {{-- Hint: preview first, then select rows to apply --}}
                            <p class="text-muted">{{ trans('admin/main.db_search_replace_hint') }}</p>
                            <div class="alert alert-warning">
                                {{ trans('admin/main.db_search_replace_warning') }}
                            </div>

                            <form id="js-db-search-replace-form" onsubmit="return false;">
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

                                <div class="form-group d-flex flex-wrap">
                                    <div class="custom-control custom-checkbox mr-4 mb-2">
                                        <input type="checkbox" name="case_sensitive" value="1" class="custom-control-input" id="db-case-sensitive" checked />
                                        <label class="custom-control-label" for="db-case-sensitive">{{ trans('admin/main.page_search_replace_case_sensitive') }}</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" name="whole_word" value="1" class="custom-control-input" id="db-whole-word" />
                                        <label class="custom-control-label" for="db-whole-word">{{ trans('admin/main.page_search_replace_whole_word') }}</label>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap">
                                    <button type="button" class="btn btn-primary mb-2" id="js-db-search-replace-preview">
                                        {{ trans('admin/main.page_search_replace_preview') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card d-none" id="js-db-search-replace-results-card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                            <h4 class="mb-2 mb-md-0">{{ trans('admin/main.page_search_replace_results') }}</h4>
                            <button type="button" class="btn btn-danger btn-sm mb-2 mb-md-0" id="js-db-search-replace-apply" disabled>
                                {{ trans('admin/main.page_search_replace_apply_selected') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="font-weight-bold mb-1" id="js-db-search-replace-summary"></p>
                            <p class="text-muted text-small mb-3 d-none" id="js-db-search-replace-truncated"></p>
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 40px;">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="js-db-select-all" />
                                                    <label class="custom-control-label" for="js-db-select-all"></label>
                                                </div>
                                            </th>
                                            <th>{{ trans('admin/main.db_search_replace_table') }}</th>
                                            <th>{{ trans('admin/main.db_search_replace_column') }}</th>
                                            <th>{{ trans('admin/main.db_search_replace_record_id') }}</th>
                                            <th class="text-center">{{ trans('admin/main.page_search_replace_occurrences') }}</th>
                                            <th>{{ trans('admin/main.page_search_replace_snippet') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="js-db-search-replace-results-body"></tbody>
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

            var $form = $('#js-db-search-replace-form');
            var $resultsCard = $('#js-db-search-replace-results-card');
            var $resultsBody = $('#js-db-search-replace-results-body');
            var $summary = $('#js-db-search-replace-summary');
            var $truncated = $('#js-db-search-replace-truncated');
            var $applyBtn = $('#js-db-search-replace-apply');
            var $selectAll = $('#js-db-select-all');
            var previewUrl = '{{ getAdminPanelUrl() }}/tools/database-search-replace/preview';
            var applyUrl = '{{ getAdminPanelUrl() }}/tools/database-search-replace/apply';
            var summaryTemplate = @json(trans('admin/main.db_search_replace_summary'));
            var truncatedTemplate = @json(trans('admin/main.db_search_replace_truncated'));
            var noMatchesText = @json(trans('admin/main.page_search_replace_no_matches'));
            var applyTitle = @json(trans('admin/main.db_search_replace_apply_title'));
            var applyText = @json(trans('admin/main.db_search_replace_apply_warning'));
            var applyConfirm = @json(trans('admin/main.page_search_replace_apply_confirm'));
            var cancelText = @json(trans('public.cancel'));
            var selectRequiredText = @json(trans('admin/main.page_search_replace_select_required'));

            function getFormPayload() {
                return {
                    search: $form.find('[name="search"]').val(),
                    replace: $form.find('[name="replace"]').val(),
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

                return true;
            }

            function getSelectedMatches() {
                var selections = [];

                $resultsBody.find('.js-db-match-check:checked').each(function () {
                    selections.push({
                        table: $(this).data('table'),
                        column: $(this).data('column'),
                        primary_key: $(this).data('primary-key'),
                    });
                });

                return selections;
            }

            function syncApplyButton() {
                $applyBtn.prop('disabled', getSelectedMatches().length === 0);
            }

            function resetPreviewState() {
                $resultsCard.addClass('d-none');
                $truncated.addClass('d-none').text('');
                $resultsBody.empty();
                $selectAll.prop('checked', false);
                $applyBtn.prop('disabled', true);
            }

            function renderResults(data) {
                $resultsBody.empty();
                $truncated.addClass('d-none').text('');
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

                if (data.truncated) {
                    $truncated
                        .text(truncatedTemplate.replace(':limit', data.preview_limit || 200))
                        .removeClass('d-none');
                }

                data.matches.forEach(function (match, index) {
                    var recordId = match.primary_key === null || match.primary_key === undefined
                        ? ''
                        : match.primary_key;
                    var checkboxId = 'db-match-' + index;

                    // Skip rows without a primary key — they cannot be applied safely.
                    if (recordId === '') {
                        return;
                    }

                    $resultsBody.append(
                        '<tr>' +
                        '<td class="text-center">' +
                        '<div class="custom-control custom-checkbox">' +
                        '<input type="checkbox" class="custom-control-input js-db-match-check" id="' + checkboxId + '" ' +
                        'data-table="' + $('<div>').text(match.table).html() + '" ' +
                        'data-column="' + $('<div>').text(match.column).html() + '" ' +
                        'data-primary-key="' + $('<div>').text(String(recordId)).html() + '" />' +
                        '<label class="custom-control-label" for="' + checkboxId + '"></label>' +
                        '</div>' +
                        '</td>' +
                        '<td>' + $('<div>').text(match.table).html() + '</td>' +
                        '<td>' + $('<div>').text(match.column).html() + '</td>' +
                        '<td>' + $('<div>').text(String(recordId)).html() + '</td>' +
                        '<td class="text-center">' + match.occurrences + '</td>' +
                        '<td><code class="font-12">' + $('<div>').text(match.snippet).html() + '</code></td>' +
                        '</tr>'
                    );
                });

                $resultsCard.removeClass('d-none');
            }

            $form.find('[name="search"], [name="replace"], [name="case_sensitive"], [name="whole_word"]').on('change input', function () {
                resetPreviewState();
            });

            $selectAll.on('change', function () {
                $resultsBody.find('.js-db-match-check').prop('checked', $(this).is(':checked'));
                syncApplyButton();
            });

            $resultsBody.on('change', '.js-db-match-check', function () {
                var total = $resultsBody.find('.js-db-match-check').length;
                var checked = $resultsBody.find('.js-db-match-check:checked').length;
                $selectAll.prop('checked', total > 0 && total === checked);
                syncApplyButton();
            });

            $('#js-db-search-replace-preview').on('click', function () {
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

                            // Refresh preview so applied rows disappear from results.
                            $('#js-db-search-replace-preview').trigger('click');
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
