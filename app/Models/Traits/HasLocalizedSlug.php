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
     * Translated slug for a locale.
     * Does not fall back to another language's translation slug.
     * Parent slug is used only when $fallbackToParent is true (front / default locale).
     */
    public function localizedSlug(?string $locale = null, bool $fallbackToParent = true): string
    {
        $locale = mb_strtolower($locale ?: app()->getLocale());

        // Fresh DB read avoids stale in-memory translation collections after admin saves.
        $translationTable = app()->make($this->getTranslationModelName())->getTable();
        $foreignKey = $this->getTranslationRelationKey();

        $slug = DB::table($translationTable)
            ->where($foreignKey, $this->getKey())
            ->where('locale', $locale)
            ->value('slug');

        if ($slug === null || $slug === '') {
            // Case-insensitive fallback for legacy uppercase locales (AR / EN).
            $slug = DB::table($translationTable)
                ->where($foreignKey, $this->getKey())
                ->whereRaw('LOWER(locale) = ?', [$locale])
                ->orderByDesc('id')
                ->value('slug');
        }

        if (!empty($slug)) {
            return (string) $slug;
        }

        if (!$fallbackToParent) {
            return '';
        }

        // Parent column = default-locale mirror.
        return (string) ($this->getAttributes()['slug'] ?? $this->attributes['slug'] ?? '');
    }

    /**
     * Whether this model has a translation row for the locale (case-insensitive).
     */
    public function hasLocaleTranslation(string $locale): bool
    {
        $locale = mb_strtolower($locale);
        $translationTable = app()->make($this->getTranslationModelName())->getTable();
        $foreignKey = $this->getTranslationRelationKey();

        return DB::table($translationTable)
            ->where($foreignKey, $this->getKey())
            ->whereRaw('LOWER(locale) = ?', [$locale])
            ->exists();
    }

    /**
     * Upsert translated fields and normalize locale casing (AR → ar).
     * Avoids duplicate rows under MySQL case-insensitive collations.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function saveLocaleTranslation(string $locale, array $attributes): void
    {
        $locale = mb_strtolower($locale);
        $translationTable = app()->make($this->getTranslationModelName())->getTable();
        $foreignKey = $this->getTranslationRelationKey();

        $ids = DB::table($translationTable)
            ->where($foreignKey, $this->getKey())
            ->whereRaw('LOWER(locale) = ?', [$locale])
            ->orderBy('id')
            ->pluck('id');

        $payload = array_merge($attributes, [
            $foreignKey => $this->getKey(),
            'locale' => $locale,
        ]);

        if ($ids->isEmpty()) {
            DB::table($translationTable)->insert($payload);

            return;
        }

        // Keep the oldest row, normalize locale, apply attributes, drop case-duplicates.
        $keepId = (int) $ids->first();
        DB::table($translationTable)->where('id', $keepId)->update($payload);

        $extras = $ids->slice(1)->values()->all();
        if (!empty($extras)) {
            DB::table($translationTable)->whereIn('id', $extras)->delete();
        }
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

        return static::ensureUniqueLocalizedSlug($base, $locale, $exceptModelId);
    }

    /**
     * Ensure a desired slug is unique for the locale (appends -1, -2, … when taken).
     */
    public static function ensureUniqueLocalizedSlug(string $desired, string $locale, ?int $exceptModelId = null): string
    {
        $locale = mb_strtolower($locale);
        $base = trim($desired);
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
     * Matches admin JS auto-fill (title focus-out) for consistency.
     */
    protected static function makeSlugSource(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            return '';
        }

        // Unicode-preserving path first (Arabic titles keep letters).
        $slug = preg_replace('/[\s_]+/u', '-', $title);
        $slug = preg_replace('/[^\p{L}\p{N}\-]+/u', '', (string) $slug);
        $slug = preg_replace('/-+/u', '-', (string) $slug);
        $slug = trim((string) $slug, '-');
        $slug = mb_strtolower($slug);

        if ($slug !== '') {
            return $slug;
        }

        // Fallback for edge cases the unicode path cannot handle.
        if (method_exists(static::class, 'makeSlug')) {
            try {
                $packageSlug = static::makeSlug($title);
                if (!empty($packageSlug)) {
                    return (string) $packageSlug;
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        return (string) Str::slug($title, '-', null);
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
