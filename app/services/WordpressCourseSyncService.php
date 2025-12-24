<?php

namespace App\Services;

use App\Models\FeatureWebinar;
use App\Models\Faq;
use App\Models\Prerequisite;
use App\Models\Tag;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordpressCourseSyncService
{
    /**
     * Sync a single course to WordPress with retry mechanism
     *
     * @param int $webinarId
     * @param int $maxRetries Maximum number of retry attempts (default: 3)
     * @param int $retryDelay Delay in seconds between retries (default: 2)
     * @return array
     */
    public function syncSingleCourse(int $webinarId, int $maxRetries = 3, int $retryDelay = 2): array
    {
        $course = Webinar::with(['category', 'tags', 'faqs', 'prerequisites', 'feature', 'translations'])
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

        $attempt = 0;
        $lastError = null;

        while ($attempt <= $maxRetries) {
            try {
                $response = Http::timeout(60) // Increase timeout to 60 seconds
                    ->withHeaders([
                        'Accept'            => 'application/json',
                        'Content-Type'      => 'application/json',
                        'X-RocketLMS-Token' => $apiToken,
                    ])->post($endpoint, $payload);

                $status = $response->status();
                $body = $response->json();

                if ($response->successful()) {
                    if ($attempt > 0) {
                        Log::info("Course {$webinarId} synced successfully to WordPress after {$attempt} retry(ies)", [
                            'webinar_id' => $webinarId,
                            'attempts'   => $attempt + 1,
                            'response'    => $body,
                        ]);
                    } else {
                        Log::info("Course {$webinarId} synced successfully to WordPress", [
                            'webinar_id' => $webinarId,
                            'response'   => $body,
                        ]);
                    }

                    return [
                        'success' => true,
                        'status'  => $status,
                        'body'    => $body,
                        'attempts' => $attempt + 1,
                    ];
                }

                // For HTTP errors (4xx, 5xx), don't retry - these are not transient
                Log::error("Failed to sync course {$webinarId} to WordPress", [
                    'webinar_id' => $webinarId,
                    'status'     => $status,
                    'response'   => $body,
                    'attempt'    => $attempt + 1,
                ]);

                return [
                    'success' => false,
                    'status'  => $status,
                    'body'    => $body,
                    'error'   => $body['message'] ?? 'Unknown error from WordPress',
                    'attempts' => $attempt + 1,
                ];
            } catch (\Exception $e) {
                $lastError = $e;
                $errorMessage = $e->getMessage();
                
                // Check if this is a timeout or connection error that we should retry
                $isRetryable = $this->isRetryableError($errorMessage);
                
                if (!$isRetryable || $attempt >= $maxRetries) {
                    // Don't retry for non-retryable errors or if we've exhausted retries
                    Log::error("Exception while syncing course {$webinarId} to WordPress", [
                        'webinar_id' => $webinarId,
                        'error'      => $errorMessage,
                        'attempt'    => $attempt + 1,
                        'retryable'  => $isRetryable,
                        'trace'      => $e->getTraceAsString(),
                    ]);

                    return [
                        'success' => false,
                        'error'   => 'Exception: ' . $errorMessage,
                        'attempts' => $attempt + 1,
                        'retryable' => $isRetryable,
                    ];
                }

                // Log retry attempt
                Log::warning("Retrying sync for course {$webinarId} (attempt " . ($attempt + 1) . "/{$maxRetries})", [
                    'webinar_id' => $webinarId,
                    'error'      => $errorMessage,
                    'retry_delay' => $retryDelay,
                ]);

                // Wait before retrying (exponential backoff: 2s, 4s, 8s, etc.)
                sleep($retryDelay * ($attempt + 1));
                $attempt++;
            }
        }

        // Should never reach here, but just in case
        return [
            'success' => false,
            'error'   => 'Exception: ' . ($lastError ? $lastError->getMessage() : 'Unknown error'),
            'attempts' => $attempt + 1,
        ];
    }

    /**
     * Check if an error is retryable (timeout, connection errors, etc.)
     *
     * @param string $errorMessage
     * @return bool
     */
    protected function isRetryableError(string $errorMessage): bool
    {
        $retryablePatterns = [
            'timeout',
            'timed out',
            'connection',
            'network',
            'resolve',
            'could not resolve',
            'failed to connect',
            'operation timed out',
            'curl error 28', // cURL timeout
            'curl error 6',  // cURL couldn't resolve host
            'curl error 7',  // cURL couldn't connect
        ];

        $errorMessageLower = strtolower($errorMessage);

        foreach ($retryablePatterns as $pattern) {
            if (strpos($errorMessageLower, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build the course payload array
     *
     * @param Webinar $course
     * @return array
     */
    public function buildCoursePayload(Webinar $course): array
    {
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

        // Check if featured
        $isFeatured = !is_null($course->feature);

        // Build full image URLs
        $imageCover = $course->image_cover ? url($course->image_cover) : null;
        $thumbnail = $course->thumbnail ? url($course->thumbnail) : null;
        $videoDemo = $course->video_demo ? url($course->video_demo) : null;

        // Use the Webinar model accessors (same way the rest of the app does it)
        // These use getTranslateAttributeValue() which handles locale fallback automatically
        // Set the locale to Arabic before accessing to ensure we get Arabic translation
        $originalLocale = app()->getLocale();
        app()->setLocale('ar');
        
        // Use the model accessors - they handle translation lookup automatically
        $title = trim($course->title ?? '');
        $description = $course->description ?? '';
        $seoDescription = $course->seo_description ?? '';
        
        // Restore original locale
        app()->setLocale($originalLocale);
        
        // If title is still empty, try direct DB query as fallback
        if (empty($title)) {
            $translationData = \DB::table('webinar_translations')
                ->where('webinar_id', $course->id)
                ->where('locale', 'ar')
                ->first();
            
            if ($translationData) {
                $title = trim($translationData->title ?? '');
                $description = $translationData->description ?? '';
                $seoDescription = $translationData->seo_description ?? '';
                
                Log::info("Used direct DB query for course {$course->id} translation", [
                    'webinar_id' => $course->id,
                ]);
            } else {
                // Try any locale as last resort
                $translationData = \DB::table('webinar_translations')
                    ->where('webinar_id', $course->id)
                    ->first();
                
                if ($translationData) {
                    $title = trim($translationData->title ?? '');
                    $description = $translationData->description ?? '';
                    $seoDescription = $translationData->seo_description ?? '';
                }
            }
            
            if (empty($title)) {
                Log::error("Title is empty for course {$course->id} after all methods", [
                    'webinar_id' => $course->id,
                    'db_record_exists' => \DB::table('webinar_translations')
                        ->where('webinar_id', $course->id)
                        ->exists(),
                ]);
            }
        }

        return [
            // Identification
            'laravel_id' => $course->id,
            'slug'       => $course->slug,
            'type'       => $course->type,
            'status'     => $course->status,

            // Text content (using accessor methods that handle translations)
            'title'            => $title,
            'description'     => $description,
            'seo_description' => $seoDescription,

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

