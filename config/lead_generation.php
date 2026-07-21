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

];
