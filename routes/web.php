<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseGroupController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Testimonial;
use App\Http\Controllers\Web\WebinarCertificateController;
use App\Http\Controllers\SitemapController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!.
|
*/

/*
| Sitemaps are registered first so they always resolve (root URLs, not under /{locale}/).
| If you use route caching, run: php artisan route:clear && php artisan route:cache after deploy.
*/
Route::redirect('/sitemap-index.xml', '/sitemap_index.xml', 301);
Route::get('/sitemap_index.xml', [SitemapController::class, 'sitemapIndexMain'])->name('sitemap.index');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.xml');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap-categories.xml', [SitemapController::class, 'categoriesSitemap'])->name('sitemap.categories');
Route::get('/sitemap-blog-categories.xml', [SitemapController::class, 'blogCategoriesSitemap'])->name('sitemap.blog-categories');
Route::get('/sitemap-instructors.xml', [SitemapController::class, 'instructorsSitemap'])->name('sitemap.instructors');
Route::get('/sitemap-courses.xml', [SitemapController::class, 'courses'])->name('sitemap.courses');
Route::get('/sitemap-blog.xml', [SitemapController::class, 'blog'])->name('sitemap.blog');
Route::get('/sitemap-upcoming-courses.xml', [SitemapController::class, 'upcomingCourses'])->name('sitemap.upcoming-courses');
Route::get('/sitemap-courses-index.xml', [SitemapController::class, 'coursesIndex'])->name('sitemap.courses.index');
Route::get('/sitemap-courses-page-{page}.xml', [SitemapController::class, 'coursesPaginated'])->where('page', '[0-9]+')->name('sitemap.courses.paginated');
Route::get('/sitemap-blog-page-{page}.xml', [SitemapController::class, 'blogPaginated'])->where('page', '[0-9]+')->name('sitemap.blog.paginated');
Route::get('/sitemap-upcoming-courses-page-{page}.xml', [SitemapController::class, 'upcomingCoursesPaginated'])->where('page', '[0-9]+')->name('sitemap.upcoming.paginated');

Route::get('/zoom/{group}', [CourseGroupController::class, 'zoomSession'])->name('course-group.session');

Route::group(['prefix' => 'my_api', 'namespace' => 'Api\Panel', 'middleware' => 'signed', 'as' => 'my_api.web.'], function () {
    Route::get('checkout/{user}', 'CartController@webCheckoutRender')->name('checkout');
    Route::get('/charge/{user}', 'PaymentsController@webChargeRender')->name('charge');
    Route::get('/subscribe/{user}/{subscribe}', 'SubscribesController@webPayRender')->name('subscribe');
    Route::get('/registration_packages/{user}/{package}', 'RegistrationPackagesController@webPayRender')->name('registration_packages');
});

Route::group(['prefix' => 'api_sessions'], function () {
    Route::get('/big_blue_button', ['uses' => 'Api\Panel\SessionController@BigBlueButton'])->name('big_blue_button');
    Route::get('/agora', ['uses' => 'Api\Panel\SessionController@agora'])->name('agora');

});

Route::get('/mobile-app', 'Web\MobileAppController@index')->middleware(['share'])->name('mobileAppRoute');
Route::get('/maintenance', 'Web\MaintenanceController@index')->middleware(['share'])->name('maintenanceRoute');
Route::get('/restriction', 'Web\RestrictionController@index')->middleware(['share'])->name('restrictionRoute');

Route::group(['prefix' => 'cookie-security'], function () {
    Route::post('/all', 'Web\CookieSecurityController@setAll');
    Route::post('/customize', 'Web\CookieSecurityController@setCustomize');
});

/* Emergency Database Update */
// SECURITY FIX: This route has been secured - only accessible in local environment with admin authentication
// RECOMMENDATION: Remove this route entirely in production and use proper deployment scripts instead
Route::get('/emergencyDatabaseUpdate', function () {
    // Only allow in local environment and require admin authentication
    if (!app()->environment('local')) {
        abort(404);
    }
    
    // Require admin authentication
    if (!auth()->check() || !auth()->user()->isAdmin()) {
        abort(403, 'Unauthorized');
    }
    
    // Additional IP restriction (optional but recommended)
    $allowedIPs = ['127.0.0.1', '::1'];
    if (!in_array(request()->ip(), $allowedIPs)) {
        abort(403, 'IP not allowed');
    }
    
    \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--force' => true
    ]);
    $msg1 = \Illuminate\Support\Facades\Artisan::output();

    \Illuminate\Support\Facades\Artisan::call('db:seed', [
        '--force' => true
    ]);
    $msg2 = \Illuminate\Support\Facades\Artisan::output();

    \Illuminate\Support\Facades\Artisan::call('clear:all', [
        '--force' => true
    ]);

    return response()->json([
        'migrations' => $msg1,
        'sections' => $msg2,
    ]);
})->middleware(['auth', 'admin']);

