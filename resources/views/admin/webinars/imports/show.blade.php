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
                    <a href="{{ getAdminPanelUrl() }}/webinars?type=course" class="btn btn-primary">{{ trans('admin/main.back') }}</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.id') }}:</strong> {{ $courseImport->id }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.status') }}:</strong> {{ $courseImport->status }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.user') }}:</strong> {{ !empty($courseImport->user) ? $courseImport->user->full_name : '-' }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.file') }}:</strong> {{ $courseImport->file_name }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.total') }}:</strong> {{ $courseImport->total_rows }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.process') }}:</strong> {{ $courseImport->processed_rows }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.created') }}:</strong> {{ $courseImport->created_count }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.updated') }}:</strong> {{ $courseImport->updated_count }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.failed') }}:</strong> {{ $courseImport->failed_count }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.start_date') }}:</strong> {{ !empty($courseImport->started_at) ? dateTimeFormat($courseImport->started_at, 'Y M j | H:i') : '-' }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ trans('admin/main.end_date') }}:</strong> {{ !empty($courseImport->finished_at) ? dateTimeFormat($courseImport->finished_at, 'Y M j | H:i') : '-' }}
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
                            <tbody>
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
