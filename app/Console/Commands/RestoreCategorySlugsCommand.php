<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Restore category slugs from the albyan_2026-08-05 backup.
 *
 * Slugs live on `categories` (not translations). Saving an English translation
 * can regenerate the shared slug via Category::makeSlug($title).
 */
class RestoreCategorySlugsCommand extends Command
{
    protected $signature = 'categories:restore-slugs
                            {--dry-run : Show planned changes without writing}
                            {--show-map : Print the restore map and exit}';

    protected $description = 'Restore category slugs from the 2026-08-05 backup (Arabic/original URLs)';

    /**
     * id => slug from albyan_2026-08-05.sql (`categories` dump).
     *
     * @var array<int, string>
     */
    private const SLUG_MAP = [
        636 => 'التدريب-المهنى-للتربويين',
        637 => 'المهارات-الاجتماعية-و-السلوكية',
        638 => 'مجال-التسويق-والمبيعات',
        639 => 'المحاسبة-والشؤون-المالية',
        640 => 'القانون',
        641 => 'الموارد-البشرية',
        642 => 'الإدارة',
        643 => 'السكرتارية-والأعمال-المكتبية',
        644 => 'تقنيات-رقمية-وبرمجيات',
        645 => 'اللغات',
        646 => 'الإنتاج-الإعلامي',
        647 => 'الصحة',
        648 => 'السلامة-المهنية',
        649 => 'المجال-الامني',
        650 => 'ديزاين',
        651 => 'الرعاية-والتعليم',
        652 => 'Engineering',
        653 => 'Sports',
        654 => 'الدبلوم-العالي',
    ];

    public function handle(): int
    {
        if ($this->option('show-map')) {
            $this->printMap();

            return self::SUCCESS;
        }

        $dry = (bool) $this->option('dry-run');

        if ($dry) {
            $this->warn('Dry run — no changes will be written.');
        }

        $rows = [];
        $updated = 0;
        $unchanged = 0;
        $missing = 0;

        foreach (self::SLUG_MAP as $id => $targetSlug) {
            $category = Category::query()->find($id);

            if (!$category) {
                $missing++;
                $rows[] = [$id, '—', $targetSlug, 'missing'];
                continue;
            }

            $current = (string) $category->slug;

            if ($current === $targetSlug) {
                $unchanged++;
                $rows[] = [$id, $current, $targetSlug, 'unchanged'];
                continue;
            }

            $rows[] = [$id, $current, $targetSlug, $dry ? 'would-update' : 'updated'];

            if (!$dry) {
                // Bypass Sluggable observers; set the exact backup slug.
                DB::table('categories')->where('id', $id)->update(['slug' => $targetSlug]);
            }

            $updated++;
        }

        $this->table(['ID', 'Current slug', 'Backup slug', 'Status'], $rows);

        $this->info("Updated: {$updated} | Unchanged: {$unchanged} | Missing: {$missing}");

        if (!$dry && $updated > 0) {
            cache()->forget(Category::$cacheKey);
            $this->line('Cleared categories cache key.');
        }

        $this->line('Examples:');
        $this->line('  php artisan categories:restore-slugs --show-map');
        $this->line('  php artisan categories:restore-slugs --dry-run');
        $this->line('  php artisan categories:restore-slugs');

        return self::SUCCESS;
    }

    private function printMap(): void
    {
        $this->info('Restore map (category id => backup slug):');
        $this->line(var_export(self::SLUG_MAP, true));
    }
}
