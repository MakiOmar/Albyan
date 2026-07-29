<?php

namespace App\Console\Commands;

use App\Http\Controllers\Web\HomeController;
use Illuminate\Console\Command;

/**
 * Clear and rebuild the homepage section data cache (home.page_data.*).
 */
class RegenerateHomePageCacheCommand extends Command
{
    protected $signature = 'home:cache-regenerate
                            {--locale= : Warm only this locale (e.g. ar, en)}
                            {--clear-only : Forget cache keys without warming}';

    protected $description = 'Regenerate (clear + warm) the homepage data cache used when performance mode is cached';

    public function handle(HomeController $home): int
    {
        $locale = $this->option('locale');
        $clearOnly = (bool) $this->option('clear-only');

        if ($clearOnly) {
            HomeController::clearHomePageCache();
            $this->info('Homepage data cache cleared.');
            return 0;
        }

        $locales = null;
        if (!empty($locale)) {
            $locales = [strtolower(trim((string) $locale))];
        }

        $this->line('Regenerating homepage data cache…');
        $warmed = $home->regenerateHomePageCache($locales);

        if (empty($warmed)) {
            $this->warn('No cache keys were warmed.');
            return 1;
        }

        foreach ($warmed as $key) {
            $this->line('  warmed: ' . $key);
        }

        $this->info('Done. Warmed ' . count($warmed) . ' locale(s). TTL=' . HomeController::HOME_CACHE_TTL . 's');
        $this->line('Mode: ' . getHomepageCacheMode() . ' (admin Performance settings)');

        return 0;
    }
}
