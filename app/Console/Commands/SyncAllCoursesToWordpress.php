<?php

namespace App\Console\Commands;

use App\Services\WordpressCourseSyncService;
use App\Models\Webinar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
                            {--skip-existing : Skip courses that already exist in WordPress}
                            {--retries=3 : Number of retry attempts for failed requests (default: 3)}
                            {--retry-delay=2 : Delay in seconds between retries (default: 2)}
                            {--resume : Resume from last checkpoint (if process was killed)}
                            {--checkpoint-file= : Custom checkpoint file path (default: storage/app/wp-sync-checkpoint.json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all courses from Laravel to WordPress/LearnPress';

    /**
     * Checkpoint file path
     *
     * @var string
     */
    protected $checkpointFile;

    /**
     * Checkpoint data
     *
     * @var array
     */
    protected $checkpoint = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(WordpressCourseSyncService $service)
    {
        // Initialize checkpoint file
        $this->checkpointFile = $this->option('checkpoint-file') 
            ?: storage_path('app/wp-sync-checkpoint.json');

        // Check if checkpoint exists
        $checkpointExists = file_exists($this->checkpointFile);

        // Load checkpoint if resuming
        if ($this->option('resume')) {
            $this->loadCheckpoint();
            if (!empty($this->checkpoint)) {
                $this->info('Resuming from checkpoint...');
                $this->info("Last processed course ID: " . ($this->checkpoint['last_course_id'] ?? 'N/A'));
                $this->info("Successfully synced: " . count($this->checkpoint['successful_ids'] ?? []));
                $this->info("Failed: " . count($this->checkpoint['failed_ids'] ?? []));
                $this->newLine();
            } else {
                $this->warn('No checkpoint found. Starting fresh sync.');
                $this->newLine();
            }
        } else {
            // Start fresh - but warn if checkpoint exists
            if ($checkpointExists) {
                $this->warn('⚠️  WARNING: A checkpoint file exists!');
                $this->warn('Running without --resume will START OVER and may duplicate courses.');
                $this->newLine();
                
                if (!$this->confirm('Do you want to start fresh and clear the checkpoint?', false)) {
                    $this->info('Cancelled. To resume from checkpoint, use: php artisan wp:sync-all-courses --resume');
                    return 0;
                }
                
                $this->clearCheckpoint();
                $this->info('Checkpoint cleared. Starting fresh sync...');
                $this->newLine();
            } else {
                // No checkpoint, start fresh normally
                $this->clearCheckpoint();
            }
        }

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

        // If resuming, skip already processed courses
        if ($this->option('resume') && !empty($this->checkpoint['successful_ids'])) {
            $query->whereNotIn('id', $this->checkpoint['successful_ids']);
            $this->info("Skipping " . count($this->checkpoint['successful_ids']) . " already synced courses");
        }

        // Get total count
        $total = $query->count();
        $this->info("Total courses to sync: {$total}");
        $this->newLine();

        if ($total === 0) {
            $this->warn('No courses found matching the criteria.');
            $this->clearCheckpoint(); // Clear checkpoint if nothing to sync
            return 0;
        }

        // Apply limit and offset
        $offset = (int) $this->option('offset');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        // Always use skip() and take() together - MySQL requires LIMIT when using OFFSET
        if ($limit) {
            $query->skip($offset)->take($limit);
            $this->info("Processing courses {$offset} to " . ($offset + $limit) . " (limit: {$limit})");
        } else {
            // If no limit specified, use a large number to get all remaining records
            // Using PHP_INT_MAX would work, but a reasonable large number is safer
            $query->skip($offset)->take(999999);
            $this->info("Processing courses starting from offset {$offset} (all remaining)");
        }

        $this->newLine();

        // Get courses
        $courses = $query->orderBy('id')->get(); // Order by ID for consistent checkpointing
        $processed = 0;
        $successful = isset($this->checkpoint['successful_ids']) ? count($this->checkpoint['successful_ids']) : 0;
        $failed = isset($this->checkpoint['failed_ids']) ? count($this->checkpoint['failed_ids']) : 0;
        $skipped = 0;

        // Initialize checkpoint arrays if not resuming
        if (!$this->option('resume') || empty($this->checkpoint)) {
            $this->checkpoint = [
                'started_at' => now()->toIso8601String(),
                'last_course_id' => null,
                'successful_ids' => [],
                'failed_ids' => [],
                'total_processed' => 0,
            ];
        }

        // Create progress bar
        $bar = $this->output->createProgressBar($courses->count());
        $bar->start();

        $maxRetries = (int) $this->option('retries');
        $retryDelay = (int) $this->option('retry-delay');

        // Register shutdown handler to save checkpoint on kill
        register_shutdown_function(function () {
            $this->saveCheckpoint();
        });

        // Handle signals for graceful shutdown (Unix/Linux)
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, function () {
                $this->newLine();
                $this->warn('Received SIGTERM. Saving checkpoint...');
                $this->saveCheckpoint();
                $this->showResumeInstructions();
                exit(1);
            });
            pcntl_signal(SIGINT, function () {
                $this->newLine();
                $this->warn('Received SIGINT. Saving checkpoint...');
                $this->saveCheckpoint();
                $this->showResumeInstructions();
                exit(1);
            });
        }

        try {
            foreach ($courses as $course) {
                $processed++;

                // Skip if already successfully synced (when resuming)
                if (in_array($course->id, $this->checkpoint['successful_ids'] ?? [])) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $result = $service->syncSingleCourse($course->id, $maxRetries, $retryDelay);

                if ($result['success']) {
                    $successful++;
                    $this->checkpoint['successful_ids'][] = $course->id;
                    // Remove from failed if it was there before
                    $this->checkpoint['failed_ids'] = array_values(array_diff($this->checkpoint['failed_ids'] ?? [], [$course->id]));
                    
                    // Show retry info if it took multiple attempts
                    if (isset($result['attempts']) && $result['attempts'] > 1) {
                        $this->newLine();
                        $this->comment("Course ID {$course->id} synced after {$result['attempts']} attempt(s)");
                    }
                } else {
                    $failed++;
                    if (!in_array($course->id, $this->checkpoint['failed_ids'] ?? [])) {
                        $this->checkpoint['failed_ids'][] = $course->id;
                    }
                    
                    $this->newLine();
                    $errorMsg = $result['error'] ?? 'Unknown error';
                    $attempts = $result['attempts'] ?? 1;
                    
                    if (isset($result['retryable']) && $result['retryable']) {
                        $this->error("Failed to sync course ID {$course->id} after {$attempts} attempt(s): {$errorMsg}");
                    } else {
                        $this->error("Failed to sync course ID {$course->id}: {$errorMsg}");
                    }
                }

                // Update checkpoint
                $this->checkpoint['last_course_id'] = $course->id;
                $this->checkpoint['total_processed'] = ($this->checkpoint['total_processed'] ?? 0) + 1;
                $this->checkpoint['last_updated'] = now()->toIso8601String();

                // Save checkpoint every 10 courses
                if ($processed % 10 === 0) {
                    $this->saveCheckpoint();
                }

                // Handle signals (Unix/Linux)
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }

                $bar->advance();
            }
        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Fatal error: " . $e->getMessage());
            $this->saveCheckpoint();
            $this->showResumeInstructions();
            throw $e;
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('=== Sync Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $processed + ($this->checkpoint['total_processed'] ?? 0)],
                ['Successful', $successful],
                ['Failed', $failed],
                ['Skipped', $skipped],
            ]
        );

        // Clear checkpoint on successful completion
        if ($failed === 0) {
            $this->clearCheckpoint();
            $this->info('All courses synced successfully!');
            return 0;
        } else {
            $this->saveCheckpoint();
            $this->warn("{$failed} course(s) failed to sync. Check the logs for details.");
            $this->showResumeInstructions();
            return 1;
        }
    }

    /**
     * Load checkpoint from file
     */
    protected function loadCheckpoint(): void
    {
        if (file_exists($this->checkpointFile)) {
            $content = file_get_contents($this->checkpointFile);
            $this->checkpoint = json_decode($content, true) ?: [];
        }
    }

    /**
     * Save checkpoint to file
     */
    protected function saveCheckpoint(): void
    {
        if (!empty($this->checkpoint)) {
            $directory = dirname($this->checkpointFile);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($this->checkpointFile, json_encode($this->checkpoint, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Clear checkpoint file
     */
    protected function clearCheckpoint(): void
    {
        if (file_exists($this->checkpointFile)) {
            unlink($this->checkpointFile);
        }
        $this->checkpoint = [];
    }

    /**
     * Show instructions for resuming
     */
    protected function showResumeInstructions(): void
    {
        $this->newLine();
        $this->warn('=== Process Interrupted or Failed ===');
        $this->info('To resume from where you left off, run:');
        $this->line('  php artisan wp:sync-all-courses --resume');
        $this->newLine();
        $this->info('Checkpoint file: ' . $this->checkpointFile);
        if (!empty($this->checkpoint['last_course_id'])) {
            $this->info('Last processed course ID: ' . $this->checkpoint['last_course_id']);
        }
    }
}

