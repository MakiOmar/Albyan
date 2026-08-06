<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use App\Models\Traits\HasLocalizedSlug;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model implements TranslatableContract
{
    use Translatable;
    use Sluggable;
    use HasLocalizedSlug;

    protected $table = 'blog_categories';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
    public $translatedAttributes = ['title', 'slug'];

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

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getSlugAttribute()
    {
        $translated = getTranslateAttributeValue($this, 'slug');
        if (!empty($translated)) {
            return $translated;
        }

        return $this->attributes['slug'] ?? '';
    }



    public function blog()
    {
        return $this->hasMany('App\Models\Blog', 'category_id', 'id');
    }

    public function getUrl(?string $locale = null)
    {
        $locale = mb_strtolower($locale ?: app()->getLocale());

        return localizedUrl('/blog/categories/' . $this->localizedSlug($locale), $locale);
    }
}
