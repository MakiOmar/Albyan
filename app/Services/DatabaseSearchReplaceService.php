<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSearchReplaceService
{
    public const PREVIEW_LIMIT = 200;

    /** Tables that must never be scanned or mutated. */
    private const EXCLUDED_TABLES = [
        'migrations',
        'failed_jobs',
        'jobs',
        'job_batches',
        'cache',
        'cache_locks',
        'sessions',
        'password_resets',
        'password_reset_tokens',
        'personal_access_tokens',
    ];

    /** Column names that must never be scanned or mutated. */
    private const EXCLUDED_COLUMNS = [
        'password',
        'remember_token',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'secret',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    private const TEXT_TYPE_PREFIXES = [
        'char',
        'varchar',
        'text',
        'tinytext',
        'mediumtext',
        'longtext',
        'json',
        'enum',
    ];

    public function preview(
        string $search,
        string $replace,
        bool $caseSensitive,
        bool $wholeWord
    ): array {
        $matches = [];
        $totalOccurrences = 0;
        $truncated = false;

        foreach ($this->getScannableColumns() as $target) {
            if (count($matches) >= self::PREVIEW_LIMIT) {
                $truncated = true;
                break;
            }

            $remaining = self::PREVIEW_LIMIT - count($matches);
            $columnMatches = $this->findColumnMatches(
                $target['table'],
                $target['column'],
                $target['primary_key'],
                $search,
                $caseSensitive,
                $wholeWord,
                $remaining
            );

            foreach ($columnMatches as $match) {
                $matches[] = $match;
                $totalOccurrences += $match['occurrences'];
            }

            if (count($matches) >= self::PREVIEW_LIMIT) {
                $truncated = true;
            }
        }

        // When truncated, recompute a fuller occurrence total for accuracy on shown rows only.
        return [
            'matches' => $matches,
            'total_occurrences' => $totalOccurrences,
            'affected_records' => count($matches),
            'truncated' => $truncated,
            'preview_limit' => self::PREVIEW_LIMIT,
        ];
    }

    public function apply(
        string $search,
        string $replace,
        bool $caseSensitive = true,
        bool $wholeWord = false
    ): array {
        $updatedRecords = 0;
        $totalOccurrences = 0;

        DB::transaction(function () use (
            $search,
            $replace,
            $caseSensitive,
            $wholeWord,
            &$updatedRecords,
            &$totalOccurrences
        ) {
            foreach ($this->getScannableColumns() as $target) {
                // Fast SQL path for simple case-sensitive substring replace.
                if ($caseSensitive && !$wholeWord) {
                    $result = $this->applySqlReplace($target['table'], $target['column'], $search, $replace);
                    $updatedRecords += $result['updated_records'];
                    $totalOccurrences += $result['total_occurrences'];
                    continue;
                }

                $result = $this->applyPhpReplace(
                    $target['table'],
                    $target['column'],
                    $target['primary_key'],
                    $search,
                    $replace,
                    $caseSensitive,
                    $wholeWord
                );
                $updatedRecords += $result['updated_records'];
                $totalOccurrences += $result['total_occurrences'];
            }
        });

        return [
            'updated_records' => $updatedRecords,
            'total_occurrences' => $totalOccurrences,
        ];
    }

    /**
     * @return array<int, array{table: string, column: string, primary_key: string|null}>
     */
    private function getScannableColumns(): array
    {
        $targets = [];
        $database = DB::getDatabaseName();
        $tablesKey = 'Tables_in_' . $database;
        $tables = DB::select('SHOW TABLES');

        foreach ($tables as $tableRow) {
            $tableName = $tableRow->{$tablesKey};

            if ($this->isExcludedTable($tableName)) {
                continue;
            }

            $primaryKey = $this->resolvePrimaryKey($tableName);
            $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");

            foreach ($columns as $column) {
                $columnName = $column->Field;

                if ($this->isExcludedColumn($columnName)) {
                    continue;
                }

                if (!$this->isTextColumnType((string) $column->Type)) {
                    continue;
                }

                $targets[] = [
                    'table' => $tableName,
                    'column' => $columnName,
                    'primary_key' => $primaryKey,
                ];
            }
        }

        return $targets;
    }

    private function isExcludedTable(string $tableName): bool
    {
        $lower = mb_strtolower($tableName);

        if (in_array($lower, self::EXCLUDED_TABLES, true)) {
            return true;
        }

        // Telescope / debug tooling prefixes.
        if (str_starts_with($lower, 'telescope_')) {
            return true;
        }

        return false;
    }

    private function isExcludedColumn(string $columnName): bool
    {
        return in_array(mb_strtolower($columnName), self::EXCLUDED_COLUMNS, true);
    }

    private function isTextColumnType(string $type): bool
    {
        $type = mb_strtolower($type);

        foreach (self::TEXT_TYPE_PREFIXES as $prefix) {
            if (str_starts_with($type, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function resolvePrimaryKey(string $tableName): ?string
    {
        try {
            $columns = Schema::getColumnListing($tableName);
        } catch (\Throwable $e) {
            return null;
        }

        if (in_array('id', $columns, true)) {
            return 'id';
        }

        try {
            $indexes = DB::select("SHOW INDEX FROM `{$tableName}`");

            foreach ($indexes as $index) {
                if (($index->Key_name ?? '') === 'PRIMARY') {
                    return $index->Column_name;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * @return array<int, array{table: string, column: string, primary_key: mixed, occurrences: int, snippet: string}>
     */
    private function findColumnMatches(
        string $table,
        string $column,
        ?string $primaryKey,
        string $search,
        bool $caseSensitive,
        bool $wholeWord,
        int $limit
    ): array {
        if ($limit <= 0 || $search === '') {
            return [];
        }

        $matches = [];

        try {
            $query = DB::table($table)->whereNotNull($column);

            if ($caseSensitive) {
                $query->whereRaw("`{$column}` LIKE BINARY ?", ['%' . $this->escapeLike($search) . '%']);
            } else {
                $query->where($column, 'like', '%' . $this->escapeLike($search) . '%');
            }

            $select = [$column];
            if ($primaryKey) {
                $select[] = $primaryKey;
            }

            $rows = $query->limit($limit * 3)->get($select);
        } catch (\Throwable $e) {
            return [];
        }

        foreach ($rows as $row) {
            $value = (string) ($row->{$column} ?? '');
            $result = $this->replaceInString($value, $search, '', $caseSensitive, $wholeWord, true);

            if ($result['count'] <= 0) {
                continue;
            }

            $matches[] = [
                'table' => $table,
                'column' => $column,
                'primary_key' => $primaryKey ? ($row->{$primaryKey} ?? null) : null,
                'occurrences' => $result['count'],
                'snippet' => $this->buildSnippet($value, 120),
            ];

            if (count($matches) >= $limit) {
                break;
            }
        }

        return $matches;
    }

    private function applySqlReplace(string $table, string $column, string $search, string $replace): array
    {
        try {
            $countQuery = DB::table($table)
                ->whereNotNull($column)
                ->whereRaw("`{$column}` LIKE BINARY ?", ['%' . $this->escapeLike($search) . '%']);

            $rows = $countQuery->pluck($column);
            $totalOccurrences = 0;

            foreach ($rows as $value) {
                $totalOccurrences += substr_count((string) $value, $search);
            }

            if ($totalOccurrences === 0) {
                return ['updated_records' => 0, 'total_occurrences' => 0];
            }

            $updated = DB::update(
                "UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, ?, ?) WHERE `{$column}` LIKE BINARY ?",
                [$search, $replace, '%' . $this->escapeLike($search) . '%']
            );

            return [
                'updated_records' => (int) $updated,
                'total_occurrences' => $totalOccurrences,
            ];
        } catch (\Throwable $e) {
            return ['updated_records' => 0, 'total_occurrences' => 0];
        }
    }

    private function applyPhpReplace(
        string $table,
        string $column,
        ?string $primaryKey,
        string $search,
        string $replace,
        bool $caseSensitive,
        bool $wholeWord
    ): array {
        $updatedRecords = 0;
        $totalOccurrences = 0;

        try {
            $query = DB::table($table)->whereNotNull($column);

            if ($caseSensitive) {
                $query->whereRaw("`{$column}` LIKE BINARY ?", ['%' . $this->escapeLike($search) . '%']);
            } else {
                $query->where($column, 'like', '%' . $this->escapeLike($search) . '%');
            }

            $select = [$column];
            if ($primaryKey) {
                $select[] = $primaryKey;
            }

            $rows = $query->get($select);
        } catch (\Throwable $e) {
            return ['updated_records' => 0, 'total_occurrences' => 0];
        }

        foreach ($rows as $row) {
            $value = (string) ($row->{$column} ?? '');
            $result = $this->replaceInString($value, $search, $replace, $caseSensitive, $wholeWord, false);

            if ($result['count'] <= 0) {
                continue;
            }

            try {
                if ($primaryKey && isset($row->{$primaryKey})) {
                    DB::table($table)
                        ->where($primaryKey, $row->{$primaryKey})
                        ->update([$column => $result['text']]);
                } else {
                    // Fallback when no primary key: update matching original value only.
                    DB::table($table)
                        ->where($column, $value)
                        ->limit(1)
                        ->update([$column => $result['text']]);
                }

                $updatedRecords++;
                $totalOccurrences += $result['count'];
            } catch (\Throwable $e) {
                continue;
            }
        }

        return [
            'updated_records' => $updatedRecords,
            'total_occurrences' => $totalOccurrences,
        ];
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function replaceInString(
        string $text,
        string $search,
        string $replace,
        bool $caseSensitive,
        bool $wholeWord,
        bool $previewOnly
    ): array {
        if ($search === '') {
            return ['text' => $text, 'count' => 0];
        }

        if ($wholeWord) {
            $pattern = '/\b' . preg_quote($search, '/') . '\b/u';

            if (!$caseSensitive) {
                $pattern .= 'i';
            }

            $count = 0;
            $newText = preg_replace($pattern, $replace, $text, -1, $count);

            if ($previewOnly) {
                return ['text' => $text, 'count' => (int) $count];
            }

            return ['text' => $newText, 'count' => (int) $count];
        }

        if ($caseSensitive) {
            $count = substr_count($text, $search);

            return [
                'text' => $previewOnly ? $text : str_replace($search, $replace, $text),
                'count' => $count,
            ];
        }

        $count = 0;
        $searchLength = mb_strlen($search);
        $offset = 0;
        $result = '';

        while (true) {
            $position = mb_stripos($text, $search, $offset);

            if ($position === false) {
                break;
            }

            $count++;
            $result .= mb_substr($text, $offset, $position - $offset);

            if (!$previewOnly) {
                $result .= $replace;
            } else {
                $result .= mb_substr($text, $position, $searchLength);
            }

            $offset = $position + $searchLength;
        }

        $result .= mb_substr($text, $offset);

        return [
            'text' => $previewOnly ? $text : $result,
            'count' => $count,
        ];
    }

    private function buildSnippet(string $value, int $maxLength): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($value)));

        if ($plain === '') {
            $plain = trim(preg_replace('/\s+/', ' ', $value));
        }

        if (mb_strlen($plain) <= $maxLength) {
            return $plain;
        }

        return mb_substr($plain, 0, $maxLength) . '…';
    }
}
