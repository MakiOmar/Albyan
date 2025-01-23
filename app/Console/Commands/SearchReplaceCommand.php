<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SearchReplaceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:search-replace {search} {replace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search and replace text in the entire database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $search = $this->argument('search');
        $replace = $this->argument('replace');

        $tables = DB::select('SHOW TABLES');
        $database = config('database.connections.mysql.database');
        $key = 'Tables_in_' . $database;

        foreach ($tables as $table) {
            $tableName = $table->$key;
            $columns = DB::select("SHOW COLUMNS FROM `$tableName`");

            foreach ($columns as $column) {
                $columnName = $column->Field;

                try {
                    DB::statement("
                        UPDATE `$tableName`
                        SET `$columnName` = REPLACE(`$columnName`, ?, ?)
                        WHERE `$columnName` LIKE ?
                    ", [$search, $replace, "%$search%"]);
                } catch (\Exception $e) {
                    // Skip non-text columns or other issues
                    $this->warn("Skipped $tableName.$columnName: " . $e->getMessage());
                }
            }
        }

        $this->info('Search and replace completed.');
        return 0;
    }
}
