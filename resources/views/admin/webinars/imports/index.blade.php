@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/webinars">{{ trans('admin/main.courses') }}</a></div>
                <div class="breadcrumb-item">{{ trans('admin/main.import') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ trans('admin/main.import') }}</h4>
                    <div class="d-flex align-items-center">
                        <a href="{{ getAdminPanelUrl() }}/webinars/imports/template/download" class="btn btn-outline-primary mr-10">{{ trans('admin/main.download') }} {{ trans('admin/main.template') }}</a>
                        <a href="{{ getAdminPanelUrl() }}/webinars?type=course" class="btn btn-primary">{{ trans('admin/main.back') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ getAdminPanelUrl() }}/webinars/imports" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="input-label">{{ trans('admin/main.file') }}</label>
                            <input type="file" name="file" class="form-control">
                            @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <p class="text-muted font-12">{{ trans('admin/main.import') }} uses queue workers and updates records by ID then slug.</p>
                        <button type="submit" class="btn btn-success">{{ trans('admin/main.submit') }}</button>
                    </form>
                </div>
            </div>

            <div class="card mt-20">
                <div class="card-header">
                    <h4 class="mb-0">{{ trans('admin/main.list') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ getAdminPanelUrl() }}/webinars/imports/bulk-delete" method="post" onsubmit="return confirm('Are you sure you want to delete selected import records?');">
                        {{ csrf_field() }}
                        <div class="mb-15 text-right">
                            <button type="submit" class="btn btn-danger btn-sm">Delete Selected</button>
                        </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="select-all-imports">
                                </th>
                                <th>{{ trans('admin/main.id') }}</th>
                                <th>{{ trans('admin/main.file') }}</th>
                                <th>{{ trans('admin/main.user') }}</th>
                                <th>{{ trans('admin/main.status') }}</th>
                                <th>{{ trans('admin/main.created') }}</th>
                                <th>{{ trans('admin/main.updated') }}</th>
                                <th>{{ trans('admin/main.failed') }}</th>
                                <th>{{ trans('admin/main.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($imports as $import)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="import-item-checkbox" name="ids[]" value="{{ $import->id }}">
                                    </td>
                                    <td>{{ $import->id }}</td>
                                    <td>{{ $import->file_name }}</td>
                                    <td>{{ !empty($import->user) ? $import->user->full_name : '-' }}</td>
                                    <td>{{ $import->status }}</td>
                                    <td>{{ $import->created_count }}</td>
                                    <td>{{ $import->updated_count }}</td>
                                    <td>{{ $import->failed_count }}</td>
                                    <td>
                                        <a href="{{ getAdminPanelUrl() }}/webinars/imports/{{ $import->id }}" class="btn btn-sm btn-primary">{{ trans('admin/main.show') }}</a>
                                        <form action="{{ getAdminPanelUrl() }}/webinars/imports/{{ $import->id }}/rerun" method="post" class="d-inline">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-sm btn-warning">Rerun</button>
                                        </form>
                                        <form action="{{ getAdminPanelUrl() }}/webinars/imports/{{ $import->id }}/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this import record?');">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No data found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    </form>
                </div>

                <div class="card-footer text-center">
                    {{ $imports->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        (function () {
            const selectAll = document.getElementById('select-all-imports');
            if (!selectAll) return;

            selectAll.addEventListener('change', function () {
                const items = document.querySelectorAll('.import-item-checkbox');
                items.forEach(function (item) {
                    item.checked = selectAll.checked;
                });
            });
        })();
    </script>
@endpush
