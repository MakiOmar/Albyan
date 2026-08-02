<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches featured WordPress blog posts for the LMS homepage section.
 * Uses LEAD_GENERATION_BASE_URL as the WP articles base.
 */
class WpFeaturedBlogService
{
    public const CACHE_KEY = 'home.wp_blog.featured';

    /**
     * Cached featured-blog payload for the homepage section.
     *
     * @return array{
     *     enabled: bool,
     *     title: string,
     *     archive_url: string,
     *     posts: Collection<int, array<string, mixed>>
     * }
     */
    public function getFeatured(): array
    {
        $ttl = (int) config('lead_generation.blog_featured.cache_ttl', 900);
        $count = max(1, min(12, (int) config('lead_generation.blog_featured.count', 3)));
        $cacheKey = self::CACHE_KEY . '.' . $count;

        $payload = Cache::remember($cacheKey, $ttl, function () use ($count) {
            return $this->fetchFromApi($count);
        });

        return [
            'enabled' => (bool) ($payload['enabled'] ?? false),
            'title' => (string) ($payload['title'] ?? ''),
            'archive_url' => (string) ($payload['archive_url'] ?? $this->baseUrl()),
            'posts' => collect($payload['posts'] ?? []),
        ];
    }

    /**
     * Drop cached featured payloads (all count variants used by config).
     */
    public static function clearCache(): void
    {
        $count = max(1, min(12, (int) config('lead_generation.blog_featured.count', 3)));
        Cache::forget(self::CACHE_KEY . '.' . $count);

        // Also clear nearby counts in case admin config changed recently.
        for ($i = 1; $i <= 12; $i++) {
            Cache::forget(self::CACHE_KEY . '.' . $i);
        }
    }

    /**
     * @return array{enabled: bool, title: string, archive_url: string, posts: array<int, array<string, mixed>>}
     */
    private function fetchFromApi(int $count): array
    {
        $empty = [
            'enabled' => false,
            'title' => '',
            'archive_url' => $this->baseUrl(),
            'posts' => [],
        ];

        $baseUrl = $this->baseUrl();
        if ($baseUrl === '') {
            return $empty;
        }

        $path = ltrim((string) config('lead_generation.blog_featured.path', 'wp-json/zskeleton/v1/blog/featured'), '/');
        $url = $baseUrl . '/' . $path;
        $origin = $this->requestOrigin();
        $timeout = (int) config('lead_generation.blog_featured.timeout', 8);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders(array_filter([
                    'Origin' => $origin,
                ]))
                ->get($url, ['count' => $count]);

            if (!$response->successful()) {
                Log::warning('WP featured blog API request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);

                return $empty;
            }

            $data = $response->json();
            if (!is_array($data)) {
                return $empty;
            }

            // Respect API kill-switch; hide section when disabled or empty.
            $enabled = !empty($data['enabled']);
            $posts = $this->normalizePosts(is_array($data['posts'] ?? null) ? $data['posts'] : []);

            return [
                'enabled' => $enabled && $posts !== [],
                'title' => trim((string) ($data['title'] ?? '')),
                'archive_url' => $baseUrl,
                'posts' => $posts,
            ];
        } catch (\Throwable $e) {
            Log::warning('WP featured blog API exception', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return $empty;
        }
    }

    /**
     * Sanitize API post objects for Blade (escape happens in the view).
     *
     * @param  array<int, mixed>  $posts
     * @return array<int, array<string, mixed>>
     */
    private function normalizePosts(array $posts): array
    {
        $normalized = [];

        foreach ($posts as $post) {
            if (!is_array($post) || empty($post['permalink']) || empty($post['title'])) {
                continue;
            }

            $thumbnail = is_array($post['thumbnail'] ?? null) ? $post['thumbnail'] : [];
            $category = is_array($post['category'] ?? null) ? $post['category'] : null;

            $normalized[] = [
                'id' => (int) ($post['id'] ?? 0),
                'permalink' => (string) $post['permalink'],
                'title' => (string) $post['title'],
                'excerpt' => (string) ($post['excerpt'] ?? ''),
                'date' => (string) ($post['date'] ?? ''),
                'date_display' => (string) ($post['date_display'] ?? ''),
                'thumbnail_url' => (string) ($thumbnail['url'] ?? ''),
                'thumbnail_alt' => (string) ($thumbnail['alt'] ?? $post['title']),
                'is_placeholder' => !empty($thumbnail['is_placeholder']),
                'category' => $category ? [
                    'name' => (string) ($category['name'] ?? ''),
                    'url' => (string) ($category['url'] ?? ''),
                ] : null,
                'has_access' => array_key_exists('has_access', $post) ? (bool) $post['has_access'] : true,
                'is_locked' => !empty($post['is_locked']),
                'members_only_label' => (string) ($post['members_only_label'] ?? 'Members Only'),
                'show_views' => !empty($post['show_views']),
                'views_display' => (string) ($post['views_display'] ?? '0'),
            ];
        }

        return $normalized;
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('lead_generation.base_url', ''), '/');
    }

    /**
     * Origin header required by the WP API domain allowlist (scheme + host [+ port]).
     */
    private function requestOrigin(): string
    {
        $override = trim((string) config('lead_generation.blog_featured.origin', ''));
        if ($override !== '') {
            return rtrim($override, '/');
        }

        $appOrigin = $this->originFromUrl((string) config('app.url', ''));
        $host = strtolower((string) (parse_url($appOrigin, PHP_URL_HOST) ?? ''));

        // Local APP_URL is not on the WP allowlist; use the articles base host instead.
        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || str_ends_with($host, '.local') || str_ends_with($host, '.test')) {
            $baseOrigin = $this->originFromUrl($this->baseUrl());
            if ($baseOrigin !== '') {
                return $baseOrigin;
            }
        }

        return $appOrigin;
    }

    private function originFromUrl(string $url): string
    {
        $parts = parse_url($url);

        if (empty($parts['scheme']) || empty($parts['host'])) {
            return rtrim($url, '/');
        }

        $origin = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }

        return $origin;
    }
}