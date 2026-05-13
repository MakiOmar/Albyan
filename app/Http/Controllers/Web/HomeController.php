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
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $homeSections = HomeSection::orderBy('order', 'asc')->get();
        $selectedSectionsName = $homeSections->pluck('name')->toArray();

        $featureWebinars = null;
        if (in_array(HomeSection::$featured_classes, $selectedSectionsName)) {
            $featureWebinars = FeatureWebinar::whereIn('page', ['home', 'home_categories'])
                ->where('status', 'publish')
                ->whereHas('webinar', function ($query) {
                    $query->where('status', Webinar::$active);
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
            //$selectedWebinarIds = $featureWebinars->pluck('id')->toArray();
        }

        if (in_array(HomeSection::$latest_classes, $selectedSectionsName)) {
            $latestWebinars = Webinar::where('status', Webinar::$active)
                ->where('private', false)
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

            //$selectedWebinarIds = array_merge($selectedWebinarIds, $latestWebinars->pluck('id')->toArray());
        }

        if (in_array(HomeSection::$latest_bundles, $selectedSectionsName)) {
            $latestBundles = Bundle::where('status', Webinar::$active)
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

        if (in_array(HomeSection::$upcoming_courses, $selectedSectionsName)) {
            $upcomingCourses = UpcomingCourse::where('status', Webinar::$active)
                ->orderBy('created_at', 'desc')
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    }
                ])
                ->limit(6)
                ->get();
        }

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
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    },
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'sales',
                    'tickets',
                    'feature'
                ])
                ->get();

            //$selectedWebinarIds = array_merge($selectedWebinarIds, $bestSaleWebinars->pluck('id')->toArray());
        }

        if (in_array(HomeSection::$best_rates, $selectedSectionsName)) {
            $bestRateWebinars = Webinar::join('webinar_reviews', 'webinars.id', '=', 'webinar_reviews.webinar_id')
                ->select('webinars.*', 'webinar_reviews.rates', 'webinar_reviews.status', DB::raw('avg(rates) as avg_rates'))
                ->where('webinars.status', 'active')
                ->where('webinars.private', false)
                ->where('webinar_reviews.status', 'active')
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

        // hasDiscountWebinars
        if (in_array(HomeSection::$discount_classes, $selectedSectionsName)) {
            $now = time();
            $webinarIdsHasDiscount = [];

            $tickets = Ticket::where('start_date', '<', $now)
                ->where('end_date', '>', $now)
                ->get();

            foreach ($tickets as $ticket) {
                if ($ticket->isValid()) {
                    $webinarIdsHasDiscount[] = $ticket->webinar_id;
                }
            }

            $specialOffersWebinarIds = SpecialOffer::where('status', 'active')
                ->where('from_date', '<', $now)
                ->where('to_date', '>', $now)
                ->pluck('webinar_id')
                ->toArray();

            $webinarIdsHasDiscount = array_merge($specialOffersWebinarIds, $webinarIdsHasDiscount);
            $hasDiscountWebinars = Webinar::whereIn('id', array_unique($webinarIdsHasDiscount))
                ->where('status', Webinar::$active)
                ->where('private', false)
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    },
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'sales',
                    'tickets',
                    'feature'
                ])
                ->limit(6)
                ->get();
        }
        // .\ hasDiscountWebinars

        if (in_array(HomeSection::$free_classes, $selectedSectionsName)) {
            $freeWebinars = Webinar::where('status', Webinar::$active)
                ->where('private', false)
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

        if (in_array(HomeSection::$store_products, $selectedSectionsName)) {
            $newProducts = Product::where('status', Product::$active)
                ->orderBy('updated_at', 'desc')
                ->with([
                    'creator' => function ($qu) {
                        $qu->select('id', 'full_name', 'avatar');
                    },
                ])
                ->limit(6)
                ->get();
        }

        if (in_array(HomeSection::$trend_categories, $selectedSectionsName)) {
            $trendCategories = TrendCategory::with([
                'category' => function ($query) {
                    $query->withCount([
                        'webinars' => function ($query) {
                            $query->where('status', 'active');
                        }
                    ]);
                }
            ])->orderBy('created_at', 'desc')
                ->get();
        }

        if (in_array(HomeSection::$blog, $selectedSectionsName) && !isLaravelPublicBlogDisabled()) {
            $blog = Blog::where('status', 'publish')
                ->with(['category', 'author' => function ($query) {
                    $query->select('id', 'full_name');
                }])->orderBy('updated_at', 'desc')
                ->withCount('comments')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
        }

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

        if (in_array(HomeSection::$testimonials, $selectedSectionsName)) {
            $data = $this->getGoogleReviews();
            $rating_reviews = [
                'rating' => $data['result']['rating'] ?? 0,
                'reviews' => $data['result']['user_ratings_total'] ?? 0,
            ];
            $testimonials = Testimonial::where('status', 'active')->get();
        }

        if (in_array(HomeSection::$subscribes, $selectedSectionsName)) {
            $subscribes = Subscribe::all();

            $user = auth()->user();
            $installmentPlans = new InstallmentPlans($user);

            foreach ($subscribes as $subscribe) {
                if (getInstallmentsSettings('status') and (empty($user) or $user->enable_installments) and $subscribe->price > 0) {
                    $installments = $installmentPlans->getPlans('subscription_packages', $subscribe->id);

                    $subscribe->has_installment = (!empty($installments) and count($installments));
                }
            }
        }

        if (in_array(HomeSection::$find_instructors, $selectedSectionsName)) {
            $findInstructorSection = getFindInstructorsSettings();
        }

        if (in_array(HomeSection::$reward_program, $selectedSectionsName)) {
            $rewardProgramSection = getRewardProgramSettings();
        }


        if (in_array(HomeSection::$become_instructor, $selectedSectionsName)) {
            $becomeInstructorSection = getBecomeInstructorSectionSettings();
        }


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

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'heroSection' => $heroSection,
            'heroSectionData' => $heroSectionData,
            'homeSections' => $homeSections,
            'featureWebinars' => $featureWebinars,
            'latestWebinars' => $latestWebinars ?? [],
            'latestBundles' => $latestBundles ?? [],
            'upcomingCourses' => $upcomingCourses ?? [],
            'bestSaleWebinars' => $bestSaleWebinars ?? [],
            'hasDiscountWebinars' => $hasDiscountWebinars ?? [],
            'bestRateWebinars' => $bestRateWebinars ?? [],
            'freeWebinars' => $freeWebinars ?? [],
            'newProducts' => $newProducts ?? [],
            'trendCategories' => $trendCategories ?? [],
            'instructors' => $instructors ?? [],
            'testimonials' => $testimonials ?? [],
            'rating_reviews' => $rating_reviews ?? [],
            'subscribes' => $subscribes ?? [],
            'blog' => $blog ?? [],
            'organizations' => $organizations ?? [],
            'advertisingBanners1' => $advertisingBanners->where('position', 'home1'),
            'advertisingBanners2' => $advertisingBanners->where('position', 'home2'),
            'homeDefaultStatistics' => $homeDefaultStatistics,
            'homeCustomStatistics' => $homeCustomStatistics,
            'boxVideoOrImage' => $boxVideoOrImage ?? null,
            'findInstructorSection' => $findInstructorSection ?? null,
            'rewardProgramSection' => $rewardProgramSection ?? null,
            'becomeInstructorSection' => $becomeInstructorSection ?? null,
            'forumSection' => $forumSection ?? null,
            'categorySectionData' => $categorySectionData,
            'siteFaqs' => $siteFaqs,
        ];

        return view(getTemplate() . '.pages.home', $data);
    }
    public function getGoogleReviews() {
        $cacheKey = 'google_reviews';
        $cacheDuration = now()->addDays(3); // Store for 3 days
    
        return Cache::remember($cacheKey, $cacheDuration, function () {
            $apiKey = env('GOOGLE_API_KEY');
            $placeId = env('GOOGLE_PLACE_ID'); // Your actual Place ID
        
            $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&fields=rating,user_ratings_total&key={$apiKey}";
        
            $response = Http::get($url);
            return $response->json();
        });
    }
    private function getHomeDefaultStatistics()
    {
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
    }
}
