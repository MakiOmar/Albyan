<?php

namespace App\Console\Commands;

use App\Services\DatabaseSearchReplaceService;
use Illuminate\Console\Command;

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
    public function handle(DatabaseSearchReplaceService $searchReplaceService)
    {
        $search = $this->argument('search');
        $replace = $this->argument('replace');

        // Case-sensitive substring replace (same default as the admin DB tool fast path).
        $result = $searchReplaceService->apply($search, $replace, true, false);

        $this->info(sprintf(
            'Search and replace completed. Replaced %d occurrence(s) in %d record(s).',
            $result['total_occurrences'],
            $result['updated_records']
        ));

        return 0;
    }
}
