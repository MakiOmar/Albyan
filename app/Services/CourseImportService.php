<?php

namespace App\Services;

use App\Models\CourseImport;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CourseImportService
{
    public function processRow(array $row, CourseImport $courseImport): array
    {
        $row = $this->normalizeRow($row);

        try {
            return DB::transaction(function () use ($row) {
                $webinar = null;

                if (!empty($row['id'])) {
                    $webinar = Webinar::query()->find((int)$row['id']);
                }

                if (empty($webinar) and !empty($row['slug'])) {
                    $webinar = Webinar::query()->where('slug', $row['slug'])->first();
                }

                if (!empty($webinar)) {
                    $this->updateWebinar($webinar, $row);
                    return ['result' => 'updated'];
                }

                $newWebinar = $this->createWebinar($row);
                if (!empty($newWebinar)) {
                    return ['result' => 'created'];
                }

                return ['result' => 'failed', 'error' => 'Unknown import error.'];
            });
        } catch (\Throwable $e) {
            return ['result' => 'failed', 'error' => $e->getMessage()];
        }
    }

    private function normalizeRow(array $row): array
    {
        return [
            'id' => $row['id'] ?? null,
            'slug' => isset($row['slug']) ? trim((string)$row['slug']) : null,
            'locale' => mb_strtolower((string)($row['locale'] ?? getDefaultLocale())),
            'title' => $row['title'] ?? null,
            'description' => $row['description'] ?? null,
            'seo_description' => $row['seo_description'] ?? null,
            'type' => $row['type'] ?? null,
            'teacher_id' => $row['teacher_id'] ?? null,
            'category_id' => $row['category_id'] ?? null,
            'price' => $row['price'] ?? null,
            'status' => $row['status'] ?? null,
            'thumbnail' => $row['thumbnail'] ?? null,
            'image_cover' => $row['image_cover'] ?? null,
            'duration' => $row['duration'] ?? null,
            'capacity' => $row['capacity'] ?? null,
            'start_date' => $row['start_date'] ?? null,
            'private' => $row['private'] ?? null,
            'downloadable' => $row['downloadable'] ?? null,
            'support' => $row['support'] ?? null,
            'certificate' => $row['certificate'] ?? null,
            'forum' => $row['forum'] ?? null,
            'subscribe' => $row['subscribe'] ?? null,
            'points' => $row['points'] ?? null,
            'message_for_reviewer' => $row['message_for_reviewer'] ?? null,
        ];
    }

    private function updateWebinar(Webinar $webinar, array $row): void
    {
        $updateData = [];

        foreach ([
                     'slug', 'type', 'teacher_id', 'category_id', 'thumbnail', 'image_cover',
                     'duration', 'capacity', 'status', 'points', 'message_for_reviewer'
                 ] as $field) {
            if ($row[$field] !== null and $row[$field] !== '') {
                $updateData[$field] = $row[$field];
            }
        }

        foreach (['private', 'downloadable', 'support', 'certificate', 'forum', 'subscribe'] as $booleanField) {
            if ($row[$booleanField] !== null and $row[$booleanField] !== '') {
                $updateData[$booleanField] = $this->toBoolean($row[$booleanField]);
            }
        }

        if ($row['price'] !== null and $row['price'] !== '') {
            $updateData['price'] = convertPriceToDefaultCurrency((float)$row['price']);
        }

        if ($row['start_date'] !== null and $row['start_date'] !== '') {
            $updateData['start_date'] = $this->toTimestamp($row['start_date']);
        }

        if (!empty($updateData['teacher_id']) and empty($webinar->creator_id)) {
            $updateData['creator_id'] = $updateData['teacher_id'];
        }

        if (!empty($updateData)) {
            $updateData['updated_at'] = time();
            $webinar->update($updateData);
        }

        if (!empty($row['title']) || !empty($row['description']) || !empty($row['seo_description'])) {
            WebinarTranslation::updateOrCreate([
                'webinar_id' => $webinar->id,
                'locale' => $row['locale'],
            ], [
                'title' => $row['title'] ?? $webinar->title,
                'description' => $row['description'],
                'seo_description' => $row['seo_description'],
            ]);
        }
    }

    private function createWebinar(array $row): Webinar
    {
        $requiredForCreate = ['title', 'teacher_id', 'type', 'slug', 'thumbnail', 'image_cover'];
        foreach ($requiredForCreate as $requiredField) {
            if (empty($row[$requiredField])) {
                throw new \InvalidArgumentException("Missing required field for creating a new course: {$requiredField}");
            }
        }

        $type = in_array($row['type'], [Webinar::$webinar, Webinar::$course, Webinar::$textLesson]) ? $row['type'] : Webinar::$course;
        $status = in_array($row['status'], Webinar::$statuses) ? $row['status'] : Webinar::$pending;

        $createData = [
            'slug' => $row['slug'],
            'type' => $type,
            'teacher_id' => (int)$row['teacher_id'],
            'creator_id' => (int)$row['teacher_id'],
            'thumbnail' => $row['thumbnail'],
            'image_cover' => $row['image_cover'],
            'category_id' => !empty($row['category_id']) ? (int)$row['category_id'] : null,
            'price' => ($row['price'] !== null and $row['price'] !== '') ? convertPriceToDefaultCurrency((float)$row['price']) : null,
            'duration' => !empty($row['duration']) ? (int)$row['duration'] : null,
            'capacity' => !empty($row['capacity']) ? (int)$row['capacity'] : null,
            'status' => $status,
            'private' => $this->toBoolean($row['private']),
            'downloadable' => $this->toBoolean($row['downloadable']),
            'support' => $this->toBoolean($row['support']),
            'certificate' => $this->toBoolean($row['certificate']),
            'forum' => $this->toBoolean($row['forum']),
            'subscribe' => $this->toBoolean($row['subscribe']),
            'points' => !empty($row['points']) ? (int)$row['points'] : null,
            'message_for_reviewer' => Arr::get($row, 'message_for_reviewer'),
            'start_date' => !empty($row['start_date']) ? $this->toTimestamp($row['start_date']) : null,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $webinar = Webinar::query()->create($createData);

        WebinarTranslation::updateOrCreate([
            'webinar_id' => $webinar->id,
            'locale' => $row['locale'],
        ], [
            'title' => $row['title'],
            'description' => $row['description'],
            'seo_description' => $row['seo_description'],
        ]);

        return $webinar;
    }

    private function toBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value === 1;
        }

        $value = mb_strtolower(trim((string)$value));

        return in_array($value, ['1', 'true', 'yes', 'y', 'on']);
    }

    private function toTimestamp($value): int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        return strtotime((string)$value) ?: time();
    }
}