Route::group(['namespace' => 'Auth', 'middleware' => ['check_mobile_app','share', 'check_maintenance', 'check_restriction']], function () {
    Route::get('/login', 'LoginController@showLoginForm');
    Route::post('/login', 'LoginController@login');
    Route::get('/logout', 'LoginController@logout');
    Route::get('/register', 'RegisterController@showRegistrationForm');
    Route::post('/register', 'RegisterController@register');
    Route::post('/register/form-fields', 'RegisterController@getFormFieldsByUserType');
    Route::get('/verification', 'VerificationController@index');
    Route::post('/verification', 'VerificationController@confirmCode');
    Route::get('/verification/resend', 'VerificationController@resendCode');
    Route::get('/forget-password', 'ForgotPasswordController@showLinkRequestForm');
    Route::post('/forget-password', 'ForgotPasswordController@forgot');
    Route::get('reset-password/{token}', 'ResetPasswordController@showResetForm');
    Route::post('/reset-password', 'ResetPasswordController@updatePassword');
    Route::get('/google', 'SocialiteController@redirectToGoogle');
    Route::get('/google/callback', 'SocialiteController@handleGoogleCallback');
    Route::get('/facebook/redirect', 'SocialiteController@redirectToFacebook');
    Route::get('/facebook/callback', 'SocialiteController@handleFacebookCallback');
    Route::get('/reff/{code}', 'ReferralController@referral');
});

