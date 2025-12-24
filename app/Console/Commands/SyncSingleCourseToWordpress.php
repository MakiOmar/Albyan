<?php

namespace App\Console\Commands;

use App\Services\WordpressCourseSyncService;
use Illuminate\Console\Command;

class SyncSingleCourseToWordpress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wp:sync-course {webinar_id : ID of the course (webinar) to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a single course from Laravel (webinars table) to WordPress/LearnPress for testing';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(WordpressCourseSyncService $service)
    {
        $webinarId = (int) $this->argument('webinar_id');

        $this->info("Syncing course ID {$webinarId} to WordPress...");

        $result = $service->syncSingleCourse($webinarId);

        // Debug: Show payload if validation fails
        if (!$result['success'] && strpos($result['error'] ?? '', 'Payload validation failed') !== false) {
            $this->warn('Payload validation failed. Check logs for details.');
        }

        if (!$result['success']) {
            $this->error('Sync failed: ' . ($result['error'] ?? 'Unknown error'));
            if (isset($result['status'])) {
                $this->error('HTTP status: ' . $result['status']);
            }
            if (isset($result['body'])) {
                $this->line('Response body: ' . json_encode($result['body']));
            }

            return 1;
        }

        $this->info('Course synced successfully.');
        if (isset($result['body'])) {
            $this->line('Response: ' . json_encode($result['body']));
        }

        return 0;
    }
}


