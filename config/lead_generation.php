<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lead generation form base URL
    |--------------------------------------------------------------------------
    |
    | Base URL for training program registration forms (WordPress articles).
    | Locale-specific paths are appended, then ?program={course title}.
    |
    */

    'base_url' => rtrim((string) env('LEAD_GENERATION_BASE_URL', 'https://albyan.institute/articles'), '/'),

    /*
    | CTA / inquire form (replaced add to cart).
    */
    'paths' => [
        'ar' => 'training-program-registration-ar',
        'en' => 'training-program-registration',
    ],

    /*
    | Deep-link form for /course/{slug}?program=apply
    */
    'diploma_paths' => [
        'ar' => 'diploma-application',
        'en' => 'diploma-application',
    ],

    /*
    |--------------------------------------------------------------------------
    | Homepage WordPress featured blog (zskeleton REST API)
    |--------------------------------------------------------------------------
    |
    | GET {base_url}/{path}?count={count}
    | Requires Origin header matching an allowlisted LMS domain.
    |
    */
    'blog_featured' => [
        'path' => 'wp-json/zskeleton/v1/blog/featured',
        'count' => (int) env('WP_BLOG_FEATURED_COUNT', 3),
        'cache_ttl' => (int) env('WP_BLOG_FEATURED_CACHE_TTL', 900),
        'timeout' => (int) env('WP_BLOG_FEATURED_TIMEOUT', 8),
        // Optional Origin override for the WP domain allowlist (defaults to APP_URL; localhost falls back to base_url host).
        'origin' => env('WP_BLOG_API_ORIGIN'),
    ],

];
