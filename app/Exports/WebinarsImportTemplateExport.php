<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WebinarsImportTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'id',
            'slug',
            'locale',
            'title',
            'description',
            'seo_description',
            'type',
            'teacher_id',
            'category_id',
            'price',
            'status',
            'thumbnail',
            'image_cover',
            'duration',
            'capacity',
            'start_date',
            'private',
            'downloadable',
            'support',
            'certificate',
            'forum',
            'subscribe',
            'points',
            'message_for_reviewer',
        ];
    }

    public function array(): array
    {
        return [[
            '',
            'sample-course-slug',
            getDefaultLocale(),
            'Sample Course Title',
            'Sample description',
            'Sample seo description',
            'course',
            '',
            '',
            '49.99',
            'pending',
            '/store/default/thumbnail.jpg',
            '/store/default/cover.jpg',
            '120',
            '0',
            '',
            '0',
            '1',
            '0',
            '0',
            '1',
            '0',
            '0',
            '',
        ]];
    }
}
