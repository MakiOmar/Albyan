<?php

namespace App\Services\Concerns;

trait ProtectsUrlsFromSearchReplace
{
    /** Column name fragments that usually store URLs or media paths. */
    private const URL_COLUMN_FRAGMENTS = [
        'url',
        'link',
        'href',
        'slug',
        'permalink',
        'canonical',
        'path',
        'image',
        'avatar',
        'thumbnail',
        'cover',
        'video',
        'audio',
        'file',
        'attachment',
        'icon',
        'logo',
        'src',
        'poster',
        'banner',
        'photo',
        'picture',
        'media',
        'download',
        'redirect',
    ];

    protected function isUrlRelatedColumnName(string $columnName): bool
    {
        $lower = mb_strtolower($columnName);

        foreach (self::URL_COLUMN_FRAGMENTS as $fragment) {
            if (
                $lower === $fragment
                || str_starts_with($lower, $fragment . '_')
                || str_ends_with($lower, '_' . $fragment)
                || str_contains($lower, '_' . $fragment . '_')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * True when the whole cell value is a URL / media path (not prose containing a URL).
     */
    protected function isUrlOnlyValue(string $value): bool
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return false;
        }

        // Multi-line or space-heavy values are treated as content, not pure URLs.
        if (preg_match('/\s/', $trimmed) && !preg_match('#^(https?://|//|www\.)\S+$#iu', $trimmed)) {
            return false;
        }

        if (preg_match('#^(https?://|//|www\.)#iu', $trimmed)) {
            return true;
        }

        if (preg_match('#^(store/|/store/|uploads/|/uploads/|storage/|/storage/|public/|/public/)#iu', $trimmed)) {
            return true;
        }

        // Bare domain-like values without spaces.
        if (!preg_match('/\s/', $trimmed) && preg_match('#^[a-z0-9][a-z0-9.-]*\.[a-z]{2,}(/.*)?$#iu', $trimmed)) {
            return true;
        }

        return false;
    }

    /**
     * Mask URLs so search/replace does not touch them inside larger text.
     *
     * @return array{0: string, 1: array<string, string>}
     */
    protected function protectUrls(string $text): array
    {
        $urls = [];

        $patterns = [
            // Absolute / protocol-relative / www URLs
            '#(https?://[^\s<>"\'\)\]]+|//[^\s<>"\'\)\]]+|www\.[^\s<>"\'\)\]]+)#iu',
            // Common HTML attribute URLs
            '#((?:href|src|action|data-src|poster)\s*=\s*)([\'"])([^\'"]+)\2#iu',
            // Common media/store paths
            '#(?<![A-Za-z0-9_])((?:/?store/|/?uploads/|/?storage/|/?public/)[^\s<>"\']+)#iu',
        ];

        $protected = $text;

        foreach ($patterns as $index => $pattern) {
            $protected = preg_replace_callback($pattern, function ($matches) use (&$urls, $index) {
                // Attribute pattern keeps the attribute prefix and quotes.
                if ($index === 1 && isset($matches[3])) {
                    $key = '___URL_PLACEHOLDER_' . count($urls) . '___';
                    $urls[$key] = $matches[3];

                    return $matches[1] . $matches[2] . $key . $matches[2];
                }

                $key = '___URL_PLACEHOLDER_' . count($urls) . '___';
                $urls[$key] = $matches[0];

                return $key;
            }, $protected) ?? $protected;
        }

        return [$protected, $urls];
    }

    protected function restoreUrls(string $text, array $urls): string
    {
        if (empty($urls)) {
            return $text;
        }

        return strtr($text, $urls);
    }

    /**
     * Run search/replace while leaving URL substrings untouched.
     */
    protected function replaceInStringProtectingUrls(
        string $text,
        string $search,
        string $replace,
        bool $caseSensitive,
        bool $wholeWord,
        bool $previewOnly
    ): array {
        if ($search === '' || $this->isUrlOnlyValue($text)) {
            return ['text' => $text, 'count' => 0];
        }

        [$protected, $urls] = $this->protectUrls($text);
        $result = $this->replaceInStringRaw($protected, $search, $replace, $caseSensitive, $wholeWord, $previewOnly);

        return [
            'text' => $previewOnly ? $text : $this->restoreUrls($result['text'], $urls),
            'count' => $result['count'],
        ];
    }

    protected function replaceInStringRaw(
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
}
