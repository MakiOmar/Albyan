<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class SiteFaqTranslation extends Model
{
    protected $table = 'site_faq_translations';
    public $timestamps = false;
    protected $guarded = ['id'];
}
