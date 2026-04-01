<?php

namespace App\Jobs;

use App\Imports\WebinarsImport;
use App\Models\CourseImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ProcessCourseImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $courseImportId;

    public function __construct(int $courseImportId)
    {
        $this->courseImportId = $courseImportId;
    }

    public function handle(): void
    {
        $courseImport = CourseImport::query()->find($this->courseImportId);
        if (empty($courseImport)) {
            return;
        }

        $courseImport->update([
            'status' => CourseImport::$processing,
            'started_at' => time(),
            'updated_at' => time(),
        ]);

        Excel::import(new WebinarsImport($courseImport->id), $courseImport->file_path, 'local');
    }

    public function failed(\Throwable $exception): void
    {
        $courseImport = CourseImport::query()->find($this->courseImportId);
        if (empty($courseImport)) {
            return;
        }

        $errorLog = !empty($courseImport->error_log) ? json_decode($courseImport->error_log, true) : [];
        $errorLog[] = [
            'row' => null,
            'error' => $exception->getMessage(),
        ];

        $courseImport->update([
            'status' => CourseImport::$failed,
            'finished_at' => time(),
            'updated_at' => time(),
            'error_log' => json_encode($errorLog),
        ]);
    }
}
