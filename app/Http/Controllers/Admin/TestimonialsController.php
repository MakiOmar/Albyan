<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Models\Translation\TestimonialTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TestimonialsController extends Controller
{
    public function index()
    {
        $this->authorize('admin_testimonials_list');

        removeContentLocale();

        $testimonials = Testimonial::query()->paginate(10);

        $data = [
            'pageTitle' => trans('admin/pages/comments.testimonials'),
            'testimonials' => $testimonials
        ];

        return view('admin.testimonials.lists', $data);
    }
    public function fetchGoogleReviews()
    {
        $apiKey = env('GOOGLE_API_KEY');
        $placeId = env('GOOGLE_PLACE_ID');
        $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&fields=reviews&key={$apiKey}&language=ar";

        $response = Http::get($url);
        $data = $response->json();
        $reviews = $data['result']['reviews'] ?? [];
        $locale = app()->getLocale(); // Get current language (e.g., 'ar')

        foreach ($reviews as $review) {
            // Check if a testimonial from the same user already exists
            $existingTestimonial = TestimonialTranslation::where('user_name', $review['author_name'])
                ->where('comment', $review['text']) // Ensure exact same review is not duplicated
                ->exists();

            if (!$existingTestimonial) {
                // Insert into testimonials table
                $testimonial = Testimonial::create([
                    'user_avatar' => $review['profile_photo_url'] ?? 'default.png',
                    'rate' => $review['rating'],
                    'status' => 'disable',
                    'created_at' => time(),
                ]);

                // Insert into testimonial_translations table
                TestimonialTranslation::create([
                    'testimonial_id' => $testimonial->id,
                    'locale' => $locale,
                    'user_name' => $review['author_name'],
                    'user_bio' => 'طالب', // Empty for now, can be updated later
                    'comment' => $review['text'],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Google reviews updated successfully!');
    }


    public function create()
    {
        $this->authorize('admin_testimonials_create');

        removeContentLocale();

        $data = [
            'pageTitle' => trans('admin/pages/comments.new_testimonial'),
        ];

        return view('admin.testimonials.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_testimonials_create');

        $this->validate($request, [
            'user_avatar' => 'required|string',
            'user_name' => 'required|string',
            'user_bio' => 'required|string',
            'rate' => 'required|integer|between:0,5',
            'comment' => 'required|string',
        ]);

        $data = $request->all();

        $testimonial = Testimonial::create([
            'user_avatar' => $data['user_avatar'],
            'rate' => $data['rate'],
            'status' => $data['status'],
            'created_at' => time(),
        ]);

        if (!empty($testimonial)) {
            TestimonialTranslation::updateOrCreate([
                'testimonial_id' => $testimonial->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'user_name' => $data['user_name'],
                'user_bio' => $data['user_bio'],
                'comment' => $data['comment'],
            ]);
        }

        return redirect(getAdminPanelUrl() . '/testimonials');
    }


    public function edit(Request $request, $id)
    {
        $this->authorize('admin_testimonials_edit');

        $testimonial = Testimonial::findOrFail($id);

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $testimonial->getTable(), $testimonial->id);

        $data = [
            'pageTitle' => trans('admin/pages/comments.edit_testimonial'),
            'testimonial' => $testimonial
        ];

        return view('admin.testimonials.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_testimonials_edit');

        $this->validate($request, [
            'user_avatar' => 'required|string',
            'user_name' => 'required|string',
            'user_bio' => 'required|string',
            'rate' => 'required|integer|between:0,5',
            'comment' => 'required|string',
        ]);

        $testimonial = Testimonial::findOrFail($id);

        $data = $request->all();

        $testimonial->update([
            'user_avatar' => $data['user_avatar'],
            'rate' => $data['rate'],
            'status' => $data['status'],
        ]);

        TestimonialTranslation::updateOrCreate([
            'testimonial_id' => $testimonial->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'user_name' => $data['user_name'],
            'user_bio' => $data['user_bio'],
            'comment' => $data['comment'],
        ]);

        removeContentLocale();

        return redirect(getAdminPanelUrl() . '/testimonials');
    }

    public function delete($id)
    {
        $this->authorize('admin_testimonials_delete');

        $testimonial = Testimonial::findOrFail($id);

        $testimonial->delete();

        return redirect(getAdminPanelUrl() . '/testimonials');
    }
}
