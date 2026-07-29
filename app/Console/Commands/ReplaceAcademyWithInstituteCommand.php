<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Replace Arabic brand noun "academy" with "institute" (معهد),
 * plus English "Al Bayan Academy" brand variants.
 *
 * Preserves adjectival "academic" phrases like الدراسة الأكاديمية.
 */
class ReplaceAcademyWithInstituteCommand extends Command
{
    protected $signature = 'content:replace-academy-institute
                            {--dry-run : Show what would change without writing}
                            {--files-only : Only update codebase files}
                            {--db-only : Only update database text columns}';

    protected $description = 'Replace Arabic academy brand wording with institute (معهد) in files and/or database';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $filesOnly = (bool) $this->option('files-only');
        $dbOnly = (bool) $this->option('db-only');

        if ($filesOnly && $dbOnly) {
            $this->error('Use only one of --files-only or --db-only.');
            return 1;
        }

        $doFiles = !$dbOnly;
        $doDb = !$filesOnly;

        if ($dry) {
            $this->warn('Dry run — no changes will be written.');
        }

        if ($doFiles) {
            $fileCount = $this->replaceInFiles($dry);
            $this->info("Files updated: {$fileCount}");
        }

        if ($doDb) {
            try {
                $dbCount = $this->replaceInDatabase($dry);
                $this->info("Database column updates attempted: {$dbCount}");
            } catch (\Throwable $e) {
                $this->error('Database replace failed: ' . $e->getMessage());
                return 1;
            }
        }

        $this->line('Done. Clear caches if needed: php artisan cache:clear && php artisan view:clear');
        $this->line('Examples:');
        $this->line('  php artisan content:replace-academy-institute --dry-run');
        $this->line('  php artisan content:replace-academy-institute --files-only');
        $this->line('  php artisan content:replace-academy-institute --db-only');