Route::group([
    'namespace' => 'Web',
    'middleware' => ['check_mobile_app', 'impersonate', 'share', 'check_maintenance', 'check_restriction'],
], function () {
    Route::get('/stripe', function () {
        return view('web.default.cart.channels.stripe');
    });

    // Debug route for course card styles
    Route::get('/debug-course-card', function () {
        return view('debug-course-card');
    });

    // City Contact Routes
    Route::get('/contact/{citySlug}', 'CityContactController@showForm')->name('city.contact.form');
    Route::post('/contact/{citySlug}/submit', 'CityContactController@submitForm')->name('city.contact.submit');
    Route::get('/api/cities', 'CityContactController@getActiveCities')->name('city.contact.cities');
    Route::get('/api/city-contact/config', 'CityContactController@getConfig')->name('city.contact.config');
    
    // New City Contact Routes
    Route::get('/cities', 'CityContactController@index')->name('city.contact.index');
    Route::get('/city/{slug}', 'CityContactController@show')->name('city.contact.show');

    Route::fallback(function () {
        return view("errors.404", ['pageTitle' => trans('public.error_404_page_title')]);
    });

    // set Locale
    Route::post('/locale', 'LocaleController@setLocale')->name('appLocaleRoute');

    // set Locale
    Route::post('/set-currency', 'SetCurrencyController@setCurrency');

    Route::get('/', 'HomeController@index');

    Route::get('/getDefaultAvatar', 'DefaultAvatarController@make');

    Route::group(['prefix' => 'course'], function () {
        Route::get('/{slug}', 'WebinarController@course');
        Route::get('/{slug}/file/{file_id}/download', 'WebinarController@downloadFile');
        Route::get('/{slug}/file/{file_id}/showHtml', 'WebinarController@showHtmlFile');
        Route::get('/{slug}/lessons/{lesson_id}/read', 'WebinarController@getLesson');
        Route::post('/getFilePath', 'WebinarController@getFilePath');
        Route::get('/{slug}/file/{file_id}/play', 'WebinarController@playFile');
        Route::get('/{slug}/free', 'WebinarController@free');
        Route::get('/{slug}/points/apply', 'WebinarController@buyWithPoint');
        Route::post('/{id}/report', 'WebinarController@reportWebinar');
        Route::post('/{id}/learningStatus', 'WebinarController@learningStatus');

        Route::group(['middleware' => 'web.auth'], function () {
            Route::get('/{slug}/installments', 'WebinarController@getInstallmentsByCourse');

            Route::post('/learning/itemInfo', 'LearningPageController@getItemInfo');
            Route::post('/learning/personalNotes', 'LearningPageController@personalNotes');
            Route::get('/learning/{slug}', 'LearningPageController@index');
            Route::get('/learning/{slug}/noticeboards', 'LearningPageController@noticeboards');
            Route::get('/assignment/{assignmentId}/download/{id}/attach', 'LearningPageController@downloadAssignment');
            Route::post('/assignment/{assignmentId}/history/{historyId}/message', 'AssignmentHistoryController@storeMessage');
            Route::post('/assignment/{assignmentId}/history/{historyId}/setGrade', 'AssignmentHistoryController@setGrade');
            Route::get('/assignment/{assignmentId}/history/{historyId}/message/{messageId}/downloadAttach', 'AssignmentHistoryController@downloadAttach');

            Route::group(['prefix' => '/learning/{slug}/forum'], function () { // LearningPageForumTrait
                Route::get('/', 'LearningPageController@forum');
                Route::post('/store', 'LearningPageController@forumStoreNewQuestion');
                Route::get('/{forumId}/edit', 'LearningPageController@getForumForEdit');
                Route::post('/{forumId}/update', 'LearningPageController@updateForum');
                Route::post('/{forumId}/pinToggle', 'LearningPageController@forumPinToggle');
                Route::get('/{forumId}/downloadAttach', 'LearningPageController@forumDownloadAttach');

                Route::group(['prefix' => '/{forumId}/answers'], function () {
                    Route::get('/', 'LearningPageController@getForumAnswers');
                    Route::post('/', 'LearningPageController@storeForumAnswers');
                    Route::get('/{answerId}/edit', 'LearningPageController@answerEdit');
                    Route::post('/{answerId}/update', 'LearningPageController@answerUpdate');
                    Route::post('/{answerId}/{togglePinOrResolved}', 'LearningPageController@answerTogglePinOrResolved');
                });
            });

            Route::post('/direct-payment', 'WebinarController@directPayment');

            Route::group(['prefix' => 'personal-notes'], function () {
                Route::get('/{id}/download-attachment', 'CoursePersonalNotesController@downloadAttachment');
            });
        });
    });

    Route::group(['prefix' => 'certificate_validation'], function () {
        Route::get('/', 'CertificateValidationController@index');
        Route::post('/validate', 'CertificateValidationController@checkValidate');
    });


    Route::group(['prefix' => 'cart'], function () {
        Route::post('/store', 'CartManagerController@store');
        Route::get('/{id}/delete', 'CartManagerController@destroy');
    });

    // Laravel File Manager Routes
    Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
        \UniSharp\LaravelFilemanager\Lfm::routes();
    });

    Route::group(['middleware' => 'web.auth'], function () {

        Route::group(['prefix' => 'reviews'], function () {
            Route::post('/store', 'WebinarReviewController@store');
            Route::post('/store-reply-comment', 'WebinarReviewController@storeReplyComment');
            Route::get('/{id}/delete', 'WebinarReviewController@destroy');
            Route::get('/{id}/delete-comment/{commentId}', 'WebinarReviewController@destroy');
        });

        Route::group(['prefix' => 'favorites'], function () {
            Route::get('{slug}/toggle', 'FavoriteController@toggle');
            Route::post('/{id}/update', 'FavoriteController@update');
            Route::get('/{id}/delete', 'FavoriteController@destroy');
        });

        Route::group(['prefix' => 'comments'], function () {
            Route::post('/store', 'CommentController@store');
            Route::post('/{id}/reply', 'CommentController@storeReply');
            Route::post('/{id}/update', 'CommentController@update');
            Route::post('/{id}/report', 'CommentController@report');
            Route::get('/{id}/delete', 'CommentController@destroy');
        });

        Route::group(['prefix' => 'cart'], function () {
            Route::get('/', 'CartController@index');

            Route::post('/coupon/validate', 'CartController@couponValidate');
            Route::post('/checkout', 'CartController@checkout')->name('checkout');
        });

        Route::group(['prefix' => 'users'], function () {
            Route::get('/{id}/follow', 'UserController@followToggle');
        });

        Route::group(['prefix' => 'become-instructor'], function () {
            Route::get('/', 'BecomeInstructorController@index')->name('becomeInstructor');
            Route::get('/packages', 'BecomeInstructorController@packages')->name('becomeInstructorPackages');
            Route::get('/packages/{id}/checkHasInstallment', 'BecomeInstructorController@checkPackageHasInstallment');
            Route::get('/packages/{id}/installments', 'BecomeInstructorController@getInstallmentsByRegistrationPackage');
            Route::post('/', 'BecomeInstructorController@store');
            Route::post('/form-fields', 'BecomeInstructorController@getFormFieldsByUserType');
        });

    });

    Route::group(['prefix' => 'meetings'], function () {
        Route::post('/reserve', 'MeetingController@reserve');
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/{id}/profile', 'UserController@profile');
        Route::post('/{id}/availableTimes', 'UserController@availableTimes');
        Route::post('/{id}/send-message', 'UserController@sendMessage');
    });

    Route::group(['prefix' => 'payments'], function () {
        Route::post('/payment-request', 'PaymentController@paymentRequest');
        Route::get('/verify/{gateway}', ['as' => 'payment_verify', 'uses' => 'PaymentController@paymentVerify']);
        Route::post('/verify/{gateway}', ['as' => 'payment_verify_post', 'uses' => 'PaymentController@paymentVerify']);
        Route::get('/status', 'PaymentController@payStatus');
        Route::get('/payku/callback/{id}', 'PaymentController@paykuPaymentVerify')->name('payku.result');
    });

    Route::group(['prefix' => 'subscribes'], function () {
        Route::get('/apply/{webinarSlug}', 'SubscribeController@apply');
        Route::get('/apply/bundle/{bundleSlug}', 'SubscribeController@bundleApply');
    });

    Route::group(['prefix' => 'search'], function () {
        Route::get('/', 'SearchController@index');
    });

    Route::group(['prefix' => 'tags'], function () {
        Route::get('/{type}/{tag}', 'TagsController@index');
    });

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/{categoryTitle}/{subCategoryTitle?}', 'CategoriesController@index');
    });

    Route::get('/classes', 'ClassesController@index');

    Route::get('/reward-courses', 'RewardCoursesController@index');

    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', 'BlogController@index');
        Route::get('/categories/{category}', 'BlogController@index');
        Route::get('/{slug}', 'BlogController@show');
    });

    Route::group(['prefix' => 'contact'], function () {
        Route::get('/', 'ContactController@index');
        Route::post('/store', 'ContactController@store');
    });

    Route::group(['prefix' => 'instructors'], function () {
        Route::get('/', 'UserController@instructors');
    });

    Route::group(['prefix' => 'organizations'], function () {
        Route::get('/', 'UserController@organizations');
    });

    Route::group(['prefix' => 'load_more'], function () {
        Route::get('/{role}', 'UserController@handleInstructorsOrOrganizationsPage');
    });

    Route::group(['prefix' => 'pages'], function () {
        Route::get('/{link}', 'PagesController@index');
    });

    // Captcha
    Route::group(['prefix' => 'captcha'], function () {
        Route::post('create', function () {
            $response = ['status' => 'success', 'captcha_src' => captcha_src('flat')];

            return response()->json($response);
        });
        Route::get('{config?}', '\Mews\Captcha\CaptchaController@getCaptcha');
    });

    Route::post('/newsletters', 'UserController@makeNewsletter');

    Route::group(['prefix' => 'jobs'], function () {
        Route::get('/{methodName}', 'JobsController@index');
        Route::post('/{methodName}', 'JobsController@index');
    });

    Route::group(['prefix' => 'regions'], function () {
        Route::get('/provincesByCountry/{countryId}', 'RegionController@provincesByCountry');
        Route::get('/citiesByProvince/{provinceId}', 'RegionController@citiesByProvince');
        Route::get('/districtsByCity/{cityId}', 'RegionController@districtsByCity');
    });

    Route::group(['prefix' => 'instructor-finder'], function () {
        Route::get('/', 'InstructorFinderController@index');
        Route::get('/wizard', 'InstructorFinderController@wizard');
    });

    Route::group(['prefix' => 'products'], function () {
        Route::get('/', 'ProductController@searchLists');
        Route::get('/{slug}', 'ProductController@show');
        Route::post('/{slug}/points/apply', 'ProductController@buyWithPoint');

        Route::group(['prefix' => 'reviews'], function () {
            Route::post('/store', 'ProductReviewController@store');
            Route::post('/store-reply-comment', 'ProductReviewController@storeReplyComment');
            Route::get('/{id}/delete', 'ProductReviewController@destroy');
            Route::get('/{id}/delete-comment/{commentId}', 'ProductReviewController@destroy');
        });

        Route::group(['middleware' => 'web.auth'], function () {
            Route::get('/{slug}/installments', 'ProductController@getInstallmentsByProduct');
            Route::post('/direct-payment', 'ProductController@directPayment');
        });
    });

    Route::get('/reward-products', 'RewardProductsController@index');

    Route::group(['prefix' => 'bundles'], function () {
        Route::get('/{slug}', 'BundleController@index');
        Route::get('/{slug}/free', 'BundleController@free');

        Route::group(['middleware' => 'web.auth'], function () {
            Route::get('/{slug}/favorite', 'BundleController@favoriteToggle');
            Route::get('/{slug}/points/apply', 'BundleController@buyWithPoint');

            Route::group(['prefix' => 'reviews'], function () {
                Route::post('/store', 'BundleReviewController@store');
                Route::post('/store-reply-comment', 'BundleReviewController@storeReplyComment');
                Route::get('/{id}/delete', 'BundleReviewController@destroy');
                Route::get('/{id}/delete-comment/{commentId}', 'BundleReviewController@destroy');
            });

            Route::post('/direct-payment', 'BundleController@directPayment');
        });
    });

    Route::group(['prefix' => 'forums'], function () {
        Route::get('/', 'ForumController@index');
        Route::get('/create-topic', 'ForumController@createTopic');
        Route::post('/create-topic', 'ForumController@storeTopic');
        Route::get('/search', 'ForumController@search');

        Route::group(['prefix' => '/{slug}/topics'], function () {
            Route::get('/', 'ForumController@topics');
            Route::post('/{topic_slug}/likeToggle', 'ForumController@topicLikeToggle');
            Route::get('/{topic_slug}/edit', 'ForumController@topicEdit');
            Route::post('/{topic_slug}/edit', 'ForumController@topicUpdate');
            Route::post('/{topic_slug}/bookmark', 'ForumController@topicBookmarkToggle');
            Route::get('/{topic_slug}/downloadAttachment/{attachment_id}', 'ForumController@topicDownloadAttachment');

            Route::group(['prefix' => '/{topic_slug}/posts'], function () {
                Route::get('/', 'ForumController@posts');
                Route::post('/', 'ForumController@storePost');
                Route::post('/report', 'ForumController@storeTopicReport');
                Route::get('/{post_id}/edit', 'ForumController@postEdit');
                Route::post('/{post_id}/edit', 'ForumController@postUpdate');
                Route::post('/{post_id}/likeToggle', 'ForumController@postLikeToggle');
                Route::post('/{post_id}/un_pin', 'ForumController@postUnPin');
                Route::post('/{post_id}/pin', 'ForumController@postPin');
                Route::get('/{post_id}/downloadAttachment', 'ForumController@postDownloadAttachment');
            });
        });
    });


    Route::group(['prefix' => 'upcoming_courses'], function () {
        Route::get('/', 'UpcomingCoursesController@index');
        Route::get('{slug}', 'UpcomingCoursesController@show');
        Route::get('{slug}/toggleFollow', 'UpcomingCoursesController@toggleFollow');
        Route::get('{slug}/favorite', 'UpcomingCoursesController@favorite');
        Route::post('{id}/report', 'UpcomingCoursesController@report');
    });

    Route::group(['prefix' => 'installments'], function () {
        Route::group(['middleware' => 'web.auth'], function () {
            Route::get('/request_submitted', 'InstallmentsController@requestSubmitted');
            Route::get('/request_rejected', 'InstallmentsController@requestRejected');
            Route::get('/{id}', 'InstallmentsController@index');
            Route::post('/{id}/store', 'InstallmentsController@store');
        });
    });

    Route::group(['prefix' => 'waitlists'], function () {
        Route::post('/join', 'WaitlistController@store');
    });

    Route::group(['prefix' => 'gift'], function () {
        Route::group(['middleware' => 'web.auth'], function () {
            Route::get('/{item_type}/{item_slug}', 'GiftController@index');
            Route::post('/{item_type}/{item_slug}', 'GiftController@store');
        });
    });

    /* Forms */
    Route::get('/forms/{url}', 'FormsController@index');
    Route::post('/forms/{url}/store', 'FormsController@store');

    /* Landing page (form ID from config LANDING_FORM_ID) */
    Route::get('/landing', 'FormsController@landing');
    Route::post('/landing/store', 'FormsController@landingStore');

    Route::get('/our-instructors', 'InstructorsCustomController@index');
    Route::get('/Reviews', function () {
        $cacheKey = 'google_reviews';
        $cacheDuration = now()->addDays(3); // Store for 3 days
    
        $data = Cache::remember($cacheKey, $cacheDuration, function () {
            $apiKey = env('GOOGLE_API_KEY');
            $placeId = env('GOOGLE_PLACE_ID'); // Your actual Place ID
        
            $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&fields=rating,user_ratings_total&key={$apiKey}";
        
            $response = Http::get($url);
            return $response->json();
        });
        $rating_reviews = [
            'rating' => $data['result']['rating'] ?? 0,
            'reviews' => $data['result']['user_ratings_total'] ?? 0,
        ];
        $testimonials = Testimonial::where('status', 'active')->get();
        return view('web.default.pages.reviews', compact(  'testimonials', 'rating_reviews'));
    });

    Route::get('/about', function () {
        return view('web.default.pages.about');
    });
    Route::post('/certificates/search', [WebinarCertificateController::class, 'search'])->name('certificates.search');

});

