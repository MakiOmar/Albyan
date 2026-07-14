<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Services\SlugService;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Category extends Model implements TranslatableContract
{
    use Translatable;
    use Sluggable;

    protected $table = 'categories';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    static $cacheKey = 'categories';

    public $translatedAttributes = ['title', 'seo_title', 'seo_description'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getSeoTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'seo_title');
    }

    public function getSeoDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'seo_description');
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public static function makeSlug($title)
    {
        return SlugService::createSlug(self::class, 'slug', $title);
    }


    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id', 'id');
    }

    public function subCategories()
    {
        return $this->hasMany($this, 'parent_id', 'id')
            ->orderByRaw('IFNULL(`order`, 2147483647) ASC')
            ->orderBy('id', 'asc');
    }

    public function filters()
    {
        return $this->hasMany('App\Models\Filter', 'category_id', 'id');
    }

    public function webinars()
    {
        return $this->hasMany('App\Models\Webinar', 'category_id', 'id');
    }

    public function userOccupations()
    {
        return $this->hasMany('App\Models\UserOccupation', 'category_id', 'id');
    }

    public function getUrl()
    {
        $url = '/categories/';

        if (!empty($this->category)) {
            $url .= $this->category->slug . '/';
        }

        $url .= $this->slug;

        return $url;
    }

    static function getCategories()
    {
        $categories = cache()->remember(self::$cacheKey, 30 * 24 * 60 * 60, function () {
            return self::whereNull('parent_id')
                ->with([
                    'subCategories' => function ($query) {
                        $query->orderByRaw('IFNULL(`order`, 2147483647) ASC')
                            ->orderBy('id', 'asc');
                    },
                ])
                ->orderByRaw('IFNULL(`order`, 2147483647) ASC')
                ->orderBy('id', 'asc')
                ->get();
        });

        // Keep lowest order first even if an older cache entry was unsorted.
        return $categories
            ->sortBy([
                fn ($category) => is_null($category->order) ? PHP_INT_MAX : (int) $category->order,
                fn ($category) => (int) $category->id,
            ])
            ->values();
    }

    public function getCategoryCourses()
    {
        $webinars = collect([]);
        $subCategories = $this->subCategories;

        foreach ($subCategories as $category) {
            $webinars = $webinars->merge($category->webinars);
        }

        return $webinars;
    }

    public function getCategoryInstructorsIdsHasMeeting()
    {
        $ids = [];
        $subCategories = $this->subCategories;

        foreach ($subCategories as $category) {
            if (count($category->userOccupations)) {
                foreach ($category->userOccupations as $occupation) {
                    if (!empty($occupation->user) and !$occupation->user->isUser() and !$occupation->user->isAdmin()) {
                        if (!empty($occupation->user->hasMeeting())) {
                            $ids[] = $occupation->user->id;
                        }
                    }
                }
            }
        }

        return $ids;
    }
}
