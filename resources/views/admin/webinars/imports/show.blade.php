@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/webinars/imports">{{ trans('admin/main.import') }}</a></div>
                <div class="breadcrumb-item">{{ trans('admin/main.details') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ trans('admin/main.details') }}</h4>
                    <div>
                        <form action="{{ getAdminPanelUrl() }}/webinars/imports/{{ $courseImport->id }}/rerun" method="post" class="d-inline">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-warning">Rerun</button>
                        </form>
                        <a href="{{ getAdminPanelUrl() }}/webinars?type=course" class="btn btn-primary">{{ trans('admin/main.back') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.id') }}:</strong> {{ $courseImport->id }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.status') }}:</strong> <span id="import-status">{{ $courseImport->status }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.user') }}:</strong> {{ !empty($courseImport->user) ? $courseImport->user->full_name : '-' }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.file') }}:</strong> {{ $courseImport->file_name }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.total') }}:</strong> <span id="import-total-rows">{{ $courseImport->total_rows }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.process') }}:</strong> <span id="import-processed-rows">{{ $courseImport->processed_rows }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.created') }}:</strong> <span id="import-created-count">{{ $courseImport->created_count }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.updated') }}:</strong> <span id="import-updated-count">{{ $courseImport->updated_count }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.failed') }}:</strong> <span id="import-failed-count">{{ $courseImport->failed_count }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.start_date') }}:</strong> <span id="import-started-at">{{ !empty($courseImport->started_at) ? dateTimeFormat($courseImport->started_at, 'Y M j | H:i') : '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.end_date') }}:</strong> <span id="import-finished-at">{{ !empty($courseImport->finished_at) ? dateTimeFormat($courseImport->finished_at, 'Y M j | H:i') : '-' }}</span>
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Progress:</strong>
                            <div class="progress mt-2" style="height: 18px;">
                                <div id="import-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;">0%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-20">
                <div class="card-header">
                    <h4 class="mb-0">{{ trans('admin/main.errors') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>{{ trans('admin/main.row') }}</th>
                                <th>{{ trans('admin/main.error') }}</th>
                            </tr>
                            </thead>
                            <tbody id="import-errors-body">
                            @forelse($errorLog as $error)
                                <tr>
                                    <td>{{ $error['row'] ?? '-' }}</td>
                                    <td>{{ $error['error'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">No errors recorded.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        (function () {
            const statusUrl = "{{ getAdminPanelUrl() }}/webinars/imports/{{ $courseImport->id }}/status";
            const statusEl = document.getElementById('import-status');
            const totalEl = document.getElementById('import-total-rows');
            const processedEl = document.getElementById('import-processed-rows');
            const createdEl = document.getElementById('import-created-count');
            const updatedEl = document.getElementById('import-updated-count');
            const failedEl = document.getElementById('import-failed-count');
            const progressBarEl = document.getElementById('import-progress-bar');
            const startedAtEl = document.getElementById('import-started-at');
            const finishedAtEl = document.getElementById('import-finished-at');
            const errorsBodyEl = document.getElementById('import-errors-body');

            let pollTimer = null;

            function setProgress(percentage, status) {
                const pct = Number.isFinite(percentage) ? percentage : 0;
                progressBarEl.style.width = pct + '%';
                progressBarEl.innerText = pct + '%';

                if (status === 'completed') {
                    progressBarEl.classList.remove('progress-bar-animated', 'bg-primary', 'bg-danger');
                    progressBarEl.classList.add('bg-success');
                } else if (status === 'failed') {
                    progressBarEl.classList.remove('progress-bar-animated', 'bg-primary', 'bg-success');
                    progressBarEl.classList.add('bg-danger');
                } else {
                    progressBarEl.classList.remove('bg-success', 'bg-danger');
                    progressBarEl.classList.add('bg-primary', 'progress-bar-animated');
                }
            }

            function stopPolling() {
                if (pollTimer) {
                    clearInterval(pollTimer);
                }
            }

            function renderErrors(errors) {
                if (!Array.isArray(errors) || errors.length === 0) {
                    errorsBodyEl.innerHTML = '<tr><td colspan="2" class="text-center">No errors recorded.</td></tr>';
                    return;
                }

                errorsBodyEl.innerHTML = errors.map(error => {
                    const row = (error && error.row !== null && error.row !== undefined) ? error.row : '-';
                    const message = (error && error.error) ? error.error : '-';
                    return '<tr><td>' + row + '</td><td>' + message + '</td></tr>';
                }).join('');
            }

            function updateStatus() {
                fetch(statusUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        statusEl.innerText = data.status;
                        totalEl.innerText = data.total_rows;
                        processedEl.innerText = data.processed_rows;
                        createdEl.innerText = data.created_count;
                        updatedEl.innerText = data.updated_count;
                        failedEl.innerText = data.failed_count;
                        startedAtEl.innerText = data.started_at_label || '-';
                        finishedAtEl.innerText = data.finished_at_label || '-';
                        setProgress(data.percentage, data.status);
                        renderErrors(data.errors);

                        if (data.status === 'completed' || data.status === 'failed') {
                            stopPolling();
                        }
                    })
                    .catch(() => {
                        // Keep polling even if a request fails temporarily.
                    });
            }

            // Initial fetch + polling every 1 second for smoother live updates.
            updateStatus();
            pollTimer = setInterval(updateStatus, 1000);
        })();
    </script>
@endpush
