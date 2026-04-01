<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WebinarsImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessCourseImportJob;
use App\Models\CourseImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
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
            'status' => CourseImport::$pending,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        ProcessCourseImportJob::dispatch($courseImport->id);

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
}
