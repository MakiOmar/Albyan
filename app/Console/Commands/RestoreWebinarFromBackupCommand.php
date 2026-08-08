<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Recreate webinars/courses deleted by mistake from the Aug 2026 SQL backup.
 *
 * Built-in payloads: 2204 (EN slug) and 2492 (AR slug).
 * Also supports extracting any webinar id from a mysqldump file.
 */
class RestoreWebinarFromBackupCommand extends Command
{
    protected $signature = 'webinars:restore-from-backup
                            {id? : Webinar ID to restore (default: restores built-in 2204 and 2492)}
                            {--dump= : Path to a .sql mysqldump (skip interactive backup picker)}
                            {--backup-dir=/root : Directory to list SQL backups from (SSH root home)}
                            {--force : Update existing row instead of skipping}
                            {--dry-run : Show what would be written without writing}
                            {--teacher= : Override teacher_id/creator_id if backup teacher is missing}
                            {--builtin : Use embedded JSON payloads instead of a dump file}';

    protected $description = 'Recreate a deleted webinar/course from /root SQL backups or built-in payloads';

    /** @var array<int, array<string, mixed>> */
    private array $builtinWebinars = [];

    /** @var array<int, array<int, array<string, mixed>>> webinar_id => translations */
    private array $builtinTranslations = [];

    public function handle(): int
    {
        $this->loadBuiltinPayloads();

        $idArg = $this->argument('id');
        $dump = trim((string) ($this->option('dump') ?: ''));
        $dry = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $useBuiltin = (bool) $this->option('builtin');

        $ids = [];
        if ($idArg !== null && $idArg !== '') {
            $ids[] = (int) $idArg;
        } else {
            $ids = array_keys($this->builtinWebinars);
            if (empty($ids)) {
                $ids = [2492, 2204];
            }
            $this->comment('No ID given — will restore: ' . implode(', ', $ids));
        }

        // Interactive backup picker when no dump path was given.
        if ($dump === '' && !$useBuiltin) {
            try {
                $dump = $this->pickDumpInteractively();
            } catch (\RuntimeException $e) {
                $this->warn($e->getMessage());
                return self::FAILURE;
            }
            if ($dump === null) {
                // User chose built-in payloads (or none available).
                $useBuiltin = true;
            }
        }

        if (!$useBuiltin && $dump !== '') {
            if (!is_file($dump)) {
                $this->error("Dump not found: {$dump}");
                return self::FAILURE;
            }

            $this->info("Using dump: {$dump}");
            foreach ($ids as $id) {
                $extracted = $this->extractFromDump($dump, $id);
                if (empty($extracted['webinar'])) {
                    $this->error("ID {$id} not found in dump.");
                    return self::FAILURE;
                }
                $this->builtinWebinars[$id] = $extracted['webinar'];
                $this->builtinTranslations[$id] = $extracted['translations'];
            }
        } elseif ($useBuiltin) {
            $this->comment('Using embedded JSON payloads (database/data/webinar_restore).');
        }

        $ok = 0;
        foreach ($ids as $id) {
            if ($this->restoreOne($id, $dry, $force)) {
                $ok++;
            }
        }

        $this->newLine();
        $this->info("Done. Restored/verified {$ok}/" . count($ids) . " course(s).");

        return $ok === count($ids) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * List SQL files under the backup dir (default /root) and ask which to use.
     * Returns absolute path, or null to use built-in payloads.
     */
    private function pickDumpInteractively(): ?string
    {
        $dir = (string) $this->option('backup-dir');
        $dir = $dir !== '' ? $dir : '/root';

        if (!is_dir($dir)) {
            $this->warn("Backup directory not found: {$dir}");
            if (!empty($this->builtinWebinars)) {
                $this->comment('Falling back to built-in payloads.');
                return null;
            }
            $this->error('No backups and no built-in payloads available.');
            return null;
        }

        $files = glob(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        // Prefer newest first.
        usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));

        if (empty($files)) {
            $this->warn("No *.sql files in {$dir}");
            return empty($this->builtinWebinars) ? null : null;
        }

        $this->newLine();
        $this->info("SQL backups in {$dir}:");

        $choices = [];
        $labels = [];
        foreach ($files as $i => $path) {
            $sizeMb = round(filesize($path) / 1048576, 1);
            $mtime = date('Y-m-d H:i', filemtime($path));
            $label = sprintf('%s  (%s MB, %s)', basename($path), $sizeMb, $mtime);
            $key = (string) ($i + 1);
            $choices[$key] = $path;
            $labels[$key] = $label;
            $this->line(sprintf('  [%s] %s', $key, $label));
        }

        $builtinKey = 'builtin';
        if (!empty($this->builtinWebinars)) {
            $labels[$builtinKey] = 'Use embedded JSON payloads (no dump file)';
            $this->line(sprintf('  [%s] %s', $builtinKey, $labels[$builtinKey]));
        }

        $cancelKey = 'cancel';
        $labels[$cancelKey] = 'Cancel';
        $this->line(sprintf('  [%s] %s', $cancelKey, $labels[$cancelKey]));

        if (!$this->input->isInteractive()) {
            // Non-interactive (CI): prefer newest dump when present.
            $newest = $files[0];
            $this->warn("Non-interactive mode — using newest dump: {$newest}");
            return $newest;
        }

        $picked = $this->choice(
            'Which backup should be used for restore?',
            array_values($labels),
            0
        );

        // Map label back to key/path.
        $selectedKey = array_search($picked, $labels, true);
        if ($selectedKey === false || $selectedKey === $cancelKey) {
            throw new \RuntimeException('Restore cancelled.');
        }
        if ($selectedKey === $builtinKey) {
            return null;
        }

        return $choices[$selectedKey] ?? null;
    }

