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
                            <p class="text-muted">{{ trans('admin/main.db_search_replace_hint') }}</p>
                            <div class="alert alert-warning">
                                {{ trans('admin/main.db_search_replace_warning') }}
                            </div>

                            <form id="js-db-search-replace-form" action="{{ getAdminPanelUrl() }}/tools/database-search-replace/apply" method="post">
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
                                    <button type="button" class="btn btn-outline-primary mr-2 mb-2" id="js-db-search-replace-preview">
                                        {{ trans('admin/main.page_search_replace_preview') }}
                                    </button>
                                    <button type="button" class="btn btn-danger mb-2" id="js-db-search-replace-apply" disabled>
                                        {{ trans('admin/main.page_search_replace_apply') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card d-none" id="js-db-search-replace-results-card">
                        <div class="card-header">
                            <h4 class="mb-0">{{ trans('admin/main.page_search_replace_results') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="font-weight-bold mb-1" id="js-db-search-replace-summary"></p>
                            <p class="text-muted text-small mb-3 d-none" id="js-db-search-replace-truncated"></p>
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <thead>
                                        <tr>
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
            var previewUrl = '{{ getAdminPanelUrl() }}/tools/database-search-replace/preview';
            var previewDone = false;
            var summaryTemplate = @json(trans('admin/main.db_search_replace_summary'));
            var truncatedTemplate = @json(trans('admin/main.db_search_replace_truncated'));
            var noMatchesText = @json(trans('admin/main.page_search_replace_no_matches'));
            var applyTitle = @json(trans('admin/main.db_search_replace_apply_title'));
            var applyText = @json(trans('admin/main.db_search_replace_apply_warning'));
            var applyConfirm = @json(trans('admin/main.page_search_replace_apply_confirm'));
            var cancelText = @json(trans('public.cancel'));
            var previewRequiredText = @json(trans('admin/main.db_search_replace_preview_required'));

            function getFormPayload() {
                return {
                    search: $form.find('[name="search"]').val(),
                    replace: $form.find('[name="replace"]').val(),
                    case_sensitive: $form.find('[name="case_sensitive"]').is(':checked') ? 1 : 0,
                    whole_word: $form.find('[name="whole_word"]').is(':checked') ? 1 : 0,
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

            function resetPreviewState() {
                previewDone = false;
                $applyBtn.prop('disabled', true);
                $resultsCard.addClass('d-none');
                $truncated.addClass('d-none').text('');
            }

            function renderResults(data) {
                $resultsBody.empty();
                $truncated.addClass('d-none').text('');

                if (!data.matches || !data.matches.length) {
                    $summary.text(noMatchesText);
                    $resultsCard.removeClass('d-none');
                    previewDone = true;
                    $applyBtn.prop('disabled', false);
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

                data.matches.forEach(function (match) {
                    var recordId = match.primary_key === null || match.primary_key === undefined
                        ? '—'
                        : match.primary_key;

                    $resultsBody.append(
                        '<tr>' +
                        '<td>' + $('<div>').text(match.table).html() + '</td>' +
                        '<td>' + $('<div>').text(match.column).html() + '</td>' +
                        '<td>' + $('<div>').text(String(recordId)).html() + '</td>' +
                        '<td class="text-center">' + match.occurrences + '</td>' +
                        '<td><code class="font-12">' + $('<div>').text(match.snippet).html() + '</code></td>' +
                        '</tr>'
                    );
                });

                $resultsCard.removeClass('d-none');
                previewDone = true;
                $applyBtn.prop('disabled', false);
            }

            $form.find('[name="search"], [name="replace"], [name="case_sensitive"], [name="whole_word"]').on('change input', function () {
                resetPreviewState();
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

                if (!validateForm(payload)) {
                    return;
                }

                if (!previewDone) {
                    $.toast({
                        heading: @json(trans('public.request_failed')),
                        text: previewRequiredText,
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
                    if (result.isConfirmed) {
                        $form.trigger('submit');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
