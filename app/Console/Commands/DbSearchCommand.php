<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Search all text-like DB columns for a needle (read-only).
 */
class DbSearchCommand extends Command
{
    protected $signature = 'db:search
                            {needle : Text to search for (case-insensitive LIKE)}
                            {--limit=20 : Max matching rows to print per column}
                            {--table= : Limit search to one table name}';

    protected $description = 'Search all (or one) database text columns for a string';

    public function handle(): int
    {
        $needle = (string) $this->argument('needle');
        if ($needle === '') {
            $this->error('Needle cannot be empty.');
            return 1;
        }

        $limit = max(1, (int) $this->option('limit'));
        $onlyTable = $this->option('table');
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $needle) . '%';

        $database = config('database.connections.' . config('database.default') . '.database');
        $key = 'Tables_in_' . $database;
        $tables = DB::select('SHOW TABLES');
        $textTypes = ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext', 'json'];

        $hits = 0;
        $columnsScanned = 0;

        foreach ($tables as $table) {
            $tableName = $table->$key;
            if (!empty($onlyTable) && strcasecmp($tableName, (string) $onlyTable) !== 0) {
                continue;
            }

            $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");
            foreach ($columns as $column) {
                $columnName = $column->Field;
                $type = strtolower((string) $column->Type);
                $isText = false;
                foreach ($textTypes as $t) {
                    if (str_starts_with($type, $t)) {
                        $isText = true;
                        break;
                    }
                }
                if (!$isText) {
                    continue;
                }

                $columnsScanned++;

                try {
                    $count = (int) DB::table($tableName)
                        ->where($columnName, 'like', $like)
                        ->count();
                } catch (\Throwable $e) {
                    $this->warn("Skipped {$tableName}.{$columnName}: " . $e->getMessage());
                    continue;
                }

                if ($count < 1) {
                    continue;
                }

                $hits += $count;
                $this->info("{$tableName}.{$columnName} — {$count} match(es)");

                $rows = DB::table($tableName)
                    ->select([$columnName])
                    ->where($columnName, 'like', $like)
                    ->limit($limit)
                    ->get();

                foreach ($rows as $row) {
                    $val = (string) $row->{$columnName};
                    $snippet = mb_substr(preg_replace('/\s+/u', ' ', $val) ?? $val, 0, 240);
                    $this->line('  · ' . $snippet);
                }
            }
        }

        $this->newLine();
        $this->line("Scanned {$columnsScanned} text column(s). Total matching rows: {$hits}.");
        if ($hits === 0) {
            $this->comment('No database matches. Clarity is likely injected only via GTM (not stored in MySQL).');
        }

        return 0;
    }
}
