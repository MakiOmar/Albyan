<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Course Card Image Styles Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file controls the visual style of course card images.
    | You can choose between different styles for the image overlay effects.
    |
    */

    // Default style for course card images
    // Options: 'dark_overlay', 'gray_hover'
    'default_style' => env('COURSE_CARD_STYLE', 'gray_hover'),

    // Available styles configuration
    'styles' => [
        'dark_overlay' => [
            'name' => 'Dark Overlay',
            'description' => 'Dark overlay on images that disappears on hover',
            'class' => 'course-card-dark-overlay',
            'enabled' => true,
        ],
        'white_overlay' => [
            'name' => 'White Overlay',
            'description' => 'White overlay on images that disappears on hover',
            'class' => 'course-card-white-overlay',
            'enabled' => true,
        ],
        'gray_hover' => [
            'name' => 'Gray to Color Hover',
            'description' => 'Gray images that become colored on hover',
            'class' => 'course-card-gray-hover',
            'enabled' => true,
        ],
    ],

    // Style-specific settings
    'settings' => [
        'dark_overlay' => [
            'overlay_color' => 'rgba(0, 0, 0, 0.3)',
            'overlay_opacity' => 1,
            'hover_opacity' => 0,
            'transition_duration' => '0.3s',
        ],
        'white_overlay' => [
            'overlay_color' => 'rgba(255, 255, 255, 0.3)',
            'overlay_opacity' => 1,
            'hover_opacity' => 0,
            'transition_duration' => '0.3s',
        ],
        'gray_hover' => [
            'gray_filter' => 'grayscale(100%)',
            'hover_filter' => 'grayscale(0%)',
            'transition_duration' => '0.3s',
            'brightness' => 0.8,
            'hover_brightness' => 1,
        ],
    ],
];
