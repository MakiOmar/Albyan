<?php

/**
 * Schema.org defaults for Al-Byan.
 * Translatable copy lives in settings.schema_settings (Admin → SEO → Schema).
 * Structural / non-locale fields stay here as fallbacks.
 */
return [

    'logo_path' => '/store/1/Logos/albyan-institute-logo-512.png',

    'organization_id_fragment' => '#organization',
    'logo_id_fragment' => '#logo',
    'website_id_fragment' => '#website',
    'webpage_id_fragment' => '#webpage',

    'telephones' => [
        '+97143931889',
        '+971569001020',
    ],

    'whatsapp' => [
        'telephone' => '+971569005544',
        'url' => 'https://wa.me/971569005544',
    ],

    'email' => 'info@albyaninstitute.net',

    'address' => [
        'streetAddress' => 'Business Avenue, M05',
        'addressLocality' => 'Dubai',
        'addressRegion' => 'Dubai',
        'addressCountry' => 'AE',
    ],

    'area_served' => [
        '@type' => 'Country',
        'name' => 'United Arab Emirates',
    ],

    'same_as' => [
        'https://web.khda.gov.ae/ar/Education-Directory/Training/Training-Details?CenterID=504072',
        'https://www.instagram.com/albyaninstitute/',
        'https://www.facebook.com/albyaninstitute1',
        'https://x.com/albyaninstitute',
        'https://www.linkedin.com/company/albyan-institute',
        'https://www.youtube.com/@albyaninstitutee',
        'https://www.tiktok.com/@albyan_institute',
    ],

    /**
     * Locale-keyed default copy (used when dashboard fields are empty).
     */
    'defaults' => [
        'ar' => [
            'legal_name' => 'معهد البيان للخدمات التعليمية ذ.م.م',
            'alternate_names' => "معهد البيان للخدمات التعليمية\nAlbyan Institute",
            'org_description' => 'معهد تدريب في دبي يقدم دورات ودبلومات تدريبية حضورية وأونلاين في الإدارة والقانون والمحاسبة واللغات والتكنولوجيا ومجالات التطوير المهني.',
            'logo_caption' => 'شعار معهد البيان للخدمات التعليمية',
            'place_name' => 'معهد البيان للخدمات التعليمية',
            'admissions_contact_type' => 'Admissions',
            'whatsapp_contact_type' => 'WhatsApp Admissions',
            'home_webpage_name' => 'معهد البيان | معهد تدريب في دبي ودورات مهنية',
            'home_webpage_description' => 'اكتشف دورات ودبلومات معهد البيان في دبي، بخيارات تدريب حضوري وأونلاين في الإدارة والقانون والمحاسبة واللغات والتكنولوجيا.',
            'breadcrumb_home_name' => 'الصفحة الرئيسية',
            'online_instance_name_suffix' => 'أونلاين مباشر',
            'onsite_instance_name_suffix' => 'حضوري في دبي',
            'course_workload_template' => '{hours} ساعة تدريبية',
            'learning_resource_type' => 'Professional Training Course',
        ],
        'en' => [
            'legal_name' => 'Albyan Institute for Educational Services L.L.C',
            'alternate_names' => "Albyan Institute for Educational Services\nمعهد البيان",
            'org_description' => 'A training institute in Dubai offering in-person and online professional courses and diplomas in management, law, accounting, languages, technology, and career development.',
            'logo_caption' => 'Albyan Institute logo',
            'place_name' => 'Albyan Institute for Educational Services',
            'admissions_contact_type' => 'Admissions',
            'whatsapp_contact_type' => 'WhatsApp Admissions',
            'home_webpage_name' => 'Albyan Institute | Training Institute in Dubai & Professional Courses',
            'home_webpage_description' => 'Discover Albyan Institute courses and diplomas in Dubai, with in-person and online training in management, law, accounting, languages, and technology.',
            'breadcrumb_home_name' => 'Home',
            'online_instance_name_suffix' => 'Live Online',
            'onsite_instance_name_suffix' => 'Onsite in Dubai',
            'course_workload_template' => '{hours} training hours',
            'learning_resource_type' => 'Professional Training Course',
        ],
    ],
];
