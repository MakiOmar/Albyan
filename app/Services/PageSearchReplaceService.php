<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Translation\PageTranslation;
use Illuminate\Support\Facades\DB;

class PageSearchReplaceService
{
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
                $result = $this->replaceInString($value, $search, $replace, $caseSensitive, $wholeWord, true);

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
                    $result = $this->replaceInString($value, $search, $replace, $caseSensitive, $wholeWord, true);

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

    public function apply(
        string $search,
        string $replace,
        array $fields,
        array $pageIds,
        bool $caseSensitive,
        bool $wholeWord,
        ?string $locale = null
    ): array {
        $fields = $this->normalizeFields($fields);
        $updatedRecords = 0;
        $totalOccurrences = 0;

        DB::transaction(function () use (
            $search,
            $replace,
            $fields,
            $pageIds,
            $caseSensitive,
            $wholeWord,
            $locale,
            &$updatedRecords,
            &$totalOccurrences
        ) {
            foreach ($this->getPages($pageIds) as $page) {
                foreach (self::PAGE_FIELDS as $field) {
                    if (!in_array($field, $fields, true)) {
                        continue;
                    }

                    $value = (string) ($page->{$field} ?? '');
                    $result = $this->replaceInString($value, $search, $replace, $caseSensitive, $wholeWord, false);

                    if ($result['count'] > 0) {
                        $page->{$field} = $result['text'];
                        $page->save();
                        $updatedRecords++;
                        $totalOccurrences += $result['count'];
                    }
                }

                $translations = $page->translations;

                if (!empty($locale)) {
                    $translations = $translations->where('locale', mb_strtolower($locale));
                }

                foreach ($translations as $translation) {
                    $translationDirty = false;

                    foreach (self::TRANSLATED_FIELDS as $field) {
                        if (!in_array($field, $fields, true)) {
                            continue;
                        }

                        $value = (string) ($translation->{$field} ?? '');
                        $result = $this->replaceInString($value, $search, $replace, $caseSensitive, $wholeWord, false);

                        if ($result['count'] > 0) {
                            $translation->{$field} = $result['text'];
                            $translationDirty = true;
                            $totalOccurrences += $result['count'];
                        }
                    }

                    if ($translationDirty) {
                        $translation->save();
                        $updatedRecords++;
                    }
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
