<?php

namespace App\Console\Commands;

use App\Models\Webinar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Diagnose why a webinar/course is missing from the admin list or front.
 */
class DiagnoseWebinarCommand extends Command
{
    protected $signature = 'webinars:diagnose
                            {id : Webinar / course ID}
                            {--title= : Optional title fragment to search if ID row is missing}';

    protected $description = 'Explain why a webinar/course may be missing from the admin list or front';

    public function handle(): int
    {
        $id = (int) $this->argument('id');
        $titleHint = trim((string) $this->option('title'));

        $this->info("Diagnosing webinar/course #{$id}");
        $this->newLine();

        $row = DB::table('webinars')->where('id', $id)->first();

        if (!$row) {
            $this->error("No row in webinars for id={$id}.");
            $this->searchByTitle($titleHint ?: 'العلوم الامنية');

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
                    $row = [
                        $t->id,
                        $t->locale,
                        mb_substr((string) $t->title, 0, 80),
                    ];
                    if ($hasSlugCol) {
                        $row[] = (string) ($t->slug ?? '');
                    }

                    return $row;
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
            $reasons[] = 'TYPE: admin "webinars" list defaults to type=webinar, but this row type="' . $row->type . '". Open Courses / Text lessons tab, or /admin/webinars?type=' . $row->type;
        }

        if ((string) $row->status !== 'active') {
            $reasons[] = 'STATUS: status="' . $row->status . '" (not active). Status filters / some badges may hide it; it still appears in the unfiltered type list.';
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

        // Front /ar|en course lookup by localization.
        $this->newLine();
        $this->line('=== Front URL check ===');
        $hasSlugCol = Schema::hasColumn('webinar_translations', 'slug');
        foreach ($translations as $t) {
            $locale = mb_strtolower((string) $t->locale);
            $slug = $hasSlugCol ? (string) ($t->slug ?: $row->slug) : (string) ($row->slug ?? '');
            if ($slug === '') {
                $this->warn("  /{$locale}/course/… — empty slug for locale {$t->locale}");
                continue;
            }
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

        if (Schema::hasTable('delete_requests') || Schema::hasTable('content_delete_requests')) {
            $table = Schema::hasTable('content_delete_requests') ? 'content_delete_requests' : 'delete_requests';
            $cols = Schema::getColumnListing($table);
            if (in_array('webinar_id', $cols, true) || in_array('targetable_id', $cols, true)) {
                $this->newLine();
                $this->line("=== {$table} ===");
                try {
                    if (in_array('webinar_id', $cols, true)) {
                        $reqs = DB::table($table)->where('webinar_id', $id)->get();
                    } else {
                        $reqs = DB::table($table)
                            ->where('targetable_id', $id)
                            ->where('targetable_type', 'like', '%Webinar%')
                            ->get();
                    }
                    $this->line($reqs->isEmpty() ? '  none' : json_encode($reqs, JSON_UNESCAPED_UNICODE));
                } catch (\Throwable $e) {
                    $this->warn('  could not query: ' . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->line('=== Verdict ===');
        if (empty($reasons)) {
            $this->info('Row exists and should appear in the admin list for type="' . $row->type . '".');
            $this->line('If you still cannot see it, clear list filters (title / teacher / category / status) and check pagination.');
        } else {
            foreach ($reasons as $reason) {
                $this->warn('• ' . $reason);
            }
        }

        // Quick counts for list context.
        $this->newLine();
        $sameTypeCount = DB::table('webinars')->where('type', $row->type)->count();
        $this->line("Same type=\"{$row->type}\" count: {$sameTypeCount}");

        return self::SUCCESS;
    }

    private function searchByTitle(string $hint): void
    {
        if ($hint === '') {
            return;
        }

        $this->newLine();
        $this->line("Searching titles like %{$hint}% …");

        $rows = DB::table('webinar_translations as t')
            ->join('webinars as w', 'w.id', '=', 't.webinar_id')
            ->where('t.title', 'like', '%' . $hint . '%')
            ->orderByDesc('w.id')
            ->limit(20)
            ->get([
                'w.id',
                'w.type',
                'w.status',
                'w.slug as parent_slug',
                't.locale',
                't.title',
            ]);

        if ($rows->isEmpty()) {
            $this->warn('No title matches.');
            return;
        }

        $this->table(
            ['id', 'type', 'status', 'locale', 'title', 'parent_slug'],
            $rows->map(fn ($r) => [
                $r->id,
                $r->type,
                $r->status,
                $r->locale,
                mb_substr((string) $r->title, 0, 50),
                (string) ($r->parent_slug ?? ''),
            ])->all()
        );
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
