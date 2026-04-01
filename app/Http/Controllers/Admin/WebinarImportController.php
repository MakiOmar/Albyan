<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WebinarsImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\WebinarsImport;
use App\Models\CourseImport;
use Illuminate\Http\Request;
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
        $storedPath = $file->store('imports/courses', 'local');
        $absolutePath = storage_path('app/' . $storedPath);
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
            $readerType = $this->getReaderTypeFromExtension((string)$file->getClientOriginalExtension());

            if (empty($readerType)) {
                throw new \InvalidArgumentException('Unsupported import file type.');
            }

            Excel::queueImport(new WebinarsImport($courseImport->id), $courseImport->file_path, 'local', $readerType);
        } catch (\Throwable $e) {
            $courseImport->update([
                'status' => CourseImport::$failed,
                'finished_at' => time(),
                'updated_at' => time(),
                'error_log' => json_encode([[
                    'row' => null,
                    'error' => $e->getMessage(),
                ]]),
            ]);

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => 'Failed to queue the import process.',
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
}
