<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Per-locale slug helpers for Astrotomic Translatable models.
 */
trait HasLocalizedSlug
{
    /**
     * Translated slug for a locale (falls back to parent slug only).
     * Does not fall back to another language's translation slug.
     */
    public function localizedSlug(?string $locale = null): string
    {
        $locale = mb_strtolower($locale ?: app()->getLocale());

        // Fresh DB read avoids stale in-memory translation collections after admin saves.
        $translationTable = app()->make($this->getTranslationModelName())->getTable();
        $foreignKey = $this->getTranslationRelationKey();

        $slug = DB::table($translationTable)
            ->where($foreignKey, $this->getKey())
            ->where('locale', $locale)
            ->value('slug');

        if (!empty($slug)) {
            return (string) $slug;
        }

        // Parent column = default-locale mirror.
        return (string) ($this->getAttributes()['slug'] ?? $this->attributes['slug'] ?? '');
    }

    /**
     * Limit query to rows that have a non-empty title translation for the active locale.
     * Used by homepage sections so /en only shows EN-translated content, etc.
     */
    public function scopeForCurrentLocale($query)
    {
        $locale = mb_strtolower((string) app()->getLocale());

        return $query->whereHas('translations', function ($q) use ($locale) {
            $q->where('locale', $locale)
                ->whereNotNull('title')
                ->where('title', '!=', '');
        });
    }

    /**
     * Find model by translation slug for a locale (with any-locale + parent fallbacks).
     */
    public static function findByLocalizedSlug(string $slug, ?string $locale = null)
    {
        $locale = mb_strtolower($locale ?: app()->getLocale());
        $candidates = static::slugLookupCandidates($slug);

        foreach ($candidates as $candidate) {
            $model = static::whereTranslation('slug', $candidate, $locale)->first();
            if ($model) {
                return $model;
            }
        }

        // Slug may belong to another locale (language switcher uses the current URL slug).
        foreach ($candidates as $candidate) {
            $model = static::whereTranslation('slug', $candidate)->first();
            if ($model) {
                return $model;
            }
        }

        foreach ($candidates as $candidate) {
            $model = static::query()->where('slug', $candidate)->first();
            if ($model) {
                return $model;
            }
        }

        return null;
    }

    /**
     * Query scope: match translated slug (any/ decoded variants) or parent slug.
     */
    public function scopeWhereLocalizedSlug($query, string $slug, ?string $locale = null)
    {
        $table = $this->getTable();
        $candidates = static::slugLookupCandidates($slug);

        return $query->where(function ($q) use ($candidates, $table) {
            foreach ($candidates as $candidate) {
                $q->orWhere(function ($inner) use ($candidate, $table) {
                    $inner->whereTranslation('slug', $candidate)
                        ->orWhere($table . '.slug', $candidate);
                });
            }
        });
    }

    /**
     * Decode / normalize URL slug variants for DB lookup.
     *
     * @return array<int, string>
     */
    protected static function slugLookupCandidates(string $slug): array
    {
        $slug = trim($slug);
        $candidates = [$slug];

        $decoded = rawurldecode($slug);
        if ($decoded !== $slug) {
            $candidates[] = $decoded;
        }

        $decoded2 = urldecode($slug);
        if ($decoded2 !== $slug && $decoded2 !== $decoded) {
            $candidates[] = $decoded2;
        }

        return array_values(array_unique(array_filter($candidates, fn ($v) => $v !== '')));
    }

    /**
     * Generate a unique slug for a locale on the translation table.
     */
    public static function makeLocalizedSlug(string $title, string $locale, ?int $exceptModelId = null): string
    {
        $locale = mb_strtolower($locale);
        $base = static::makeSlugSource($title);

        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $i = 1;

        while (static::localizedSlugExists($slug, $locale, $exceptModelId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    /**
     * Whether a translation slug is already used for this locale.
     */
    public static function localizedSlugExists(string $slug, string $locale, ?int $exceptModelId = null): bool
    {
        $locale = mb_strtolower($locale);
        $instance = new static();
        // Use Astrotomic's public API (avoid colliding with its protected getTranslationsTable).
        $translationTable = app()->make($instance->getTranslationModelName())->getTable();
        $foreignKey = $instance->getTranslationRelationKey();

        $query = DB::table($translationTable)
            ->where('locale', $locale)
            ->where('slug', $slug);

        if (!empty($exceptModelId)) {
            $query->where($foreignKey, '!=', $exceptModelId);
        }

        return $query->exists();
    }

    /**
     * Build a URL-safe slug string (keeps Arabic letters).
     */
    protected static function makeSlugSource(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            return '';
        }

        // Prefer package slugger against parent table when available.
        if (method_exists(static::class, 'makeSlug')) {
            try {
                return static::makeSlug($title);
            } catch (\Throwable $e) {
                // fall through
            }
        }

        $slug = Str::slug($title, '-', null);
        if ($slug !== '') {
            return $slug;
        }

        // Arabic / non-ASCII: dash-separate words without stripping letters.
        $slug = preg_replace('/\s+/u', '-', $title);
        $slug = preg_replace('/[^\p{L}\p{N}\-_]+/u', '', $slug);

        return trim((string) $slug, '-');
    }

    /**
     * Persist slug on a translation and mirror default locale onto parent.
     * Uses the query builder so Astrotomic/Sluggable cannot create slug-only translation rows
     * (title and other required columns have no DB defaults).
     */
    public function saveLocalizedSlug(string $locale, string $slug): void
    {
        $locale = mb_strtolower($locale);
        $slug = trim($slug);

        $translationTable = app()->make($this->getTranslationModelName())->getTable();
        $foreignKey = $this->getTranslationRelationKey();

        $existing = DB::table($translationTable)
            ->where($foreignKey, $this->getKey())
            ->where('locale', $locale)
            ->first();

        if ($existing) {
            DB::table($translationTable)
                ->where('id', $existing->id)
                ->update(['slug' => $slug]);
        } else {
            $fallback = DB::table($translationTable)
                ->where($foreignKey, $this->getKey())
                ->orderByRaw('CASE WHEN locale = ? THEN 0 ELSE 1 END', [
                    function_exists('getDefaultLocaleCode') ? getDefaultLocaleCode() : 'ar',
                ])
                ->first();

            $payload = [
                $foreignKey => $this->getKey(),
                'locale' => $locale,
                'slug' => $slug,
                'title' => $fallback->title ?? '',
            ];

            // Copy optional translated columns when present on the table/fallback row.
            foreach (['description', 'seo_description', 'seo_title', 'summary', 'meta_description', 'content'] as $column) {
                if ($fallback && property_exists($fallback, $column)) {
                    $payload[$column] = $fallback->{$column};
                }
            }

            DB::table($translationTable)->insert($payload);
        }

        $default = function_exists('getDefaultLocaleCode') ? getDefaultLocaleCode() : 'ar';
        if ($locale === $default) {
            DB::table($this->getTable())->where('id', $this->id)->update(['slug' => $slug]);
        }
    }
}
