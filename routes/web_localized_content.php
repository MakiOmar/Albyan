<?php

/**
 * Public front content routes that must live under /{locale}/...
 * Included from routes/web.php inside the locale-prefixed group.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebinarCertificateController;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

Route::get('/', 'HomeController@index');

Route::group(['prefix' => 'course'], function () {
    Route::get('/{slug}', 'WebinarController@course');
    Route::get('/{slug}/file/{file_id}/download', 'WebinarController@downloadFile');
    Route::get('/{slug}/file/{file_id}/showHtml', 'WebinarController@showHtmlFile');
    Route::get('/{slug}/lessons/{lesson_id}/read', 'WebinarController@getLesson');
    Route::get('/{slug}/file/{file_id}/play', 'WebinarController@playFile');
    Route::get('/{slug}/free', 'WebinarController@free');
    Route::get('/{slug}/points/apply', 'WebinarController@buyWithPoint');

    Route::group(['middleware' => 'web.auth'], function () {
        Route::get('/{slug}/installments', 'WebinarController@getInstallmentsByCourse');
        Route::get('/learning/{slug}', 'LearningPageController@index');
        Route::get('/learning/{slug}/noticeboards', 'LearningPageController@noticeboards');

        Route::group(['prefix' => '/learning/{slug}/forum'], function () {
            Route::get('/', 'LearningPageController@forum');
            Route::get('/{forumId}/edit', 'LearningPageController@getForumForEdit');
            Route::get('/{forumId}/downloadAttach', 'LearningPageController@forumDownloadAttach');

            Route::group(['prefix' => '/{forumId}/answers'], function () {
                Route::get('/', 'LearningPageController@getForumAnswers');
                Route::get('/{answerId}/edit', 'LearningPageController@answerEdit');
            });
        });
    });
});

Route::group(['prefix' => 'users'], function () {
    Route::get('/{id}/profile', 'UserController@profile');
});

Route::group(['prefix' => 'search'], function () {
    Route::get('/', 'SearchController@index');
});

Route::group(['prefix' => 'tags'], function () {
    Route::get('/{type}/{tag}', 'TagsController@index');
});

Route::group(['prefix' => 'categories'], function () {
    Route::get('/', 'CategoriesController@all')->name('categories.all');
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

Route::group(['prefix' => 'instructor-finder'], function () {
    Route::get('/', 'InstructorFinderController@index');
    Route::get('/wizard', 'InstructorFinderController@wizard');
});

Route::group(['prefix' => 'products'], function () {
    Route::get('/', 'ProductController@searchLists');
    Route::get('/{slug}', 'ProductController@show');

    Route::group(['middleware' => 'web.auth'], function () {
        Route::get('/{slug}/installments', 'ProductController@getInstallmentsByProduct');
    });
});

Route::get('/reward-products', 'RewardProductsController@index');

Route::group(['prefix' => 'bundles'], function () {
    Route::get('/{slug}', 'BundleController@index');
    Route::get('/{slug}/free', 'BundleController@free');

    Route::group(['middleware' => 'web.auth'], function () {
        Route::get('/{slug}/favorite', 'BundleController@favoriteToggle');
        Route::get('/{slug}/points/apply', 'BundleController@buyWithPoint');
    });
});

Route::group(['prefix' => 'upcoming_courses'], function () {
    Route::get('/', 'UpcomingCoursesController@index');
    Route::get('{slug}', 'UpcomingCoursesController@show');
    Route::get('{slug}/toggleFollow', 'UpcomingCoursesController@toggleFollow');
    Route::get('{slug}/favorite', 'UpcomingCoursesController@favorite');
});

Route::group(['prefix' => 'gift'], function () {
    Route::group(['middleware' => 'web.auth'], function () {
        Route::get('/{item_type}/{item_slug}', 'GiftController@index');
    });
});

Route::get('/certificate_validation', 'CertificateValidationController@index');
Route::get('/about', 'AboutController@index');
Route::get('/our-instructors', 'InstructorsCustomController@index');

Route::get('/Reviews', function () {
    $cacheKey = 'google_reviews';
    $cacheDuration = now()->addDays(3);

    $data = Cache::remember($cacheKey, $cacheDuration, function () {
        $apiKey = env('GOOGLE_API_KEY');
        $placeId = env('GOOGLE_PLACE_ID');
        $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&fields=rating,user_ratings_total&key={$apiKey}";
        $response = Http::get($url);
        return $response->json();
    });
    $rating_reviews = [
        'rating' => $data['result']['rating'] ?? 0,
        'reviews' => $data['result']['user_ratings_total'] ?? 0,
    ];
    $testimonials = Testimonial::where('status', 'active')->get();
    return view('web.default.pages.reviews', compact('testimonials', 'rating_reviews'));
});

Route::get('/landing', 'FormsController@landing');
Route::get('/landing/cyber-security', 'FormsController@cyberSecurityLanding');
Route::get('/landing/business-admin', 'FormsController@businessAdminLanding');
Route::get('/landing/diplomas', 'DiplomaLandingController@show');
Route::get('/forms/{url}', 'FormsController@index');
