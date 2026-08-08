<?php

namespace App\Console\Commands;

use App\Models\Webinar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Diagnose why a webinar/course is missing from the admin list or front.
 * Search by ID and/or title fragment.
 */
class DiagnoseWebinarCommand extends Command
{
    protected $signature = 'webinars:diagnose
                            {id? : Webinar / course ID (optional if --title is given)}
                            {--title= : Title fragment to search (Arabic/English)}
                            {--slug= : Slug fragment to search}
                            {--limit=30 : Max search results}';

    protected $description = 'Explain why a webinar/course may be missing from the admin list or front';

    public function handle(): int
    {
        $idArg = $this->argument('id');
        $id = ($idArg !== null && $idArg !== '') ? (int) $idArg : null;
        $titleHint = trim((string) $this->option('title'));
        $slugHint = trim((string) $this->option('slug'));
        $limit = max(1, (int) $this->option('limit'));

        if (empty($id) && $titleHint === '' && $slugHint === '') {
            $this->error('Provide an ID and/or --title=... and/or --slug=...');
            $this->line('Examples:');
            $this->line('  php artisan webinars:diagnose 2204');
            $this->line('  php artisan webinars:diagnose --title="العلوم الامنية"');
            $this->line('  php artisan webinars:diagnose --slug="Security-Sciences"');

            return self::FAILURE;
        }

        // Title / slug search mode (also used when ID is missing or not found).
        if ($titleHint !== '' || $slugHint !== '') {
            $matches = $this->searchCourses($titleHint, $slugHint, $limit);

            if ($matches->isEmpty()) {
                $this->error('No courses matched the search.');
                return self::FAILURE;
            }

            $this->info('Search results:');
            $this->printSearchTable($matches);
            $this->newLine();

            if (empty($id)) {
                if ($matches->count() === 1) {
                    $id = (int) $matches->first()->id;
                    $this->comment("Only one match — diagnosing #{$id}");
                    $this->newLine();
                } else {
                    $this->comment('Multiple matches. Re-run with a specific ID, e.g.:');
                    $this->line('  php artisan webinars:diagnose ' . $matches->first()->id);
                    return self::SUCCESS;
                }
            }
        }

        $this->info("Diagnosing webinar/course #{$id}");
        $this->newLine();

        $row = DB::table('webinars')->where('id', $id)->first();

        if (!$row) {
            $this->error("No row in webinars for id={$id}.");
            if ($titleHint === '' && $slugHint === '') {
                $this->comment('Try: php artisan webinars:diagnose --title="..."');
            }

            return self::FAILURE;
        }

        $this->line('=== Parent row (webinars) ===');
        $this->table(
            ['Field', 'Value'],
            [
                ['id', $row->id],
                ['type', $row->type],
                ['status', $row->status],
                ['private', $this->boolLabel($row->private ?? null)],
                ['slug (parent)', $row->slug ?? ''],
                ['teacher_id', $row->teacher_id],
                ['creator_id', $row->creator_id],
                ['category_id', $row->category_id],
                ['price', $row->price],
                ['start_date', $this->formatTs($row->start_date ?? null)],
                ['created_at', $this->formatTs($row->created_at ?? null)],
                ['updated_at', $this->formatTs($row->updated_at ?? null)],
            ]
        );

        $translationColumns = ['id', 'locale', 'title'];
        if (Schema::hasColumn('webinar_translations', 'slug')) {
            $translationColumns[] = 'slug';
        }

        $translations = DB::table('webinar_translations')
            ->where('webinar_id', $id)
            ->orderBy('id')
            ->get($translationColumns);

        $this->line('=== Translations ===');
        if ($translations->isEmpty()) {
            $this->warn('No webinar_translations rows.');
        } else {
            $hasSlugCol = in_array('slug', $translationColumns, true);
            if (!$hasSlugCol) {
                $this->warn('webinar_translations.slug column is missing — run migrations.');
            }

            $this->table(
                $hasSlugCol ? ['id', 'locale', 'title', 'slug'] : ['id', 'locale', 'title'],
                $translations->map(function ($t) use ($hasSlugCol) {
                    $rowData = [
                        $t->id,
                        $t->locale,
                        mb_substr((string) $t->title, 0, 80),
                    ];
                    if ($hasSlugCol) {
                        $rowData[] = (string) ($t->slug ?? '');
                    }

                    return $rowData;
                })->all()
            );

            $localeGroups = $translations->groupBy(fn ($t) => mb_strtolower((string) $t->locale));
            foreach ($localeGroups as $locale => $group) {
                if ($group->count() > 1) {
                    $this->warn("Duplicate locale rows for '{$locale}' (ids: " . $group->pluck('id')->implode(', ') . ').');
                }
            }
        }

        $this->newLine();
        $this->line('=== Admin list visibility ===');
        $this->comment('Admin WebinarController@index defaults to ?type=webinar');

        $reasons = [];

        foreach (['webinar', 'course', 'text_lesson'] as $type) {
            $inTypeList = ((string) $row->type === $type);
            $this->line(sprintf(
                '  %-12s tab: %s',
                $type,
                $inTypeList ? '<fg=green>VISIBLE</> (matches type)' : '<fg=red>HIDDEN</> (type is "' . $row->type . '")'
            ));
        }

        if ((string) $row->type !== 'webinar') {
            $reasons[] = 'TYPE: admin "webinars" list defaults to type=webinar, but this row type="' . $row->type . '". Open Courses tab, or /admin/webinars?type=' . $row->type;
        }

        if ((string) $row->status !== 'active') {
            $reasons[] = 'STATUS: status="' . $row->status . '" (not active).';
        }

        if (!empty($row->private)) {
            $reasons[] = 'PRIVATE: private=1 — hidden on the public front for guests, but still listed in admin.';
        }

        $teacherExists = !empty($row->teacher_id)
            && DB::table('users')->where('id', $row->teacher_id)->exists();
        if (!$teacherExists) {
            $reasons[] = 'TEACHER: teacher_id=' . ($row->teacher_id ?? 'null') . ' missing from users.';
        }

        $categoryExists = empty($row->category_id)
            || DB::table('categories')->where('id', $row->category_id)->exists();
        if (!$categoryExists) {
            $reasons[] = 'CATEGORY: category_id=' . $row->category_id . ' missing from categories.';
        }

        $this->newLine();
        $this->line('=== Front URL check ===');
        $hasSlugCol = Schema::hasColumn('webinar_translations', 'slug');
        $slugCandidates = [];
        if (!empty($row->slug)) {
            $slugCandidates['parent'] = (string) $row->slug;
        }
        foreach ($translations as $t) {
            $locale = mb_strtolower((string) $t->locale);
            $slug = $hasSlugCol ? (string) ($t->slug ?: $row->slug) : (string) ($row->slug ?? '');
            if ($slug === '') {
                $this->warn("  /{$locale}/course/… — empty slug for locale {$t->locale}");
                continue;
            }
            $slugCandidates[$locale] = $slug;
            $this->checkFrontSlug($id, $locale, $slug);
        }

        // Also try parent slug under default locale if not already checked.
        if (!empty($row->slug) && !isset($slugCandidates['ar'])) {
            $this->checkFrontSlug($id, 'ar', (string) $row->slug);
        }

        $this->newLine();
        $this->line('=== Verdict ===');
        if (empty($reasons)) {
            $this->info('Row exists and should appear in the admin list for type="' . $row->type . '".');
            $this->line('If you still cannot see it, clear list filters (title / teacher / category / status) and check pagination.');
            $this->line('After a slug change, old URLs 404 — use the current slug above.');
        } else {
            foreach ($reasons as $reason) {
                $this->warn('• ' . $reason);
            }
            $this->line('After a slug change, old URLs 404 — use the current slug listed above.');
        }

        $this->newLine();
        $sameTypeCount = DB::table('webinars')->where('type', $row->type)->count();
        $this->line("Same type=\"{$row->type}\" count: {$sameTypeCount}");

        return self::SUCCESS;
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function searchCourses(string $titleHint, string $slugHint, int $limit)
    {
        $hasSlugCol = Schema::hasColumn('webinar_translations', 'slug');

        $query = DB::table('webinars as w')
            ->leftJoin('webinar_translations as t', 't.webinar_id', '=', 'w.id')
            ->select([
                'w.id',
                'w.type',
                'w.status',
                'w.private',
                'w.slug as parent_slug',
                't.locale',
                't.title',
            ])
            ->orderByDesc('w.id')
            ->limit($limit);

        if ($hasSlugCol) {
            $query->addSelect('t.slug as translation_slug');
        }

        $query->where(function ($q) use ($titleHint, $slugHint, $hasSlugCol) {
            $added = false;

            if ($titleHint !== '') {
                $q->where('t.title', 'like', '%' . $titleHint . '%');
                $added = true;
            }

            if ($slugHint !== '') {
                $method = $added ? 'orWhere' : 'where';
                $q->{$method}(function ($inner) use ($slugHint, $hasSlugCol) {
                    $inner->where('w.slug', 'like', '%' . $slugHint . '%');
                    if ($hasSlugCol) {
                        $inner->orWhere('t.slug', 'like', '%' . $slugHint . '%');
                    }
                });
            }
        });

        return $query->get();
    }

    private function printSearchTable($rows): void
    {
        $hasSlugCol = Schema::hasColumn('webinar_translations', 'slug');

        $this->table(
            $hasSlugCol
                ? ['id', 'type', 'status', 'private', 'locale', 'title', 't.slug', 'parent_slug']
                : ['id', 'type', 'status', 'private', 'locale', 'title', 'parent_slug'],
            collect($rows)->map(function ($r) use ($hasSlugCol) {
                $row = [
                    $r->id,
                    $r->type,
                    $r->status,
                    $this->boolLabel($r->private ?? null),
                    $r->locale ?? '',
                    mb_substr((string) ($r->title ?? ''), 0, 50),
                ];
                if ($hasSlugCol) {
                    $row[] = (string) ($r->translation_slug ?? '');
                }
                $row[] = (string) ($r->parent_slug ?? '');

                return $row;
            })->all()
        );
    }

    private function checkFrontSlug(int $id, string $locale, string $slug): void
    {
        try {
            $found = Webinar::query()->whereLocalizedSlug($slug, $locale)->where('id', $id)->exists();
            $this->line(sprintf(
                '  /%s/course/%s → %s',
                $locale,
                $slug,
                $found ? '<fg=green>resolves</>' : '<fg=red>does not resolve</>'
            ));
        } catch (\Throwable $e) {
            $this->warn("  /{$locale}/course/{$slug} → lookup error: " . $e->getMessage());
        }
    }

    private function boolLabel($value): string
    {
        if ($value === null) {
            return 'null';
        }

        return ((int) $value === 1 || $value === true || $value === '1') ? 'yes' : 'no';
    }

    private function formatTs($value): string
    {
        if (empty($value)) {
            return '';
        }

        if (!is_numeric($value)) {
            return (string) $value;
        }

        return date('Y-m-d H:i:s', (int) $value) . " ({$value})";
    }
}
