<?php

namespace App\Imports;

use App\Models\CourseImport;
use App\Services\CourseImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class WebinarsImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue, WithEvents
{
    private int $courseImportId;
    private array $errors = [];

    public function __construct(int $courseImportId)
    {
        $this->courseImportId = $courseImportId;
    }

    public function collection($rows): void
    {
        $courseImport = CourseImport::query()->find($this->courseImportId);
        if (empty($courseImport)) {
            return;
        }

        $service = app(CourseImportService::class);
        $created = 0;
        $updated = 0;
        $failed = 0;
        $processed = 0;

        foreach ($rows as $index => $row) {
            $processed++;

            $result = $service->processRow($row->toArray(), $courseImport);
            if ($result['result'] === 'created') {
                $created++;
                continue;
            }

            if ($result['result'] === 'updated') {
                $updated++;
                continue;
            }

            $failed++;
            $this->errors[] = [
                'row' => (($this->headingRow() + 1) + $index),
                'error' => $result['error'] ?? 'Unknown row error.',
            ];
        }

        $this->updateCounters($courseImport->id, $processed, $created, $updated, $failed);
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                $courseImport = CourseImport::query()->find($this->courseImportId);
                if (empty($courseImport)) {
                    return;
                }

                $data = [
                    'status' => CourseImport::$completed,
                    'finished_at' => time(),
                    'updated_at' => time(),
                ];

                if (!empty($this->errors)) {
                    $data['error_log'] = json_encode($this->errors);
                }

                $courseImport->update($data);
            },
            ImportFailed::class => function (ImportFailed $event) {
                $courseImport = CourseImport::query()->find($this->courseImportId);
                if (empty($courseImport)) {
                    return;
                }

                $failedError = [
                    'row' => null,
                    'error' => $event->getException()->getMessage(),
                ];

                $errorLog = !empty($courseImport->error_log) ? json_decode($courseImport->error_log, true) : [];
                $errorLog[] = $failedError;

                $courseImport->update([
                    'status' => CourseImport::$failed,
                    'finished_at' => time(),
                    'updated_at' => time(),
                    'error_log' => json_encode($errorLog),
                ]);
            }
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function headingRow(): int
    {
        return 1;
    }

    private function updateCounters(int $courseImportId, int $processed, int $created, int $updated, int $failed): void
    {
        DB::table('course_imports')
            ->where('id', $courseImportId)
            ->update([
                'processed_rows' => DB::raw("processed_rows + {$processed}"),
                'created_count' => DB::raw("created_count + {$created}"),
                'updated_count' => DB::raw("updated_count + {$updated}"),
                'failed_count' => DB::raw("failed_count + {$failed}"),
                'updated_at' => time(),
            ]);
    }
}
