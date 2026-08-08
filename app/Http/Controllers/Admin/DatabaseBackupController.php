<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    public function index(DatabaseBackupService $backupService)
    {
        $this->authorize('admin_settings');

        try {
            $backups = $backupService->listBackups();
            $listError = null;
        } catch (RuntimeException $e) {
            $backups = [];
            $listError = $e->getMessage();
        }

        $data = [
            'pageTitle' => trans('admin/main.tools_database_backup'),
            'backups' => $backups,
            'listError' => $listError,
            'confirmPhrase' => $backupService->confirmPhrase(),
            'retention' => (int) config('database_backup.retention', 10),
            'backupPath' => $backupService->backupDirectory(),
        ];

        return view('admin.tools.database_backup', $data);
    }

    public function store(DatabaseBackupService $backupService)
    {
        $this->authorize('admin_settings');

        try {
            $result = $backupService->createBackup();

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('admin/main.database_backup_created', [
                    'file' => $result['filename'],
                    'size' => $result['size_human'],
                ]),
                'status' => 'success',
            ];
        } catch (RuntimeException $e) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => $e->getMessage(),
                'status' => 'error',
            ];
        }

        return redirect(getAdminPanelUrl('/tools/database-backup'))->with(['toast' => $toastData]);
    }

    public function download(string $file, DatabaseBackupService $backupService): BinaryFileResponse
    {
        $this->authorize('admin_settings');

        $path = $backupService->resolveBackupPath($file);

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function destroy(string $file, DatabaseBackupService $backupService)
    {
        $this->authorize('admin_settings');

        try {
            $backupService->deleteBackup($file);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('admin/main.database_backup_deleted', ['file' => $file]),
                'status' => 'success',
            ];
        } catch (RuntimeException $e) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => $e->getMessage(),
                'status' => 'error',
            ];
        }

        return redirect(getAdminPanelUrl('/tools/database-backup'))->with(['toast' => $toastData]);
    }

    public function restore(Request $request, string $file, DatabaseBackupService $backupService)
    {
        $this->authorize('admin_settings');

        $data = $request->validate([
            'confirm_phrase' => 'required|string',
        ]);

        try {
            // Long-running import; avoid PHP killing the request mid-restore.
            @set_time_limit(0);
            @ini_set('max_execution_time', '0');
            @ini_set('memory_limit', '512M');

            $backupService->restoreBackup($file, $data['confirm_phrase']);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('admin/main.database_backup_restored', ['file' => $file]),
                'status' => 'success',
            ];
        } catch (RuntimeException $e) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => $e->getMessage(),
                'status' => 'error',
            ];
        }

        return redirect(getAdminPanelUrl('/tools/database-backup'))->with(['toast' => $toastData]);
    }
}
