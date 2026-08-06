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
     * Translated slug for a locale (falls back to parent slug).
     */
    public function localizedSlug(?string $locale = null): string
    {
        $locale = mb_strtolower($locale ?: app()->getLocale());

        $translated = null;
        try {
            $translated = $this->translate($locale);
        } catch (\Throwable $e) {
            $translated = null;
        }

        if (!empty($translated) && !empty($translated->slug)) {
            return (string) $translated->slug;
        }

        // Fall back to any translation slug, then parent column.
        if (method_exists($this, 'translations')) {
            $any = $this->translations->first(function ($row) {
                return !empty($row->slug);
            });
            if ($any) {
                return (string) $any->slug;
            }
        }

        return (string) ($this->getAttributes()['slug'] ?? $this->attributes['slug'] ?? '');
    }

    /**
     * Find model by translation slug for a locale (with parent-slug fallback).
     */
    public static function findByLocalizedSlug(string $slug, ?string $locale = null)
    {
        $locale = mb_strtolower($locale ?: app()->getLocale());

        $model = static::whereTranslation('slug', $slug, $locale)->first();
        if ($model) {
            return $model;
        }

        // Fallback: parent-table slug (pre-migration / default-locale mirror).
        return static::query()->where('slug', $slug)->first();
    }

    /**
     * Query scope: match translated slug for locale, or parent slug fallback.
     */
    public function scopeWhereLocalizedSlug($query, string $slug, ?string $locale = null)
    {
        $locale = mb_strtolower($locale ?: app()->getLocale());
        $table = $this->getTable();

        return $query->where(function ($q) use ($slug, $locale, $table) {
            $q->whereTranslation('slug', $slug, $locale)
                ->orWhere($table . '.slug', $slug);
        });
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
     */
    public function saveLocalizedSlug(string $locale, string $slug): void
    {
        $locale = mb_strtolower($locale);
        $slug = trim($slug);

        $this->translateOrNew($locale)->slug = $slug;
        $this->save();

        $default = function_exists('getDefaultLocaleCode') ? getDefaultLocaleCode() : 'ar';
        if ($locale === $default) {
            DB::table($this->getTable())->where('id', $this->id)->update(['slug' => $slug]);
        }
    }
}