// Locale-prefixed SEO routes for content that does NOT have a language-specific slug in the URL.
// Keep courses/blog detail URLs without locale because they already use per-language slugs.
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => '^[A-Za-z]{2}$'],
    'namespace' => 'Web',
    'middleware' => ['check_mobile_app', 'impersonate', 'share', 'check_maintenance', 'check_restriction'],
], function () {
    Route::get('/', 'HomeController@index');

    Route::get('/classes', 'ClassesController@index');
    Route::get('/reward-courses', 'RewardCoursesController@index');

    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', 'BlogController@index');
    });

    Route::group(['prefix' => 'instructors'], function () {
        Route::get('/', 'UserController@instructors');
    });

    Route::group(['prefix' => 'organizations'], function () {
        Route::get('/', 'UserController@organizations');
    });

    Route::group(['prefix' => 'contact'], function () {
        Route::get('/', 'ContactController@index');
    });

    Route::group(['prefix' => 'pages'], function () {
        Route::get('/{link}', 'PagesController@index');
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/{id}/profile', 'UserController@profile');
    });

    Route::get('/about', function () {
        return view('web.default.pages.about');
    });

    // Crawlers sometimes request sitemaps under /{locale}/ — redirect to canonical root URLs
    Route::redirect('sitemap-index.xml', '/sitemap_index.xml', 301);
    Route::redirect('sitemap_index.xml', '/sitemap_index.xml', 301);
    Route::redirect('sitemap.xml', '/sitemap.xml', 301);
    Route::redirect('sitemap-pages.xml', '/sitemap-pages.xml', 301);
    Route::redirect('sitemap-categories.xml', '/sitemap-categories.xml', 301);
    Route::redirect('sitemap-blog-categories.xml', '/sitemap-blog-categories.xml', 301);
    Route::redirect('sitemap-instructors.xml', '/sitemap-instructors.xml', 301);
    Route::redirect('sitemap-courses.xml', '/sitemap-courses.xml', 301);
    Route::redirect('sitemap-blog.xml', '/sitemap-blog.xml', 301);
    Route::redirect('sitemap-upcoming-courses.xml', '/sitemap-upcoming-courses.xml', 301);
    Route::redirect('sitemap-courses-index.xml', '/sitemap-courses-index.xml', 301);
    Route::get('sitemap-courses-page-{page}.xml', function (string $locale, string $page) {
        return redirect('/sitemap-courses-page-' . $page . '.xml', 301);
    })->where('page', '[0-9]+');
    Route::get('sitemap-blog-page-{page}.xml', function (string $locale, string $page) {
        return redirect('/sitemap-blog-page-' . $page . '.xml', 301);
    })->where('page', '[0-9]+');
    Route::get('sitemap-upcoming-courses-page-{page}.xml', function (string $locale, string $page) {
        return redirect('/sitemap-upcoming-courses-page-' . $page . '.xml', 301);
    })->where('page', '[0-9]+');
});

Route::get('tabby/success', [App\Http\Controllers\TabbyController::class, 'success'])->name('tabby.success');
Route::get('tabby/cancel', [App\Http\Controllers\TabbyController::class, 'cancel'])->name('tabby.cancel');
Route::get('tabby/failure', [App\Http\Controllers\TabbyController::class, 'failure'])->name('tabby.failure');

// RSS Feed routes
Route::get('rss/courses', [App\Http\Controllers\RssController::class, 'courses'])->name('rss.courses');
Route::get('rss/blog', [App\Http\Controllers\RssController::class, 'blog'])->name('rss.blog');

