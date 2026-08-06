<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\AdvertisingBanner;
use App\Models\Blog;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\FeatureWebinar;
use App\Models\HomePageStatistic;
use App\Models\HomeSection;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SiteFaq;
use App\Models\SpecialOffer;
use App\Models\Subscribe;
use App\Models\Ticket;
use App\Models\TrendCategory;
use App\Models\UpcomingCourse;
use App\Models\Webinar;
use App\Models\Testimonial;
use App\Services\WpFeaturedBlogService;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /** Guest/shared homepage data cache TTL (seconds). */
    public const HOME_CACHE_TTL = 900;

    /** Cache key prefix for locale-scoped homepage payloads. */
    public const HOME_CACHE_PREFIX = 'home.page_data.';

    /**
     * Locales that may have a homepage data cache entry.
     *
     * @return array<int, string>
     */
    public static function homeCacheLocales(): array
    {
        $locales = [];

        try {
            $general = getGeneralSettings();
            if (!empty($general['user_languages']) && is_array($general['user_languages'])) {
                $locales = $general['user_languages'];
            }
            if (!empty($general['site_language'])) {
                $locales[] = $general['site_language'];
            }
        } catch (\Throwable $e) {
            // Settings may be unavailable during early boot/cache clear.
        }

        return array_values(array_unique(array_filter(array_map(function ($locale) {
            return strtolower((string) $locale);
        }, array_merge($locales, [
            app()->getLocale(),
            'en',
            'ar',
        ])))));
    }

    /**
     * Forget cached homepage payloads for site-configured locales.
     */
    public static function clearHomePageCache(): void
    {
        foreach (self::homeCacheLocales() as $locale) {
            Cache::forget(self::HOME_CACHE_PREFIX . $locale);
            // Legacy key from short-lived full-HTML cache (removed — broke CSRF markup).
            Cache::forget('home.html.' . $locale);
        }

        Cache::forget('home.default_statistics');
        WpFeaturedBlogService::clearCache();
    }

    /**
     * Clear then rebuild homepage data cache for one or all locales.
     *
     * @param  array<int, string>|null  $locales  Null = all configured locales
     * @return array<int, string> Warmed cache keys
     */
    public function regenerateHomePageCache(?array $locales = null): array
    {
        $targets = !empty($locales)
            ? array_values(array_unique(array_filter(array_map(static function ($locale) {
                return strtolower(trim((string) $locale));
            }, $locales))))
            : self::homeCacheLocales();

        if (empty($targets)) {
            $targets = [strtolower((string) app()->getLocale())];
        }

        // Drop existing keys first (including stats shared across locales).
        if ($locales === null) {
            self::clearHomePageCache();
        } else {
            foreach ($targets as $locale) {
                Cache::forget(self::HOME_CACHE_PREFIX . $locale);
                Cache::forget('home.html.' . $locale);
            }
            Cache::forget('home.default_statistics');
            WpFeaturedBlogService::clearCache();
        }

        $previousLocale = app()->getLocale();
        $warmed = [];

        try {
            foreach ($targets as $locale) {
                app()->setLocale($locale);
                $key = self::HOME_CACHE_PREFIX . $locale;
                Cache::put($key, $this->buildHomePageData(), self::HOME_CACHE_TTL);
                $warmed[] = $key;
            }
        } finally {
            app()->setLocale($previousLocale);
        }

        return $warmed;
    }

    public function index()
    {
        $locale = app()->getLocale();
        $useCache = getHomepageCacheMode() === 'cached';

        if ($useCache) {
            $cacheKey = self::HOME_CACHE_PREFIX . $locale;
            // Shared section data (no auth-specific installment flags).
            $data = Cache::remember($cacheKey, self::HOME_CACHE_TTL, function () {
                return $this->buildHomePageData();
            });
        } else {
            // Original: always live queries (admin performance setting).
            $data = $this->buildHomePageData();
        }

        // User-specific subscribe installment flags (must not live in shared cache).
        if (!empty($data['subscribes']) && count($data['subscribes'])) {
            $data['subscribes'] = $this->applySubscribeInstallments($data['subscribes']);
        }

        $response = response()->view(getTemplate() . '.pages.home', $data);

        // Never publicly cache HTML — CSRF fields are per-session; public/CDN cache caused token leakage.
        $response->headers->set('Cache-Control', 'private, no-store');
        // Live debugging: inspect Response headers in DevTools Network tab.
        $response->headers->set('X-Perf-Home-Cache', $useCache ? 'cached' : 'original');
        $response->headers->set('X-Perf-Lazy', getImageLazyLoadMode());

        return $response;
    }

    /**
     * Build the homepage view payload (cacheable for guests/shared).
     */
    private function buildHomePageData(): array
    {
        $homeSections = HomeSection::orderBy('order', 'asc')->get();
        $selectedSectionsName = $homeSections->pluck('name')->toArray();

        $featureWebinars = null;
        if (in_array(HomeSection::$featured_classes, $selectedSectionsName)) {
            $featureWebinars = FeatureWebinar::whereIn('page', ['home', 'home_categories'])
                ->where('status', 'publish')
                ->whereHas('webinar', function ($query) {
                    $query->where('status', Webinar::$active)
                        ->forCurrentLocale();
                })
                ->with([
                    'webinar' => function ($query) {
                        $query->with([
                            'teacher' => function ($qu) {
                                $qu->select('id', 'full_name', 'avatar');
                            },
                            'reviews' => function ($query) {
                                $query->where('status', 'active');
                            },
                            'tickets',
                            'feature'
                        ]);
                    }
                ])
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        $latestWebinars = [];
        if (in_array(HomeSection::$latest_classes, $selectedSectionsName)) {
            $latestWebinars = Webinar::where('status', Webinar::$active)
                ->where('private', false)
                ->forCurrentLocale()
                ->orderBy('updated_at', 'desc')
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    },
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'tickets',
                    'feature'
                ])
                ->limit(6)
                ->get();
        }

        $latestBundles = [];
        if (in_array(HomeSection::$latest_bundles, $selectedSectionsName)) {
            $latestBundles = Bundle::where('status', Webinar::$active)
                ->forCurrentLocale()
                ->orderBy('updated_at', 'desc')
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    },
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'tickets',
                ])
                ->limit(6)
                ->get();
        }

        $upcomingCourses = [];
        if (in_array(HomeSection::$upcoming_courses, $selectedSectionsName)) {
            $upcomingCourses = UpcomingCourse::where('status', Webinar::$active)
                ->forCurrentLocale()
                ->orderBy('created_at', 'desc')
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    }
                ])
                ->limit(6)
                ->get();
        }

        $bestSaleWebinars = [];
        if (in_array(HomeSection::$best_sellers, $selectedSectionsName)) {
            $bestSaleWebinarsIds = Sale::whereNotNull('webinar_id')
                ->select(DB::raw('COUNT(id) as cnt,webinar_id'))
                ->groupBy('webinar_id')
                ->orderBy('cnt', 'DESC')
                ->limit(6)
                ->pluck('webinar_id')
                ->toArray();

            $bestSaleWebinars = Webinar::whereIn('id', $bestSaleWebinarsIds)
                ->where('status', Webinar::$active)
                ->where('private', false)
                ->forCurrentLocale()
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    },
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'tickets',
                    'feature'
                ])
                ->get();
        }

        $bestRateWebinars = [];
        if (in_array(HomeSection::$best_rates, $selectedSectionsName)) {
            $bestRateWebinars = Webinar::join('webinar_reviews', 'webinars.id', '=', 'webinar_reviews.webinar_id')
                ->select('webinars.*', 'webinar_reviews.rates', 'webinar_reviews.status', DB::raw('avg(rates) as avg_rates'))
                ->where('webinars.status', 'active')
                ->where('webinars.private', false)
                ->where('webinar_reviews.status', 'active')
                ->forCurrentLocale()
                ->groupBy('teacher_id')
                ->orderBy('avg_rates', 'desc')
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    }
                ])
                ->limit(6)
                ->get();
        }

        $hasDiscountWebinars = [];
        if (in_array(HomeSection::$discount_classes, $selectedSectionsName)) {
            $hasDiscountWebinars = $this->getHasDiscountWebinars();
        }

        $freeWebinars = [];
        if (in_array(HomeSection::$free_classes, $selectedSectionsName)) {
            $freeWebinars = Webinar::where('status', Webinar::$active)
                ->where('private', false)
                ->forCurrentLocale()
                ->where(function ($query) {
                    $query->whereNull('price')
                        ->orWhere('price', '0');
                })
                ->orderBy('updated_at', 'desc')
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    },
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'tickets',
                    'feature'
                ])
                ->limit(6)
                ->get();
        }

        $newProducts = [];
        if (in_array(HomeSection::$store_products, $selectedSectionsName)) {
            $newProducts = Product::where('status', Product::$active)
                ->forCurrentLocale()
                ->orderBy('updated_at', 'desc')
                ->with([
                    'creator' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    },
                ])
                ->limit(6)
                ->get();
        }

        $trendCategories = [];
        if (in_array(HomeSection::$trend_categories, $selectedSectionsName)) {
            $trendCategories = TrendCategory::with([
                'category' => function ($query) {
                    $query->withCount([
                        'webinars' => function ($query) {
                            $query->where('status', 'active')->forCurrentLocale();
                        }
                    ]);
                }
            ])->orderBy('created_at', 'desc')
                ->get();
        }

        $blog = [];
        if (in_array(HomeSection::$blog, $selectedSectionsName) && !isLaravelPublicBlogDisabled()) {
            $blog = Blog::where('status', 'publish')
                ->forCurrentLocale()
                ->with(['category', 'author' => function ($query) {
                    $query->select('id', 'full_name');
                }])->orderBy('updated_at', 'desc')
                ->withCount('comments')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
        }

        $instructors = [];
        if (in_array(HomeSection::$instructors, $selectedSectionsName)) {
            $instructors = User::where('role_name', Role::$teacher)
                ->select('id', 'full_name', 'avatar', 'bio')
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->where('ban', false)
                        ->orWhere(function ($query) {
                            $query->whereNotNull('ban_end_at')
                                ->where('ban_end_at', '<', time());
                        });
                })
                ->limit(8)
                ->get();
        }

        $organizations = [];
        if (in_array(HomeSection::$organizations, $selectedSectionsName)) {
            $organizations = User::where('role_name', Role::$organization)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->where('ban', false)
                        ->orWhere(function ($query) {
                            $query->whereNotNull('ban_end_at')
                                ->where('ban_end_at', '<', time());
                        });
                })
                ->withCount('webinars')
                ->orderBy('webinars_count', 'desc')
                ->limit(6)
                ->get();
        }

        $testimonials = [];
        $rating_reviews = [];
        if (in_array(HomeSection::$testimonials, $selectedSectionsName)) {
            $googleData = $this->getGoogleReviews();
            $rating_reviews = [
                'rating' => $googleData['result']['rating'] ?? 0,
                'reviews' => $googleData['result']['user_ratings_total'] ?? 0,
            ];
            $testimonials = Testimonial::where('status', 'active')->get();
        }

        $subscribes = [];
        if (in_array(HomeSection::$subscribes, $selectedSectionsName)) {
            $subscribes = Subscribe::all();
        }

        $findInstructorSection = null;
        if (in_array(HomeSection::$find_instructors, $selectedSectionsName)) {
            $findInstructorSection = getFindInstructorsSettings();
        }

        $rewardProgramSection = null;
        if (in_array(HomeSection::$reward_program, $selectedSectionsName)) {
            $rewardProgramSection = getRewardProgramSettings();
        }

        $becomeInstructorSection = null;
        if (in_array(HomeSection::$become_instructor, $selectedSectionsName)) {
            $becomeInstructorSection = getBecomeInstructorSectionSettings();
        }

        $forumSection = null;
        if (in_array(HomeSection::$forum_section, $selectedSectionsName)) {
            $forumSection = getForumSectionSettings();
        }

        $categorySectionData = [];
        $categoryCoursesSections = $homeSections->where('name', HomeSection::$category_courses)->filter(function ($section) {
            return !empty($section->category_id);
        });
        foreach ($categoryCoursesSections as $section) {
            $category = Category::find($section->category_id);
            if (!$category) {
                continue;
            }
            $mode = $section->getCategoryCoursesMode();
            $webinarIds = $section->getCategoryCoursesWebinarIds();

            if ($mode === 'specific' && !empty($webinarIds)) {
                $webinars = Webinar::whereIn('id', $webinarIds)
                    ->where('category_id', $section->category_id)
                    ->where('status', Webinar::$active)
                    ->where('private', false)
                    ->forCurrentLocale()
                    ->with([
                        'teacher' => function ($qu) {
                            $qu->select('id', 'full_name', 'avatar');
                        },
                        'reviews' => function ($query) {
                            $query->where('status', 'active');
                        },
                        'tickets',
                        'feature',
                        'category',
                    ])
                    ->orderByRaw('FIELD(id, ' . implode(',', array_map('intval', $webinarIds)) . ')')
                    ->get();
            } else {
                $webinars = Webinar::where('category_id', $section->category_id)
                    ->where('status', Webinar::$active)
                    ->where('private', false)
                    ->forCurrentLocale()
                    ->orderBy('updated_at', 'desc')
                    ->with([
                        'teacher' => function ($qu) {
                            $qu->select('id', 'full_name', 'avatar');
                        },
                        'reviews' => function ($query) {
                            $query->where('status', 'active');
                        },
                        'tickets',
                        'feature',
                        'category',
                    ])
                    ->limit(12)
                    ->get();
            }

            $categorySectionData[$section->id] = [
                'category' => $category,
                'webinars' => $webinars,
            ];
        }

        $siteFaqs = [];
        if (in_array(HomeSection::$faq_section, $selectedSectionsName)) {
            $siteFaqs = SiteFaq::where('status', 'active')->orderBy('order')->get();
        }

        $advertisingBanners = AdvertisingBanner::where('published', true)
            ->whereIn('position', ['home1', 'home2'])
            ->get();

        $siteGeneralSettings = getGeneralSettings();
        $heroSection = (!empty($siteGeneralSettings['hero_section2']) and $siteGeneralSettings['hero_section2'] == "1") ? "2" : "1";
        $heroSectionData = getHomeHeroSettings($heroSection);

        $boxVideoOrImage = null;
        if (in_array(HomeSection::$video_or_image_section, $selectedSectionsName)) {
            $boxVideoOrImage = getHomeVideoOrImageBoxSettings();
        }

        $seoSettings = getSeoMetas('home');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('home.home_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('home.home_title');
        $pageRobot = getPageRobot('home');

        $statisticsSettings = getStatisticsSettings();

        $homeDefaultStatistics = null;
        $homeCustomStatistics = null;

        if (!empty($statisticsSettings['enable_statistics'])) {
            if (!empty($statisticsSettings['display_default_statistics'])) {
                $homeDefaultStatistics = $this->getHomeDefaultStatistics();
            } else {
                $homeCustomStatistics = HomePageStatistic::query()->orderBy('order', 'asc')->limit(4)->get();
            }
        }

        $trainingDomainCategories = collect();
        if (in_array(HomeSection::$training_domains, $selectedSectionsName, true)) {
            $domainsSettings = getHomeContentBlocksSettings('training_domains') ?? [];
            $idsRaw = trim((string) ($domainsSettings['category_ids'] ?? ''));
            $categoryIds = array_values(array_filter(array_map('intval', preg_split('/\s*,\s*/', $idsRaw))));
            if (!empty($categoryIds)) {
                $trainingDomainCategories = Category::query()
                    ->whereIn('id', $categoryIds)
                    ->withCount(['webinars' => function ($query) {
                        $query->where('status', Webinar::$active)
                            ->where('private', false)
                            ->forCurrentLocale();
                    }])
                    ->get()
                    ->sortBy(function ($category) use ($categoryIds) {
                        return array_search($category->id, $categoryIds, true);
                    })
                    ->values();
            }
        }

        // WordPress featured articles (cached separately so live homepage mode still avoids per-request API hits).
        $wpBlogSection = [
            'enabled' => false,
            'title' => '',
            'archive_url' => '',
        ];
        $wpBlogPosts = collect();

        if (in_array(HomeSection::$wp_blog, $selectedSectionsName, true) && isWpBlogSectionVisibleForLocale()) {
            $wpFeatured = app(WpFeaturedBlogService::class)->getFeatured();
            $wpBlogSection = [
                'enabled' => (bool) $wpFeatured['enabled'],
                'title' => (string) $wpFeatured['title'],
                'archive_url' => (string) $wpFeatured['archive_url'],
            ];
            $wpBlogPosts = $wpFeatured['posts'];
        }

        return [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'heroSection' => $heroSection,
            'heroSectionData' => $heroSectionData,
            'homeSections' => $homeSections,
            'featureWebinars' => $featureWebinars,
            'latestWebinars' => $latestWebinars,
            'latestBundles' => $latestBundles,
            'upcomingCourses' => $upcomingCourses,
            'bestSaleWebinars' => $bestSaleWebinars,
            'hasDiscountWebinars' => $hasDiscountWebinars,
            'bestRateWebinars' => $bestRateWebinars,
            'freeWebinars' => $freeWebinars,
            'newProducts' => $newProducts,
            'trendCategories' => $trendCategories,
            'instructors' => $instructors,
            'testimonials' => $testimonials,
            'rating_reviews' => $rating_reviews,
            'subscribes' => $subscribes,
            'blog' => $blog,
            'organizations' => $organizations,
            'advertisingBanners1' => $advertisingBanners->where('position', 'home1'),
            'advertisingBanners2' => $advertisingBanners->where('position', 'home2'),
            'homeDefaultStatistics' => $homeDefaultStatistics,
            'homeCustomStatistics' => $homeCustomStatistics,
            'boxVideoOrImage' => $boxVideoOrImage,
            'findInstructorSection' => $findInstructorSection,
            'rewardProgramSection' => $rewardProgramSection,
            'becomeInstructorSection' => $becomeInstructorSection,
            'forumSection' => $forumSection,
            'categorySectionData' => $categorySectionData,
            'siteFaqs' => $siteFaqs,
            'trainingDomainCategories' => $trainingDomainCategories,
            'wpBlogSection' => $wpBlogSection,
            'wpBlogPosts' => $wpBlogPosts,
        ];
    }

    /**
     * Discount section: single-query ticket IDs (capacity-aware) + active special offers.
     */
    private function getHasDiscountWebinars()
    {
        $now = time();

        // Date window + capacity check in SQL (avoids per-ticket TicketUser::count N+1).
        $ticketWebinarIds = Ticket::query()
            ->where('start_date', '<', $now)
            ->where('end_date', '>', $now)
            ->where(function ($query) {
                $query->whereNull('capacity')
                    ->orWhere('capacity', 0)
                    ->orWhereRaw(
                        '(SELECT COUNT(*) FROM ticket_users WHERE ticket_users.ticket_id = tickets.id) < tickets.capacity'
                    );
            })
            ->pluck('webinar_id')
            ->toArray();

        $specialOffersWebinarIds = SpecialOffer::where('status', 'active')
            ->where('from_date', '<', $now)
            ->where('to_date', '>', $now)
            ->pluck('webinar_id')
            ->toArray();

        $webinarIdsHasDiscount = array_values(array_unique(array_merge($specialOffersWebinarIds, $ticketWebinarIds)));

        if (empty($webinarIdsHasDiscount)) {
            return collect();
        }

        return Webinar::whereIn('id', $webinarIdsHasDiscount)
            ->where('status', Webinar::$active)
            ->where('private', false)
            ->forCurrentLocale()
            ->with([
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name', 'avatar');
                },
                'reviews' => function ($query) {
                    $query->where('status', 'active');
                },
                'tickets',
                'feature'
            ])
            ->limit(6)
            ->get();
    }

    /**
     * Attach installment flags for the current user without storing them in shared cache.
     */
    private function applySubscribeInstallments($subscribes)
    {
        $user = auth()->user();
        $installmentPlans = new InstallmentPlans($user);

        foreach ($subscribes as $subscribe) {
            $subscribe->has_installment = false;
            if (getInstallmentsSettings('status') and (empty($user) or $user->enable_installments) and $subscribe->price > 0) {
                $installments = $installmentPlans->getPlans('subscription_packages', $subscribe->id);
                $subscribe->has_installment = (!empty($installments) and count($installments));
            }
        }

        return $subscribes;
    }

    public function getGoogleReviews()
    {
        $cacheKey = 'google_reviews';
        $cacheDuration = now()->addDays(3);

        return Cache::remember($cacheKey, $cacheDuration, function () {
            $apiKey = env('GOOGLE_API_KEY');
            $placeId = env('GOOGLE_PLACE_ID');

            $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&fields=rating,user_ratings_total&key={$apiKey}";

            $response = Http::get($url);
            return $response->json();
        });
    }

    private function getHomeDefaultStatistics()
    {
        return Cache::remember('home.default_statistics', self::HOME_CACHE_TTL, function () {
            $skillfulTeachersCount = User::where('role_name', Role::$teacher)
                ->where(function ($query) {
                    $query->where('ban', false)
                        ->orWhere(function ($query) {
                            $query->whereNotNull('ban_end_at')
                                ->where('ban_end_at', '<', time());
                        });
                })
                ->where('status', 'active')
                ->count();

            $studentsCount = User::where('role_name', Role::$user)
                ->where(function ($query) {
                    $query->where('ban', false)
                        ->orWhere(function ($query) {
                            $query->whereNotNull('ban_end_at')
                                ->where('ban_end_at', '<', time());
                        });
                })
                ->where('status', 'active')
                ->count();

            $liveClassCount = Webinar::where('type', 'webinar')
                ->where('status', 'active')
                ->count();

            $offlineCourseCount = Webinar::where('status', 'active')
                ->whereIn('type', ['course', 'text_lesson'])
                ->count();

            return [
                'skillfulTeachersCount' => $skillfulTeachersCount,
                'studentsCount' => $studentsCount,
                'liveClassCount' => $liveClassCount,
                'offlineCourseCount' => $offlineCourseCount,
            ];
        });
    }
}
