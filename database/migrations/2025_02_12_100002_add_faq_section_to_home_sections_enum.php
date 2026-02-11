<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFaqSectionToHomeSectionsEnum extends Migration
{
    /**
     * Run the migrations. Add faq_section to home_sections name enum.
     */
    public function up()
    {
        $enumValues = [
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
        $enumList = "'" . implode("','", $enumValues) . "'";
        DB::statement("ALTER TABLE home_sections MODIFY COLUMN name ENUM({$enumList}) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::table('home_sections')->where('name', 'faq_section')->update(['name' => 'blog']);

        $enumValues = [
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
        ];
        $enumList = "'" . implode("','", $enumValues) . "'";
        DB::statement("ALTER TABLE home_sections MODIFY COLUMN name ENUM({$enumList}) NOT NULL");
    }
}
