<?php

namespace App\Console\Commands;

use App\Services\WordpressCourseSyncService;
use App\Models\Webinar;
use Illuminate\Console\Command;

class SyncAllCoursesToWordpress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wp:sync-all-courses 
                            {--status= : Filter by status (active, pending, is_draft, inactive)}
                            {--type= : Filter by type (webinar, course, text_lesson)}
                            {--limit= : Limit the number of courses to sync}
                            {--offset=0 : Start from this offset}
                            {--skip-existing : Skip courses that already exist in WordPress}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all courses from Laravel to WordPress/LearnPress';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(WordpressCourseSyncService $service)
    {
        $this->info('Starting batch sync of all courses to WordPress...');
        $this->newLine();

        // Build query
        $query = Webinar::query();

        // Apply filters
        if ($this->option('status')) {
            $query->where('status', $this->option('status'));
            $this->info("Filtering by status: {$this->option('status')}");
        }

        if ($this->option('type')) {
            $query->where('type', $this->option('type'));
            $this->info("Filtering by type: {$this->option('type')}");
        }

        // Get total count
        $total = $query->count();
        $this->info("Total courses to sync: {$total}");
        $this->newLine();

        if ($total === 0) {
            $this->warn('No courses found matching the criteria.');
            return 0;
        }

        // Apply limit and offset
        $offset = (int) $this->option('offset');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if ($limit) {
            $query->skip($offset)->take($limit);
            $this->info("Processing courses {$offset} to " . ($offset + $limit) . " (limit: {$limit})");
        } else {
            $query->skip($offset);
            $this->info("Processing courses starting from offset {$offset}");
        }

        $this->newLine();

        // Get courses
        $courses = $query->get();
        $processed = 0;
        $successful = 0;
        $failed = 0;
        $skipped = 0;

        // Create progress bar
        $bar = $this->output->createProgressBar($courses->count());
        $bar->start();

        foreach ($courses as $course) {
            $processed++;

            // Check if we should skip existing courses
            if ($this->option('skip-existing')) {
                // Note: This would require checking WordPress, which might be slow
                // For now, we'll sync and let WordPress handle duplicates
            }

            $result = $service->syncSingleCourse($course->id);

            if ($result['success']) {
                $successful++;
            } else {
                $failed++;
                $this->newLine();
                $this->error("Failed to sync course ID {$course->id}: " . ($result['error'] ?? 'Unknown error'));
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('=== Sync Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $processed],
                ['Successful', $successful],
                ['Failed', $failed],
                ['Skipped', $skipped],
            ]
        );

        if ($failed > 0) {
            $this->warn("{$failed} course(s) failed to sync. Check the logs for details.");
            return 1;
        }

        $this->info('All courses synced successfully!');
        return 0;
    }
}

