<?php

namespace App\Services;

use App\Models\FeatureWebinar;
use App\Models\Faq;
use App\Models\Prerequisite;
use App\Models\Tag;
use App\Models\Webinar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordpressCourseSyncService
{
    /**
     * Build the payload for a single course and send it to WordPress.
     *
     * This uses the LearnPress mapping documented in LEARNPRESS_MIGRATION_README.md.
     *
     * @param  int  $webinarId
     * @return array{success: bool, status?: int, body?: mixed, error?: string}
     */
    public function syncSingleCourse(int $webinarId): array
    {
        /** @var Webinar|null $course */
        $course = Webinar::query()
            ->with(['category'])
            ->find($webinarId);

        if (!$course) {
            return [
                'success' => false,
                'error' => "Course with ID {$webinarId} not found.",
            ];
        }

        $payload = $this->buildCoursePayload($course);

        $baseUrl = config('services.wordpress_sync.base_url');
        $token   = config('services.wordpress_sync.api_token');

        if (empty($baseUrl) || empty($token)) {
            return [
                'success' => false,
                'error' => 'WordPress sync is not configured. Please set WORDPRESS_SYNC_BASE_URL and WORDPRESS_SYNC_API_TOKEN in .env.',
            ];
        }

        $endpoint = rtrim($baseUrl, '/') . '/wp-json/rocket-lms/v1/course';

        try {
            $response = Http::withHeaders([
                    'Accept'              => 'application/json',
                    'Content-Type'        => 'application/json',
                    'X-RocketLMS-Token'   => $token,
                ])
                ->timeout(30)
                ->post($endpoint, $payload);

            if ($response->failed()) {
                Log::error('WordPress course sync failed', [
                    'webinar_id' => $webinarId,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);

                return [
                    'success' => false,
                    'status'  => $response->status(),
                    'body'    => $response->json(),
                    'error'   => 'Request to WordPress failed. Check logs for details.',
                ];
            }

            return [
                'success' => true,
                'status'  => $response->status(),
                'body'    => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('WordPress course sync exception', [
                'webinar_id' => $webinarId,
                'exception'  => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Build the payload for a single course, based on the README mapping.
     *
     * @param  \App\Models\Webinar  $course
     * @return array
     */
    public function buildCoursePayload(Webinar $course): array
    {
        // Basic fields & translations (title, description, seo_description)
        $translationLocale = config('app.locale', 'en');

        // Accessors on Webinar + Translatable will resolve the current locale.
        // If you use multiple locales you can extend this later.

        $imageCover = $course->image_cover ? url($course->image_cover) : null;
        $thumbnail  = $course->thumbnail ? url($course->thumbnail) : null;

        // Tags (simple title list)
        $tags = Tag::query()
            ->where('webinar_id', $course->id)
            ->pluck('title')
            ->filter()
            ->values()
            ->all();

        // FAQs (question/answer)
        $faqs = Faq::query()
            ->where('webinar_id', $course->id)
            ->orderBy('id')
            ->get()
            ->map(function (Faq $faq) {
                return [
                    'question' => $faq->title ?? '',
                    'answer'   => $faq->answer ?? '',
                ];
            })
            ->values()
            ->all();

        // Prerequisites (Laravel IDs)
        $prerequisiteIds = Prerequisite::query()
            ->where('webinar_id', $course->id)
            ->pluck('prerequisite_id')
            ->filter()
            ->values()
            ->all();

        // Featured status
        $isFeatured = FeatureWebinar::query()
            ->where('webinar_id', $course->id)
            ->exists();

        return [
            // Identification & original references
            'laravel_id'        => $course->id,
            'slug'              => $course->slug,
            'type'              => $course->type,
            'status'            => $course->status,

            // Text content
            'title'             => $course->title,
            'description'       => $course->description ?? '',
            'seo_description'   => $course->seo_description ?? '',

            // Media
            'image_cover'       => $imageCover,
            'thumbnail'         => $thumbnail,
            'video_demo'        => $course->video_demo,
            'video_demo_source' => $course->video_demo_source,

            // Pricing & capacity
            'price'             => $course->price,
            'organization_price'=> $course->organization_price,
            'capacity'          => $course->capacity,
            'sales_count_number'=> $course->sales_count_number,

            // Access & points
            'points'            => $course->points,
            'access_days'       => $course->access_days,
            'timezone'          => $course->timezone,

            // Booleans / flags
            'support'           => (bool) $course->support,
            'downloadable'      => (bool) $course->downloadable,
            'certificate'       => (bool) $course->certificate,
            'private'           => (bool) $course->private,
            'forum'             => (bool) $course->forum,
            'enable_waitlist'   => (bool) $course->enable_waitlist,
            'is_featured'       => $isFeatured,

            // Category (by title/slug)
            'category'          => optional($course->category)->title,
            'category_slug'     => optional($course->category)->slug ?? null,

            // Tags, FAQs, prerequisites
            'tags'              => $tags,
            'faqs'              => $faqs,
            'prerequisites'     => $prerequisiteIds,

            // Original timestamps
            'created_at'        => $course->created_at,
            'updated_at'        => $course->updated_at,
        ];
    }
}


