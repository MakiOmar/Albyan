@extends('admin.layouts.app')

@section('content')
    {{-- Full database backup & restore under Tools --}}
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ trans('admin/main.tools') }}</div>
                <div class="breadcrumb-item">{{ trans('admin/main.tools_database_backup') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            {{-- Hint: create on server, download, restore carefully --}}
                            <p class="text-muted mb-2">{{ trans('admin/main.database_backup_hint') }}</p>
                            <div class="alert alert-warning">
                                {{ trans('admin/main.database_backup_warning') }}
                            </div>
                            <p class="text-muted small mb-4">
                                {{ trans('admin/main.database_backup_path_label') }}:
                                <code>{{ $backupPath }}</code>
                                &middot;
                                {{ trans('admin/main.database_backup_retention_label', ['count' => $retention]) }}
                            </p>

                            @if(!empty($listError))
                                <div class="alert alert-danger">{{ $listError }}</div>
                            @endif

                            {{-- Create backup --}}
                            <form id="js-database-backup-create" method="post" action="{{ getAdminPanelUrl('/tools/database-backup') }}" class="mb-4">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-primary" id="js-database-backup-create-btn">
                                    <i class="fa fa-download mr-1"></i>
                                    {{ trans('admin/main.database_backup_create') }}
                                </button>
                            </form>

                            {{-- Backup list --}}
                            <div class="table-responsive">
                                <table class="table table-striped table-md">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('admin/main.database_backup_filename') }}</th>
                                        <th>{{ trans('admin/main.database_backup_size') }}</th>
                                        <th>{{ trans('admin/main.database_backup_modified') }}</th>
                                        <th class="text-right">{{ trans('admin/main.actions') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($backups as $backup)
                                        <tr>
                                            <td><code>{{ $backup['filename'] }}</code></td>
                                            <td>{{ $backup['size_human'] }}</td>
                                            <td>{{ $backup['modified_human'] }}</td>
                                            <td class="text-right text-nowrap">
                                                <a href="{{ getAdminPanelUrl('/tools/database-backup/' . $backup['filename'] . '/download') }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    {{ trans('admin/main.database_backup_download') }}
                                                </a>

                                                <button type="button"
                                                        class="btn btn-sm btn-outline-warning js-database-backup-restore"
                                                        data-filename="{{ $backup['filename'] }}"
                                                        data-action="{{ getAdminPanelUrl('/tools/database-backup/' . $backup['filename'] . '/restore') }}">
                                                    {{ trans('admin/main.database_backup_restore') }}
                                                </button>

                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger js-database-backup-delete"
                                                        data-filename="{{ $backup['filename'] }}"
                                                        data-action="{{ getAdminPanelUrl('/tools/database-backup/' . $backup['filename'] . '/delete') }}">
                                                    {{ trans('admin/main.delete') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                {{ trans('admin/main.database_backup_empty') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Hidden forms submitted after SweetAlert confirmation --}}
    <form id="js-database-backup-delete-form" method="post" class="d-none">
        {{ csrf_field() }}
    </form>
    <form id="js-database-backup-restore-form" method="post" class="d-none">
        {{ csrf_field() }}
        <input type="hidden" name="confirm_phrase" id="js-database-backup-confirm-phrase" value="">
    </form>
@endsection

@push('scripts_bottom')
    <script>
        (function () {
            const createForm = document.getElementById('js-database-backup-create');
            const createBtn = document.getElementById('js-database-backup-create-btn');
            const confirmPhrase = @json($confirmPhrase);

            if (createForm && createBtn) {
                createForm.addEventListener('submit', function () {
                    createBtn.disabled = true;
                    createBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> {{ trans('admin/main.database_backup_creating') }}';
                });
            }

            document.querySelectorAll('.js-database-backup-delete').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const filename = btn.getAttribute('data-filename');
                    const action = btn.getAttribute('data-action');

                    Swal.fire({
                        title: @json(trans('admin/main.database_backup_delete_title')),
                        text: @json(trans('admin/main.database_backup_delete_warning')) + ' ' + filename,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: @json(trans('admin/main.delete')),
                        cancelButtonText: @json(trans('public.cancel')),
                        confirmButtonColor: '#fc544b',
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            return;
                        }
                        const form = document.getElementById('js-database-backup-delete-form');
                        form.action = action;
                        form.submit();
                    });
                });
            });

            document.querySelectorAll('.js-database-backup-restore').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const filename = btn.getAttribute('data-filename');
                    const action = btn.getAttribute('data-action');

                    Swal.fire({
                        title: @json(trans('admin/main.database_backup_restore_title')),
                        html: @json(trans('admin/main.database_backup_restore_warning')) +
                            '<br><br><strong>' + filename + '</strong>' +
                            '<br><br>' + @json(trans('admin/main.database_backup_confirm_label', ['phrase' => $confirmPhrase])),
                        icon: 'warning',
                        input: 'text',
                        inputPlaceholder: confirmPhrase,
                        showCancelButton: true,
                        confirmButtonText: @json(trans('admin/main.database_backup_restore')),
                        cancelButtonText: @json(trans('public.cancel')),
                        confirmButtonColor: '#ffa426',
                        preConfirm: function (value) {
                            if (value !== confirmPhrase) {
                                Swal.showValidationMessage(@json(trans('admin/main.database_backup_confirm_mismatch')));
                                return false;
                            }
                            return value;
                        }
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            return;
                        }
                        const form = document.getElementById('js-database-backup-restore-form');
                        document.getElementById('js-database-backup-confirm-phrase').value = result.value;
                        form.action = action;
                        form.submit();
                    });
                });
            });
        })();
    </script>
@endpush
