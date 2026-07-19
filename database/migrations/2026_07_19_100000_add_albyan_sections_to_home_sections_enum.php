<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAlbyanSectionsToHomeSectionsEnum extends Migration
{
    /**
     * Base enum values before the Al-Byan homepage sections were added.
     */
    private array $baseEnumValues = [
        'featured_classes',
        'latest_bundles',
        'latest_classes',
        'best_rates',
        'trend_categories',
        'full_advertising_banner',
        'best_sellers',
        'discount_classes',
        'free_classes',
        'store_products',
        'testimonials',
        'subscribes',
        'find_instructors',
        'reward_program',
        'become_instructor',
        'forum_section',
        'video_or_image_section',
        'instructors',
        'half_advertising_banner',
        'organizations',
        'blog',
        'upcoming_courses',
        'category_courses',
        'faq_section',
    ];

    /**
     * Al-Byan optional homepage sections (enabled via Admin → Home sections).
     */
    private array $albyanSections = [
        'trust_badges',
        'training_domains',
        'training_modality',
        'why_albyan',
        'help_cta_band',
        'wp_blog',
    ];

    /**
     * Run the migrations. Add Al-Byan section names to home_sections name enum.
     */
    public function up()
    {
        $enumValues = array_merge($this->baseEnumValues, $this->albyanSections);
        $enumList = "'" . implode("','", $enumValues) . "'";
        DB::statement("ALTER TABLE home_sections MODIFY COLUMN name ENUM({$enumList}) NOT NULL");
    }

    /**
     * Reverse the migrations. Remove Al-Byan rows before shrinking the enum.
     */
    public function down()
    {
        DB::table('home_sections')->whereIn('name', $this->albyanSections)->delete();

        $enumList = "'" . implode("','", $this->baseEnumValues) . "'";
        DB::statement("ALTER TABLE home_sections MODIFY COLUMN name ENUM({$enumList}) NOT NULL");
    }
}
