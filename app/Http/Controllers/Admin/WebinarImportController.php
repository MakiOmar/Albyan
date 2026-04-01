<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WebinarsImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\WebinarsImport;
use App\Models\CourseImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WebinarImportController extends Controller
{
    public function index()
    {
        $this->authorize('admin_webinars_create');

        $imports = CourseImport::query()
            ->with('user')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $data = [
            'pageTitle' => 'Course Imports',
            'imports' => $imports,
        ];

        return view('admin.webinars.imports.index', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_webinars_create');

        $this->validate($request, [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        if (config('queue.default') === 'sync') {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => 'Queue driver is sync. Please set QUEUE_CONNECTION=database or redis and run queue worker.',
                'status' => 'error',
            ];

            return back()->with(['toast' => $toastData]);
        }

        $file = $request->file('file');
        $extension = (string)$file->getClientOriginalExtension();
        $storedPath = $file->storeAs('imports/courses', uniqid('course-import-') . '.' . $extension, 'local');

        if (empty($storedPath) || !Storage::disk('local')->exists($storedPath)) {
            Log::error('Course import file could not be stored.', [
                'original_name' => $file->getClientOriginalName(),
                'extension' => $extension,
                'disk' => 'local',
            ]);

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => 'Import file could not be saved to storage. Please check storage permissions.',
                'status' => 'error',
            ];

            return back()->with(['toast' => $toastData]);
        }

        $absolutePath = Storage::disk('local')->path($storedPath);
        $totalRows = $this->detectTotalRows($absolutePath);

        $courseImport = CourseImport::query()->create([
            'user_id' => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'total_rows' => $totalRows,
            'status' => CourseImport::$processing,
            'started_at' => time(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        try {
            $this->dispatchImport($courseImport, $extension);
        } catch (\Throwable $e) {
            $this->markImportAsFailed($courseImport, $e, 'queue_import_failed_at_store');

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => 'Failed to queue the import process: ' . $e->getMessage(),
                'status' => 'error',
            ];

            return back()->with(['toast' => $toastData]);
        }

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => 'Course import has been queued successfully.',
            'status' => 'success',
        ];

        return redirect(getAdminPanelUrl() . '/webinars/imports/' . $courseImport->id)->with(['toast' => $toastData]);
    }

    public function show($id)
    {
        $this->authorize('admin_webinars_create');

        $courseImport = CourseImport::query()->with('user')->findOrFail($id);
        $errorLog = !empty($courseImport->error_log) ? json_decode($courseImport->error_log, true) : [];

        $data = [
            'pageTitle' => 'Course Import Details',
            'courseImport' => $courseImport,
            'errorLog' => $errorLog,
        ];

        return view('admin.webinars.imports.show', $data);
    }

    public function rerun($id)
    {
        $this->authorize('admin_webinars_create');

        $courseImport = CourseImport::query()->findOrFail($id);

        if (!Storage::disk('local')->exists($courseImport->file_path)) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => 'Import file no longer exists. Please upload the file again.',
                'status' => 'error',
            ];

            return back()->with(['toast' => $toastData]);
        }

        $courseImport->update([
            'status' => CourseImport::$processing,
            'processed_rows' => 0,
            'created_count' => 0,
            'updated_count' => 0,
            'failed_count' => 0,
            'error_log' => null,
            'started_at' => time(),
            'finished_at' => null,
            'updated_at' => time(),
        ]);

        try {
            $this->dispatchImport($courseImport, pathinfo($courseImport->file_name, PATHINFO_EXTENSION));
        } catch (\Throwable $e) {
            $this->markImportAsFailed($courseImport, $e, 'queue_import_failed_at_rerun');

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => 'Failed to rerun import: ' . $e->getMessage(),
                'status' => 'error',
            ];

            return back()->with(['toast' => $toastData]);
        }

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => 'Import has been requeued successfully.',
            'status' => 'success',
        ];

        return redirect(getAdminPanelUrl() . '/webinars/imports/' . $courseImport->id)->with(['toast' => $toastData]);
    }

    public function delete($id)
    {
        $this->authorize('admin_webinars_create');

        $courseImport = CourseImport::query()->findOrFail($id);
        $this->deleteImportRecordAndFile($courseImport);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => 'Import record deleted successfully.',
            'status' => 'success',
        ];

        return back()->with(['toast' => $toastData]);
    }

    public function bulkDelete(Request $request)
    {
        $this->authorize('admin_webinars_create');

        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => 'No import records selected.',
                'status' => 'error',
            ];

            return back()->with(['toast' => $toastData]);
        }

        $imports = CourseImport::query()->whereIn('id', $ids)->get();
        foreach ($imports as $courseImport) {
            $this->deleteImportRecordAndFile($courseImport);
        }

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => 'Selected import records deleted successfully.',
            'status' => 'success',
        ];

        return back()->with(['toast' => $toastData]);
    }

    public function status($id)
    {
        $this->authorize('admin_webinars_create');

        $courseImport = CourseImport::query()->findOrFail($id);
        $percentage = 0;

        if (!empty($courseImport->total_rows)) {
            $percentage = min(100, (int)round(($courseImport->processed_rows / $courseImport->total_rows) * 100));
        } elseif (in_array($courseImport->status, [CourseImport::$completed, CourseImport::$failed])) {
            $percentage = 100;
        }

        return response()->json([
            'id' => $courseImport->id,
            'status' => $courseImport->status,
            'total_rows' => (int)$courseImport->total_rows,
            'processed_rows' => (int)$courseImport->processed_rows,
            'created_count' => (int)$courseImport->created_count,
            'updated_count' => (int)$courseImport->updated_count,
            'failed_count' => (int)$courseImport->failed_count,
            'started_at' => $courseImport->started_at,
            'finished_at' => $courseImport->finished_at,
            'started_at_label' => !empty($courseImport->started_at) ? dateTimeFormat($courseImport->started_at, 'Y M j | H:i') : '-',
            'finished_at_label' => !empty($courseImport->finished_at) ? dateTimeFormat($courseImport->finished_at, 'Y M j | H:i') : '-',
            'errors' => !empty($courseImport->error_log) ? json_decode($courseImport->error_log, true) : [],
            'percentage' => $percentage,
        ], 200);
    }

    public function template()
    {
        $this->authorize('admin_webinars_create');

        return Excel::download(new WebinarsImportTemplateExport(), 'webinars_import_template.xlsx');
    }

    private function detectTotalRows(string $absolutePath): int
    {
        try {
            $reader = IOFactory::createReaderForFile($absolutePath);
            $worksheetsInfo = $reader->listWorksheetInfo($absolutePath);

            if (empty($worksheetsInfo[0]['totalRows'])) {
                return 0;
            }

            return max(0, ((int)$worksheetsInfo[0]['totalRows']) - 1);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getReaderTypeFromExtension(string $extension): ?string
    {
        $extension = mb_strtolower(trim($extension));

        return match ($extension) {
            'xlsx' => ExcelFormat::XLSX,
            'xls' => ExcelFormat::XLS,
            'csv' => ExcelFormat::CSV,
            default => null,
        };
    }

    private function dispatchImport(CourseImport $courseImport, string $extension): void
    {
        $readerType = $this->getReaderTypeFromExtension($extension);

        if (empty($readerType)) {
            throw new \InvalidArgumentException("Unsupported import file type: {$extension}");
        }

        if (empty($courseImport->file_path) || !Storage::disk('local')->exists($courseImport->file_path)) {
            throw new \RuntimeException('Import file is missing from local storage.');
        }

        $absolutePath = Storage::disk('local')->path($courseImport->file_path);
        if (!is_readable($absolutePath)) {
            throw new \RuntimeException("Import file is not readable: {$absolutePath}");
        }

        Excel::queueImport(new WebinarsImport($courseImport->id), $absolutePath, null, $readerType);
    }

    private function markImportAsFailed(CourseImport $courseImport, \Throwable $e, string $tag): void
    {
        $errorPayload = [[
            'row' => null,
            'error' => $e->getMessage(),
            'tag' => $tag,
        ]];

        Log::error('Course import queue failed.', [
            'tag' => $tag,
            'import_id' => $courseImport->id,
            'file_name' => $courseImport->file_name,
            'file_path' => $courseImport->file_path,
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        $courseImport->update([
            'status' => CourseImport::$failed,
            'finished_at' => time(),
            'updated_at' => time(),
            'error_log' => json_encode($errorPayload),
        ]);
    }

    private function deleteImportRecordAndFile(CourseImport $courseImport): void
    {
        if (Storage::disk('local')->exists($courseImport->file_path)) {
            Storage::disk('local')->delete($courseImport->file_path);
        }

        $courseImport->delete();
    }
}