        return 0;
    }

    /**
     * Build replacement map using Unicode escapes so this command file
     * is not rewritten when it scans itself.
     *
     * @return array{0: array<string,string>, 1: array<string,string>}
     */
    private function maps(): array
    {
        // أ ك ا د ي م ي ة  (with / without hamza on alif)
        $academyHamza = "\u{0623}\u{0643}\u{0627}\u{062F}\u{064A}\u{0645}\u{064A}\u{0629}"; // أكاديمية
        $academyPlain = "\u{0627}\u{0643}\u{0627}\u{062F}\u{064A}\u{0645}\u{064A}\u{0629}"; // اكاديمية
        $institute = "\u{0645}\u{0639}\u{0647}\u{062F}"; // معهد
        $alBayan = "\u{0627}\u{0644}\u{0628}\u{064A}\u{0627}\u{0646}"; // البيان
        $al = "\u{0627}\u{0644}"; // ال

        // Adjectival "academic" (keep); note: الدراسة الأكاديمية = الدراسة + ال + أكاديمية
        $protectMap = [
            "\u{0627}\u{0644}\u{062F}\u{0631}\u{0627}\u{0633}\u{0629} {$al}{$academyHamza}" => '__PROTECT_ACADEMIC_STUDY__',
            "\u{0627}\u{0644}\u{062F}\u{0631}\u{0627}\u{0633}\u{0629} {$al}{$academyPlain}" => '__PROTECT_ACADEMIC_STUDY_NOHAMZA__',
            "\u{0627}\u{0644}\u{062F}\u{0631}\u{0627}\u{0633}\u{0629} {$academyHamza}" => '__PROTECT_ACADEMIC_STUDY_BARE__',
            "\u{0627}\u{0644}\u{062F}\u{0631}\u{0627}\u{0633}\u{0629} {$academyPlain}" => '__PROTECT_ACADEMIC_STUDY_BARE_NOHAMZA__',
            "\u{0645}\u{0647}\u{0646}\u{064A}\u{0629} \u{0648}{$academyHamza}" => '__PROTECT_PROF_ACADEMIC__',
            "\u{0645}\u{0647}\u{0646}\u{064A}\u{0629} \u{0648}{$academyPlain}" => '__PROTECT_PROF_ACADEMIC_NOHAMZA__',
            "\u{0648}{$academyHamza} \u{0648}\u{0641}\u{0642}" => '__PROTECT_AND_ACADEMIC_ACCORDING__',
            "\u{0648}{$academyPlain} \u{0648}\u{0641}\u{0642}" => '__PROTECT_AND_ACADEMIC_ACCORDING_NOHAMZA__',
            "\u{0648}{$academyHamza} \u{062F}\u{0648}\u{0644}\u{064A}\u{0629}" => '__PROTECT_AND_ACADEMIC_INTL__',
            "\u{0648}{$academyPlain} \u{062F}\u{0648}\u{0644}\u{064A}\u{0629}" => '__PROTECT_AND_ACADEMIC_INTL_NOHAMZA__',
            "\u{0627}\u{0644}\u{0628}\u{062D}\u{0631}\u{064A}\u{0629} {$al}{$academyHamza}" => '__PROTECT_NAVAL_ACADEMIC__',
            "\u{0627}\u{0644}\u{0628}\u{062D}\u{0631}\u{064A}\u{0629} {$academyHamza}" => '__PROTECT_NAVAL_ACADEMIC_BARE__',
        ];

        $replacements = [
            'Al-Bayan Academy' => 'Al-Bayan Institute',
            'Al Bayan Academy' => 'Al Bayan Institute',
            'Albyan Academy' => 'Albyan Institute',
            "{$academyHamza} {$alBayan}" => "{$institute} {$alBayan}",
            "{$academyPlain} {$alBayan}" => "{$institute} {$alBayan}",
            "{$al}{$academyHamza}" => "{$al}{$institute}",
            "{$al}{$academyPlain}" => "{$al}{$institute}",
            $academyHamza => $institute,
            $academyPlain => $institute,
        ];

        return [$protectMap, $replacements];
    }

    private function applyReplacements(string $text): string
    {
        [$protectMap, $replacements] = $this->maps();

        foreach ($protectMap as $phrase => $token) {
            $text = str_replace($phrase, $token, $text);
        }

        foreach ($replacements as $search => $replace) {
            $text = str_replace($search, $replace, $text);
        }

        foreach ($protectMap as $phrase => $token) {
            $text = str_replace($token, $phrase, $text);
        }

        return $text;
    }

    private function replaceInFiles(bool $dry): int
    {
        $roots = [
            base_path('lang'),
            base_path('resources'),
            base_path('app'),
        ];

        $extensions = ['php', 'blade.php', 'js', 'json', 'md', 'txt', 'html', 'xml'];
        $updated = 0;
        $selfPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, __FILE__);

        foreach ($roots as $root) {
            if (!is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $path = $file->getPathname();
                $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
                if ($normalized === $selfPath) {
                    continue;
                }
                if ($this->isExcludedPath($normalized)) {
                    continue;
                }

                $name = $file->getFilename();
                $okExt = false;
                foreach ($extensions as $ext) {
                    if (str_ends_with($name, '.' . $ext) || str_ends_with($name, $ext)) {
                        $okExt = true;
                        break;
                    }
                }
                if (!$okExt || str_contains($name, ' - Copy.')) {
                    continue;
                }

                $original = @file_get_contents($path);
                if ($original === false || $original === '') {
                    continue;
                }

                $working = str_replace('academyapp://', '__PROTECT_ACADEMYAPP__', $original);
                $updatedContent = $this->applyReplacements($working);
                $updatedContent = str_replace('__PROTECT_ACADEMYAPP__', 'academyapp://', $updatedContent);

                if ($updatedContent === $original) {
                    continue;
                }

                $rel = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
                $this->line(($dry ? '[dry] ' : '') . $rel);

                if (!$dry) {
                    file_put_contents($path, $updatedContent);
                }
                $updated++;
            }
        }

        return $updated;
    }

    private function isExcludedPath(string $path): bool
    {
        $fragments = [
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'articles' . DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR,
        ];

        foreach ($fragments as $fragment) {
            if (str_contains($path, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private function replaceInDatabase(bool $dry): int
    {
        [$protectMap, $replacements] = $this->maps();
        unset($protectMap);

        $tables = DB::select('SHOW TABLES');
        $database = config('database.connections.' . config('database.default') . '.database');
        $key = 'Tables_in_' . $database;
        $updates = 0;
        $textTypes = ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext', 'json'];

        foreach ($tables as $table) {
            $tableName = $table->$key;
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

                $likeParts = [];
                $bindings = [];
                foreach (array_keys($replacements) as $needle) {
                    $likeParts[] = "`{$columnName}` LIKE ?";
                    $bindings[] = '%' . $needle . '%';
                }

                try {
                    $rows = DB::select(
                        "SELECT `{$columnName}` AS val FROM `{$tableName}` WHERE " . implode(' OR ', $likeParts),
                        $bindings
                    );
                } catch (\Throwable $e) {
                    $this->warn("Skipped {$tableName}.{$columnName}: " . $e->getMessage());
                    continue;
                }

                foreach ($rows as $row) {
                    $original = (string) $row->val;
                    $new = $this->applyReplacements($original);
                    if ($new === $original) {
                        continue;
                    }

                    $this->line(($dry ? '[dry] ' : '') . "{$tableName}.{$columnName}");

                    if (!$dry) {
                        DB::table($tableName)
                            ->where($columnName, $original)
                            ->update([$columnName => $new]);
                    }
                    $updates++;
                }
            }
        }

        return $updates;
    }
}
