<?php

namespace App\Services;

use App\Models\FeatureWebinar;
use App\Models\Faq;
use App\Models\Prerequisite;
use App\Models\Tag;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordpressCourseSyncService
{
    /**
     * Sync a single course to WordPress
     *
     * @param int $webinarId
     * @return array
     */
    public function syncSingleCourse(int $webinarId): array
    {
        $course = Webinar::with(['category', 'tags', 'faqs', 'prerequisites', 'feature'])
            ->find($webinarId);

        if (!$course) {
            return [
                'success' => false,
                'error'   => "Course with ID {$webinarId} not found.",
            ];
        }

        $payload = $this->buildCoursePayload($course);

        $baseUrl = config('services.wordpress_sync.base_url');
        $apiToken = config('services.wordpress_sync.api_token');

        if (!$baseUrl || !$apiToken) {
            return [
                'success' => false,
                'error'   => 'WordPress sync configuration is missing. Please set WORDPRESS_SYNC_BASE_URL and WORDPRESS_SYNC_API_TOKEN in .env',
            ];
        }

        $endpoint = rtrim($baseUrl, '/') . '/wp-json/rocket-lms/v1/course';

        try {
            $response = Http::withHeaders([
                'Accept'            => 'application/json',
                'Content-Type'      => 'application/json',
                'X-RocketLMS-Token' => $apiToken,
            ])->post($endpoint, $payload);

            $status = $response->status();
            $body = $response->json();

            if ($response->successful()) {
                Log::info("Course {$webinarId} synced successfully to WordPress", [
                    'webinar_id' => $webinarId,
                    'response'   => $body,
                ]);

                return [
                    'success' => true,
                    'status'  => $status,
                    'body'    => $body,
                ];
            }

            Log::error("Failed to sync course {$webinarId} to WordPress", [
                'webinar_id' => $webinarId,
                'status'     => $status,
                'response'   => $body,
            ]);

            return [
                'success' => false,
                'status'  => $status,
                'body'    => $body,
                'error'   => $body['message'] ?? 'Unknown error from WordPress',
            ];
        } catch (\Exception $e) {
            Log::error("Exception while syncing course {$webinarId} to WordPress", [
                'webinar_id' => $webinarId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error'   => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build the course payload array
     *
     * @param Webinar $course
     * @return array
     */
    public function buildCoursePayload(Webinar $course): array
    {
        // Get translation data (default locale)
        $translation = $course->translate();

        // Build category data
        $category = null;
        $categorySlug = null;
        if ($course->category) {
            $category = $course->category->title ?? null;
            $categorySlug = $course->category->slug ?? null;
        }

        // Build tags array
        $tags = $course->tags->pluck('title')->toArray();

        // Build FAQs array
        $faqs = $course->faqs->map(function (Faq $faq) {
            return [
                'question' => $faq->title ?? '',
                'answer'   => $faq->answer ?? '',
            ];
        })->toArray();

        // Build prerequisites array (Laravel course IDs - will need mapping on WordPress side)
        $prerequisites = $course->prerequisites->pluck('prerequisite_id')->toArray();

        // Check if featured - FIXED: use null check instead of instanceof
        $isFeatured = !is_null($course->feature);

        // Build full image URLs
        $imageCover = $course->image_cover ? url($course->image_cover) : null;
        $thumbnail = $course->thumbnail ? url($course->thumbnail) : null;
        $videoDemo = $course->video_demo ? url($course->video_demo) : null;

        return [
            // Identification
            'laravel_id' => $course->id,
            'slug'       => $course->slug,
            'type'       => $course->type,
            'status'     => $course->status,

            // Text content
            'title'            => $translation->title ?? '',
            'description'     => $translation->description ?? '',
            'seo_description' => $translation->seo_description ?? '',

            // Media
            'image_cover'       => $imageCover,
            'thumbnail'        => $thumbnail,
            'video_demo'       => $videoDemo,
            'video_demo_source' => $course->video_demo_source,

            // Pricing & capacity
            'price'              => $course->price,
            'organization_price' => $course->organization_price,
            'capacity'           => $course->capacity,
            'sales_count_number' => $course->sales_count_number,

            // Flags
            'support'         => (bool) $course->support,
            'downloadable'    => (bool) $course->downloadable,
            'certificate'     => (bool) $course->certificate,
            'private'         => (bool) $course->private,
            'forum'           => (bool) $course->forum,
            'enable_waitlist' => (bool) $course->enable_waitlist,
            'is_featured'     => $isFeatured,

            // Category
            'category'     => $category,
            'category_slug' => $categorySlug,

            // Tags
            'tags' => $tags,

            // FAQs
            'faqs' => $faqs,

            // Prerequisites (Laravel course IDs)
            'prerequisites' => $prerequisites,

            // Additional metadata
            'duration'   => $course->duration,
            'points'     => $course->points,
            'access_days' => $course->access_days,
            'timezone'  => $course->timezone,
            'start_date' => $course->start_date,

            // Timestamps (stored as Unix timestamps, convert to ISO 8601)
            'created_at' => $course->created_at ? Carbon::createFromTimestamp($course->created_at)->toIso8601String() : null,
            'updated_at' => $course->updated_at ? Carbon::createFromTimestamp($course->updated_at)->toIso8601String() : null,
        ];
    }
}
