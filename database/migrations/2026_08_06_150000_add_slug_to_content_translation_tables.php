<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add per-locale slug columns to content translation tables and backfill
 * from parent-table slugs (shared today).
 */
return new class extends Migration
{
    /**
     * Translation table => [parent table, foreign key on translations, parent slug column].
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    private array $tables = [
        'webinar_translations' => ['webinars', 'webinar_id', 'slug'],
        'category_translations' => ['categories', 'category_id', 'slug'],
        'blog_category_translations' => ['blog_categories', 'blog_category_id', 'slug'],
        'blog_translations' => ['blog', 'blog_id', 'slug'],
        'product_translations' => ['products', 'product_id', 'slug'],
        'bundle_translations' => ['bundles', 'bundle_id', 'slug'],
        'upcoming_course_translations' => ['upcoming_courses', 'upcoming_course_id', 'slug'],
    ];

    public function up(): void
    {
        foreach ($this->tables as $translationTable => [$parentTable, $foreignKey, $slugColumn]) {
            if (!Schema::hasTable($translationTable) || !Schema::hasTable($parentTable)) {
                continue;
            }

            if (!Schema::hasColumn($translationTable, 'slug')) {
                Schema::table($translationTable, function (Blueprint $table) {
                    $table->string('slug', 255)->nullable()->after('locale');
                });
            }

            // Backfill translation slug from parent shared slug.
            DB::statement("
                UPDATE `{$translationTable}` AS t
                INNER JOIN `{$parentTable}` AS p ON p.id = t.`{$foreignKey}`
                SET t.slug = p.`{$slugColumn}`
                WHERE (t.slug IS NULL OR t.slug = '')
                  AND p.`{$slugColumn}` IS NOT NULL
                  AND p.`{$slugColumn}` != ''
            ");

            $indexName = $translationTable . '_locale_slug_unique';
            $indexes = collect(DB::select("SHOW INDEX FROM `{$translationTable}`"))
                ->pluck('Key_name')
                ->unique()
                ->all();

            if (!in_array($indexName, $indexes, true)) {
                Schema::table($translationTable, function (Blueprint $table) use ($indexName) {
                    $table->unique(['locale', 'slug'], $indexName);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $translationTable => $meta) {
            if (!Schema::hasTable($translationTable)) {
                continue;
            }

            $indexName = $translationTable . '_locale_slug_unique';
            $indexes = collect(DB::select("SHOW INDEX FROM `{$translationTable}`"))
                ->pluck('Key_name')
                ->unique()
                ->all();

            Schema::table($translationTable, function (Blueprint $table) use ($indexName, $indexes, $translationTable) {
                if (in_array($indexName, $indexes, true)) {
                    $table->dropUnique($indexName);
                }

                if (Schema::hasColumn($translationTable, 'slug')) {
                    $table->dropColumn('slug');
                }
            });
        }
    }
};
