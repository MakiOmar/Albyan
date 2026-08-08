<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use RuntimeException;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'database:backup
                            {--scheduled : Respect auto-backup enabled/interval settings (used by the scheduler)}
                            {--force : When used with --scheduled, create even if disabled/not due}';

    protected $description = 'Create a full MySQL dump into the configured backup directory';

    public function handle(DatabaseBackupService $backupService): int
    {
        $scheduled = (bool) $this->option('scheduled');
        $force = (bool) $this->option('force');

        try {
            if ($scheduled) {
                if (!$force && !$backupService->shouldRunScheduledBackup()) {
                    $auto = $backupService->getAutoSettings();
                    $this->line(
                        'Skipped (enabled=' . ($auto['enabled'] ? 'yes' : 'no')
                        . ', interval=' . $auto['interval'] . ').'
                    );
                    return self::SUCCESS;
                }

                $result = $backupService->runScheduledBackup();
            } else {
                $result = $backupService->createBackup();
            }
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info('Backup created: ' . $result['filename'] . ' (' . $result['size_human'] . ')');

        return self::SUCCESS;
    }
}
