<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Page extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'pages';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public $translatedAttributes = ['title', 'seo_description', 'content', 'styles', 'scripts', 'head_content', 'footer_content'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getSeoDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'seo_description');
    }

    public function getContentAttribute()
    {
        return getTranslateAttributeValue($this, 'content');
    }

    public function getStylesAttribute()
    {
        return getTranslateAttributeValue($this, 'styles');
    }

    public function getScriptsAttribute()
    {
        return getTranslateAttributeValue($this, 'scripts');
    }

    public function getHeadContentAttribute()
    {
        return getTranslateAttributeValue($this, 'head_content');
    }

    public function getFooterContentAttribute()
    {
        return getTranslateAttributeValue($this, 'footer_content');
    }
}
