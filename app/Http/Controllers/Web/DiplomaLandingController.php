<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Testimonial;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DiplomaLandingController extends Controller
{
    public function show(Request $request, FormsController $formsController)
    {
        return $formsController->renderLandingPage(
            $request,
            'diploma_landing',
            'web.default.forms.diploma_landing',
            $this->buildPageData()
        );
    }

    public function store(Request $request, FormsController $formsController)
    {
        return $formsController->storeLandingSubmission($request, 'diploma_landing', '/landing/diplomas?tanks=1');
    }

    private function buildPageData(): array
    {
        $webinars = $this->resolveLandingWebinars();

        $siteGeneralSettings = getGeneralSettings();
        $heroSection = (!empty($siteGeneralSettings['hero_section2']) and $siteGeneralSettings['hero_section2'] == "1") ? "2" : "1";

        return [
            'heroSection' => $heroSection,
            'heroSectionData' => getHomeHeroSettings($heroSection),
            'webinars' => $webinars,
            'category' => $this->resolveLandingCategory(),
            'testimonials' => Testimonial::where('status', 'active')->get(),
            'rating_reviews' => $this->getGoogleReviewsSummary(),
            'diplomaLandingWhatsapp' => config('diploma_landing.whatsapp_number'),
            'diplomaLandingCall' => config('diploma_landing.call_number'),
        ];
    }

    private function resolveLandingCategory()
    {
        $categoryId = config('diploma_landing.category_id');
        if (empty($categoryId)) {
            return null;
        }

        return Category::find($categoryId);
    }

    private function resolveLandingWebinars()
    {
        $webinarIdsRaw = config('diploma_landing.webinar_ids');
        $relations = [
            'teacher' => function ($qu) {
                $qu->select('id', 'full_name', 'avatar');
            },
            'reviews' => function ($query) {
                $query->where('status', 'active');
            },
            'tickets',
            'feature',
            'category',
        ];

        if (!empty($webinarIdsRaw)) {
            $ids = array_filter(array_map('intval', explode(',', $webinarIdsRaw)));
            if (empty($ids)) {
                return collect();
            }

            return Webinar::query()
                ->whereIn('id', $ids)
                ->where('status', Webinar::$active)
                ->where('private', false)
                ->with($relations)
                ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
                ->get();
        }

        $categoryId = config('diploma_landing.category_id');
        if (empty($categoryId)) {
            return collect();
        }

        return Webinar::query()
            ->where('category_id', $categoryId)
            ->where('status', Webinar::$active)
            ->where('private', false)
            ->orderBy('updated_at', 'desc')
            ->with($relations)
            ->limit(24)
            ->get();
    }

    private function getGoogleReviewsSummary(): array
    {
        $data = Cache::remember('google_reviews', now()->addDays(3), function () {
            $apiKey = env('GOOGLE_API_KEY');
            $placeId = env('GOOGLE_PLACE_ID');
            if (empty($apiKey) || empty($placeId)) {
                return [];
            }

            $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&fields=rating,user_ratings_total&key={$apiKey}";

            $response = Http::get($url);

            return $response->json();
        });

        return [
            'rating' => $data['result']['rating'] ?? 0,
            'reviews' => $data['result']['user_ratings_total'] ?? 0,
        ];
    }
}
