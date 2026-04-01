<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WebinarsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $webinars;

    public function __construct($webinars)
    {
        $this->webinars = $webinars;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->webinars;
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function map($webinar): array
    {
        $locale = getDefaultLocale();
        $translation = $webinar->translate($locale);

        return [
            $webinar->id,
            $webinar->slug,
            $locale,
            !empty($translation) ? $translation->title : $webinar->title,
            !empty($translation) ? $translation->description : null,
            !empty($translation) ? $translation->seo_description : null,
            $webinar->type,
            $webinar->teacher_id,
            $webinar->category_id,
            $webinar->price,
            $webinar->status,
            $webinar->thumbnail,
            $webinar->image_cover,
            $webinar->duration,
            $webinar->capacity,
            $webinar->start_date,
            (int)$webinar->private,
            (int)$webinar->downloadable,
            (int)$webinar->support,
            (int)$webinar->certificate,
            (int)$webinar->forum,
            (int)$webinar->subscribe,
            $webinar->points,
            $webinar->message_for_reviewer,
        ];
    }
}