    private function loadBuiltinPayloads(): void
    {
        $base = database_path('data/webinar_restore');
        $webinarsFile = $base . '/webinars_2204_2492.json';
        $translationsFile = $base . '/webinar_translations_2204_2492.json';

        if (!is_file($webinarsFile) || !is_file($translationsFile)) {
            $this->warn('Built-in payload JSON missing under database/data/webinar_restore.');
            return;
        }

        $webinars = json_decode((string) file_get_contents($webinarsFile), true) ?: [];
        $translations = json_decode((string) file_get_contents($translationsFile), true) ?: [];

        foreach ($webinars as $row) {
            $this->builtinWebinars[(int) $row['id']] = $row;
        }
        foreach ($translations as $row) {
            $wid = (int) $row['webinar_id'];
            $this->builtinTranslations[$wid][] = $row;
        }
    }

    private function restoreOne(int $id, bool $dry, bool $force): bool
    {
        $this->newLine();
        $this->info("=== Restoring webinar #{$id} ===");

        $webinar = $this->builtinWebinars[$id] ?? null;
        if (!$webinar) {
            $this->error("No payload for #{$id}. Pass --dump=path/to.sql");
            return false;
        }

        $translations = $this->builtinTranslations[$id] ?? [];
        $existing = DB::table('webinars')->where('id', $id)->first();

        if ($existing && !$force) {
            $this->warn("Already exists (slug={$existing->slug}). Use --force to overwrite parent+translations.");
            return true;
        }

        $teacherOverride = $this->option('teacher');
        $teacherId = $teacherOverride !== null && $teacherOverride !== ''
            ? (int) $teacherOverride
            : (int) ($webinar['teacher_id'] ?? 0);

        if ($teacherId && !DB::table('users')->where('id', $teacherId)->exists()) {
            $fallback = DB::table('users')->orderBy('id')->value('id');
            $this->warn("Teacher #{$teacherId} missing — falling back to user #{$fallback}.");
            $teacherId = (int) $fallback;
        }

        $payload = $webinar;
        unset($payload['deleted_at']);
        $payload['teacher_id'] = $teacherId ?: ($payload['teacher_id'] ?? null);
        $payload['creator_id'] = $teacherId ?: ($payload['creator_id'] ?? null);

        // Drop columns that do not exist on current schema.
        $payload = $this->filterColumns('webinars', $payload);

        $this->line('Parent: type=' . ($payload['type'] ?? '') . ' slug=' . ($payload['slug'] ?? '') . ' status=' . ($payload['status'] ?? ''));
        foreach ($translations as $t) {
            $this->line('  translation [' . ($t['locale'] ?? '?') . ']: ' . mb_substr((string) ($t['title'] ?? ''), 0, 80));
        }

        if ($dry) {
            $this->comment('Dry run — no writes.');
            return true;
        }

        DB::beginTransaction();
        try {
            if ($existing) {
                $update = $payload;
                unset($update['id']);
                DB::table('webinars')->where('id', $id)->update($update);
                $this->info("Updated webinars.id={$id}");
            } else {
                // Preserve original ID.
                DB::table('webinars')->insert($payload);
                $this->info("Inserted webinars.id={$id}");
            }

            $hasSlugCol = Schema::hasColumn('webinar_translations', 'slug');

            foreach ($translations as $t) {
                $locale = mb_strtolower((string) ($t['locale'] ?? 'ar'));
                $row = [
                    'webinar_id' => $id,
                    'locale' => $locale,
                    'title' => $t['title'] ?? '',
                    'description' => $t['description'] ?? null,
                    'seo_description' => $t['seo_description'] ?? null,
                ];

                if ($hasSlugCol) {
                    // Prefer explicit translation slug; else parent slug for default locale.
                    $row['slug'] = $t['slug']
                        ?? (($locale === (function_exists('getDefaultLocaleCode') ? getDefaultLocaleCode() : 'ar'))
                            ? ($payload['slug'] ?? null)
                            : ($payload['slug'] ?? null));
                }

                $row = $this->filterColumns('webinar_translations', $row);

                $existingTranslationId = DB::table('webinar_translations')
                    ->where('webinar_id', $id)
                    ->whereRaw('LOWER(locale) = ?', [$locale])
                    ->orderBy('id')
                    ->value('id');

                if ($existingTranslationId) {
                    DB::table('webinar_translations')->where('id', $existingTranslationId)->update($row);
                    // Remove case-duplicates.
                    DB::table('webinar_translations')
                        ->where('webinar_id', $id)
                        ->whereRaw('LOWER(locale) = ?', [$locale])
                        ->where('id', '!=', $existingTranslationId)
                        ->delete();
                    $this->info("Updated translation locale={$locale}");
                } else {
                    DB::table('webinar_translations')->insert($row);
                    $this->info("Inserted translation locale={$locale}");
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Restore failed: ' . $e->getMessage());
            return false;
        }

        $this->line('Admin list: /admin/webinars?type=' . ($payload['type'] ?? 'course'));
        $this->line('Front URL: /ar/course/' . ($payload['slug'] ?? ''));

        return true;
    }

    /**
     * @return array{webinar: ?array, translations: array<int, array>}
     */
    private function extractFromDump(string $path, int $id): array
    {
        $this->comment("Extracting #{$id} from dump…");

        $fh = fopen($path, 'rb');
        $creates = [];
        $current = null;
        $buf = '';
        $webinar = null;
        $translations = [];

        while (($line = fgets($fh)) !== false) {
            if (preg_match('/^CREATE TABLE `([^`]+)`/', $line, $m)) {
                $current = $m[1];
                $buf = $line;
                continue;
            }
            if ($current !== null) {
                $buf .= $line;
                if (str_contains($line, ';')) {
                    $creates[$current] = $this->parseCreateColumns($buf);
                    $current = null;
                    $buf = '';
                }
                continue;
            }

            if (!preg_match('/^INSERT INTO `(webinars|webinar_translations)`/', $line, $tm)) {
                continue;
            }
            $table = $tm[1];
            if (!preg_match('/VALUES\s*(.*);\s*$/s', rtrim($line), $vm)) {
                continue;
            }
            $cols = $creates[$table] ?? [];
            foreach ($this->splitMysqlTuples($vm[1]) as $tuple) {
                $vals = $this->parseTupleValues($tuple);
                if (!$cols || count($cols) !== count($vals)) {
                    continue;
                }
                $row = array_combine($cols, $vals);
                if ($table === 'webinars' && (int) $row['id'] === $id) {
                    $webinar = $row;
                }
                if ($table === 'webinar_translations' && (int) ($row['webinar_id'] ?? 0) === $id) {
                    $translations[] = $row;
                }
            }
        }
        fclose($fh);

        return ['webinar' => $webinar, 'translations' => $translations];
    }

    private function filterColumns(string $table, array $row): array
    {
        if (!Schema::hasTable($table)) {
            return $row;
        }
        $cols = Schema::getColumnListing($table);

        return array_intersect_key($row, array_flip($cols));
    }

    private function splitMysqlTuples(string $blob): array
    {
        $tuples = [];
        $len = strlen($blob);
        $depth = 0;
        $inStr = false;
        $escape = false;
        $start = null;
        for ($i = 0; $i < $len; $i++) {
            $ch = $blob[$i];
            if ($inStr) {
                if ($escape) {
                    $escape = false;
                    continue;
                }
                if ($ch === '\\') {
                    $escape = true;
                    continue;
                }
                if ($ch === "'") {
                    if ($i + 1 < $len && $blob[$i + 1] === "'") {
                        $i++;
                        continue;
                    }
                    $inStr = false;
                }
                continue;
            }
            if ($ch === "'") {
                $inStr = true;
                continue;
            }
            if ($ch === '(') {
                if ($depth === 0) {
                    $start = $i;
                }
                $depth++;
                continue;
            }
            if ($ch === ')') {
                $depth--;
                if ($depth === 0 && $start !== null) {
                    $tuples[] = substr($blob, $start, $i - $start + 1);
                    $start = null;
                }
            }
        }

        return $tuples;
    }

    private function parseTupleValues(string $tuple): array
    {
        $tuple = trim($tuple);
        if (str_starts_with($tuple, '(')) {
            $tuple = substr($tuple, 1, -1);
        }
        $vals = [];
        $len = strlen($tuple);
        $inStr = false;
        $escape = false;
        $buf = '';
        for ($i = 0; $i < $len; $i++) {
            $ch = $tuple[$i];
            if ($inStr) {
                if ($escape) {
                    $buf .= $ch;
                    $escape = false;
                    continue;
                }
                if ($ch === '\\') {
                    $buf .= $ch;
                    $escape = true;
                    continue;
                }
                if ($ch === "'") {
                    if ($i + 1 < $len && $tuple[$i + 1] === "'") {
                        $buf .= "'";
                        $i++;
                        continue;
                    }
                    $inStr = false;
                    continue;
                }
                $buf .= $ch;
                continue;
            }
            if ($ch === "'") {
                $inStr = true;
                continue;
            }
            if ($ch === ',') {
                $vals[] = $this->normalizeSqlValue($buf);
                $buf = '';
                continue;
            }
            $buf .= $ch;
        }
        $vals[] = $this->normalizeSqlValue($buf);

        return $vals;
    }

    private function normalizeSqlValue(string $raw)
    {
        $raw = trim($raw);
        if (strcasecmp($raw, 'NULL') === 0) {
            return null;
        }
        if (is_numeric($raw)) {
            return str_contains($raw, '.') ? (float) $raw : (int) $raw;
        }

        return stripcslashes($raw);
    }

    private function parseCreateColumns(string $createSql): array
    {
        if (!preg_match('/\((.*)\)\s*(ENGINE|DEFAULT|CHARSET|;)/si', $createSql, $m)) {
            return [];
        }
        $cols = [];
        foreach (preg_split('/\n/', $m[1]) as $line) {
            $line = trim($line, " \t\r\n,");
            if ($line === '' || preg_match('/^(PRIMARY|UNIQUE|KEY|CONSTRAINT|FULLTEXT|SPATIAL)/', $line)) {
                continue;
            }
            if (preg_match('/^`([^`]+)`/', $line, $cm)) {
                $cols[] = $cm[1];
            }
        }

        return $cols;
    }
}
