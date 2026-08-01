<?php

namespace App\Services;

use App\Models\Page;
use App\Services\Concerns\ProtectsUrlsFromSearchReplace;
use Illuminate\Support\Facades\DB;

class PageSearchReplaceService
{
    use ProtectsUrlsFromSearchReplace;

    public const TRANSLATED_FIELDS = [
        'title',
        'seo_description',
        'content',
        'styles',
        'scripts',
        'head_content',
        'footer_content',
    ];

    public const PAGE_FIELDS = [
        'name',
    ];

    public function getFieldOptions(): array
    {
        $options = [];

        foreach (self::PAGE_FIELDS as $field) {
            $options[$field] = trans('admin/main.page_search_replace_field_' . $field);
        }

        foreach (self::TRANSLATED_FIELDS as $field) {
            $options[$field] = trans('admin/main.page_search_replace_field_' . $field);
        }

        return $options;
    }

    public function preview(
        string $search,
        string $replace,
        array $fields,
        array $pageIds,
        bool $caseSensitive,
        bool $wholeWord,
        ?string $locale = null
    ): array {
        $fields = $this->normalizeFields($fields);
        $matches = [];
        $totalOccurrences = 0;

        foreach ($this->getPages($pageIds) as $page) {
            foreach (self::PAGE_FIELDS as $field) {
                if (!in_array($field, $fields, true)) {
                    continue;
                }

                $value = (string) ($page->{$field} ?? '');
                $result = $this->replaceInStringProtectingUrls($value, $search, $replace, $caseSensitive, $wholeWord, true);

                if ($result['count'] > 0) {
                    $matches[] = $this->buildMatchRow($page, null, $field, $result['count'], $value);
                    $totalOccurrences += $result['count'];
                }
            }

            $translations = $page->translations;

            if (!empty($locale)) {
                $translations = $translations->where('locale', mb_strtolower($locale));
            }

            foreach ($translations as $translation) {
                foreach (self::TRANSLATED_FIELDS as $field) {
                    if (!in_array($field, $fields, true)) {
                        continue;
                    }

                    $value = (string) ($translation->{$field} ?? '');
                    $result = $this->replaceInStringProtectingUrls($value, $search, $replace, $caseSensitive, $wholeWord, true);

                    if ($result['count'] > 0) {
                        $matches[] = $this->buildMatchRow($page, $translation->locale, $field, $result['count'], $value);
                        $totalOccurrences += $result['count'];
                    }
                }
            }
        }

        return [
            'matches' => $matches,
            'total_occurrences' => $totalOccurrences,
            'affected_records' => count($matches),
        ];
    }

    /**
     * @param  array<int, array{page_id: int, locale?: string|null, field: string}>  $selections
     */
    public function apply(
        string $search,
        string $replace,
        bool $caseSensitive,
        bool $wholeWord,
        array $selections
    ): array {
        $updatedRecords = 0;
        $totalOccurrences = 0;
        $allowedFields = array_merge(self::PAGE_FIELDS, self::TRANSLATED_FIELDS);

        DB::transaction(function () use (
            $search,
            $replace,
            $caseSensitive,
            $wholeWord,
            $selections,
            $allowedFields,
            &$updatedRecords,
            &$totalOccurrences
        ) {
            $seen = [];

            foreach ($selections as $selection) {
                $pageId = (int) ($selection['page_id'] ?? 0);
                $field = (string) ($selection['field'] ?? '');
                $locale = isset($selection['locale']) && $selection['locale'] !== ''
                    ? mb_strtolower((string) $selection['locale'])
                    : null;

                if ($pageId <= 0 || !in_array($field, $allowedFields, true)) {
                    continue;
                }

                $dedupeKey = $pageId . '|' . ($locale ?? '-') . '|' . $field;

                if (isset($seen[$dedupeKey])) {
                    continue;
                }

                $seen[$dedupeKey] = true;

                $page = Page::with('translations')->find($pageId);

                if (!$page) {
                    continue;
                }

                if (in_array($field, self::PAGE_FIELDS, true)) {
                    $value = (string) ($page->{$field} ?? '');
                    $result = $this->replaceInStringProtectingUrls($value, $search, $replace, $caseSensitive, $wholeWord, false);

                    if ($result['count'] > 0) {
                        $page->{$field} = $result['text'];
                        $page->save();
                        $updatedRecords++;
                        $totalOccurrences += $result['count'];
                    }

                    continue;
                }

                if ($locale === null) {
                    continue;
                }

                $translation = $page->translations->firstWhere('locale', $locale);

                if (!$translation) {
                    continue;
                }

                $value = (string) ($translation->{$field} ?? '');
                $result = $this->replaceInStringProtectingUrls($value, $search, $replace, $caseSensitive, $wholeWord, false);

                if ($result['count'] > 0) {
                    $translation->{$field} = $result['text'];
                    $translation->save();
                    $updatedRecords++;
                    $totalOccurrences += $result['count'];
                }
            }
        });

        return [
            'updated_records' => $updatedRecords,
            'total_occurrences' => $totalOccurrences,
        ];
    }

    private function getPages(array $pageIds)
    {
        $query = Page::query()->with('translations');

        if (!empty($pageIds)) {
            $query->whereIn('id', $pageIds);
        }

        return $query->orderBy('name')->get();
    }

    private function normalizeFields(array $fields): array
    {
        $allowed = array_merge(self::PAGE_FIELDS, self::TRANSLATED_FIELDS);

        return array_values(array_intersect($fields, $allowed));
    }

    private function buildMatchRow(Page $page, ?string $locale, string $field, int $occurrences, string $value): array
    {
        return [
            'page_id' => $page->id,
            'page_name' => $page->name,
            'page_link' => $page->link,
            'locale' => $locale,
            'field' => $field,
            'field_label' => trans('admin/main.page_search_replace_field_' . $field),
            'occurrences' => $occurrences,
            'snippet' => $this->buildSnippet($value, 120),
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
