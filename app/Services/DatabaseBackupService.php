<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    /**
     * Ensure the backup directory exists and is writable.
     */
    public function ensureBackupDirectory(): string
    {
        $path = $this->backupDirectory();

        if (!is_dir($path) && !mkdir($path, 0750, true) && !is_dir($path)) {
            throw new RuntimeException('Unable to create backup directory.');
        }

        if (!is_writable($path)) {
            throw new RuntimeException('Backup directory is not writable.');
        }

        return $path;
    }

    /**
     * Absolute configured backup directory (real path when possible).
     */
    public function backupDirectory(): string
    {
        $path = (string) config('database_backup.path', storage_path('app/backups'));
        $path = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);

        return $path;
    }

    /**
     * @return list<array{filename:string,size:int,size_human:string,modified_at:int,modified_human:string}>
     */
    public function listBackups(): array
    {
        $dir = $this->ensureBackupDirectory();
        $pattern = (string) config('database_backup.filename_regex');
        $items = [];

        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (!preg_match($pattern, $entry)) {
                continue;
            }

            $full = $dir . DIRECTORY_SEPARATOR . $entry;
            if (!is_file($full)) {
                continue;
            }

            $size = (int) filesize($full);
            $mtime = (int) filemtime($full);

            $items[] = [
                'filename' => $entry,
                'size' => $size,
                'size_human' => $this->formatBytes($size),
                'modified_at' => $mtime,
                'modified_human' => date('Y-m-d H:i:s', $mtime),
            ];
        }

        usort($items, static fn ($a, $b) => $b['modified_at'] <=> $a['modified_at']);

        return $items;
    }

    /**
     * Create a new full mysqldump and enforce retention.
     *
     * @return array{filename:string,path:string,size:int,size_human:string}
     */
    public function createBackup(): array
    {
        $dir = $this->ensureBackupDirectory();
        $prefix = (string) config('database_backup.filename_prefix', 'albyan-backup-');
        $filename = $prefix . date('Ymd-His') . '.sql';
        $target = $dir . DIRECTORY_SEPARATOR . $filename;

        $connection = $this->mysqlConnection();
        $mysqldump = (string) config('database_backup.mysqldump_bin', 'mysqldump');
        $defaultsFile = $this->writeDefaultsExtraFile($connection);

        try {
            $command = [
                $mysqldump,
                '--defaults-extra-file=' . $defaultsFile,
                '--single-transaction',
                '--quick',
                '--routines',
                '--triggers',
                '--hex-blob',
                '--default-character-set=utf8mb4',
                '--result-file=' . $target,
                $connection['database'],
            ];

            $process = new Process($command);
            $process->setTimeout(3600);
            $process->run();

            if (!$process->isSuccessful()) {
                @unlink($target);
                $error = trim($process->getErrorOutput() ?: $process->getOutput());
                throw new RuntimeException(
                    'mysqldump failed' . ($error !== '' ? ': ' . $this->sanitizeProcessOutput($error) : '.')
                );
            }

            if (!is_file($target) || filesize($target) < 32) {
                @unlink($target);
                throw new RuntimeException('Backup file was empty or was not created.');
            }
        } finally {
            $this->secureUnlink($defaultsFile);
        }

        $this->enforceRetention();

        $size = (int) filesize($target);

        Log::info('Database backup created', [
            'filename' => $filename,
            'size' => $size,
            'user_id' => Auth::id(),
        ]);

        return [
            'filename' => $filename,
            'path' => $target,
            'size' => $size,
            'size_human' => $this->formatBytes($size),
        ];
    }

    /**
     * Resolve a safe absolute path for an allowlisted backup basename.
     */
    public function resolveBackupPath(string $filename): string
    {
        $filename = basename(str_replace(["\0", '\\', '/'], '', $filename));
        $pattern = (string) config('database_backup.filename_regex');

        if ($filename === '' || !preg_match($pattern, $filename)) {
            throw new RuntimeException('Invalid backup filename.');
        }

        $dir = $this->ensureBackupDirectory();
        $full = $dir . DIRECTORY_SEPARATOR . $filename;
        $realFile = realpath($full);
        $realDir = realpath($dir);

        if ($realDir === false || $realFile === false || !is_file($realFile)) {
            throw new RuntimeException('Backup file not found.');
        }

        $dirPrefix = rtrim($realDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($realFile, $dirPrefix)) {
            throw new RuntimeException('Backup path is outside the allowed directory.');
        }

        return $realFile;
    }

    public function deleteBackup(string $filename): void
    {
        $path = $this->resolveBackupPath($filename);

        if (!@unlink($path)) {
            throw new RuntimeException('Unable to delete backup file.');
        }

        Log::info('Database backup deleted', [
            'filename' => $filename,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Restore the live database from an allowlisted backup file.
     */
    public function restoreBackup(string $filename, string $confirmPhrase): void
    {
        $expected = (string) config('database_backup.confirm_phrase', 'RESTORE');
        if (!hash_equals($expected, $confirmPhrase)) {
            throw new RuntimeException('Confirmation phrase is incorrect.');
        }

        $path = $this->resolveBackupPath($filename);
        if (filesize($path) < 32) {
            throw new RuntimeException('Backup file is empty.');
        }

        $connection = $this->mysqlConnection();
        $mysql = (string) config('database_backup.mysql_bin', 'mysql');
        $defaultsFile = $this->writeDefaultsExtraFile($connection);
        $maintenanceEnabled = false;

        try {
            Artisan::call('down', [
                '--retry' => 60,
                '--secret' => bin2hex(random_bytes(8)),
            ]);
            $maintenanceEnabled = true;

            $command = [
                $mysql,
                '--defaults-extra-file=' . $defaultsFile,
                '--default-character-set=utf8mb4',
                $connection['database'],
            ];

            $process = new Process($command);
            $process->setTimeout(7200);
            $process->setInput(fopen($path, 'rb'));
            $process->run();

            if (!$process->isSuccessful()) {
                $error = trim($process->getErrorOutput() ?: $process->getOutput());
                throw new RuntimeException(
                    'mysql restore failed' . ($error !== '' ? ': ' . $this->sanitizeProcessOutput($error) : '.')
                );
            }

            try {
                Artisan::call('cache:clear');
            } catch (\Throwable $e) {
                Log::warning('cache:clear failed after database restore', [
                    'message' => $e->getMessage(),
                ]);
            }

            try {
                Artisan::call('clear:all', ['--force' => true]);
            } catch (\Throwable $e) {
                // Optional project command; ignore if missing.
            }

            Log::warning('Database restored from backup', [
                'filename' => $filename,
                'user_id' => Auth::id(),
            ]);
        } finally {
            $this->secureUnlink($defaultsFile);

            if ($maintenanceEnabled) {
                try {
                    Artisan::call('up');
                } catch (\Throwable $e) {
                    Log::error('Failed to exit maintenance mode after restore', [
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    public function confirmPhrase(): string
    {
        return (string) config('database_backup.confirm_phrase', 'RESTORE');
    }

    /**
     * Delete oldest backups beyond retention limit.
     */
    public function enforceRetention(): void
    {
        $retention = max(1, (int) config('database_backup.retention', 10));
        $backups = $this->listBackups();

        if (count($backups) <= $retention) {
            return;
        }

        $toDelete = array_slice($backups, $retention);
        foreach ($toDelete as $item) {
            try {
                $this->deleteBackup($item['filename']);
            } catch (\Throwable $e) {
                Log::warning('Failed to prune old backup', [
                    'filename' => $item['filename'],
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @return array{host:string,port:string,database:string,username:string,password:string}
     */
    private function mysqlConnection(): array
    {
        $config = config('database.connections.mysql', []);

        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');

        if ($database === '' || $username === '') {
            throw new RuntimeException('MySQL connection is not configured.');
        }

        return [
            'host' => (string) ($config['host'] ?? '127.0.0.1'),
            'port' => (string) ($config['port'] ?? '3306'),
            'database' => $database,
            'username' => $username,
            'password' => (string) ($config['password'] ?? ''),
        ];
    }

    /**
     * Write a short-lived defaults file so the password is not passed on argv.
     */
    private function writeDefaultsExtraFile(array $connection): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dbbak_');
        if ($tmp === false) {
            throw new RuntimeException('Unable to create temporary credentials file.');
        }

        $password = str_replace(['\\', '"'], ['\\\\', '\"'], $connection['password']);

        $contents = "[client]\n"
            . 'host="' . addcslashes($connection['host'], "\\\"\n\r") . "\"\n"
            . 'port="' . addcslashes($connection['port'], "\\\"\n\r") . "\"\n"
            . 'user="' . addcslashes($connection['username'], "\\\"\n\r") . "\"\n"
            . 'password="' . $password . "\"\n";

        if (file_put_contents($tmp, $contents) === false) {
            $this->secureUnlink($tmp);
            throw new RuntimeException('Unable to write temporary credentials file.');
        }

        @chmod($tmp, 0600);

        return $tmp;
    }

    private function secureUnlink(string $path): void
    {
        if ($path !== '' && is_file($path)) {
            @unlink($path);
        }
    }

    private function sanitizeProcessOutput(string $output): string
    {
        // Avoid leaking credential-like fragments into UI/logs.
        $output = preg_replace('/password\s*=\s*"[^"]*"/i', 'password="***"', $output) ?? $output;
        $output = preg_replace('/--password=\S+/i', '--password=***', $output) ?? $output;

        return mb_substr(trim($output), 0, 500);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        return round($bytes / 1073741824, 2) . ' GB';
    }
}
