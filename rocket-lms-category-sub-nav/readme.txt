=== Rocket LMS Category Sub-Nav ===
Contributors: albayan
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Horizontal course category carousel fed by Rocket LMS JSON endpoint.

== Description ==

Fetches top-level course categories from:

`GET {lms_site}/course-categories/nav?locale=ar`

Renders a responsive horizontal sub-navigation bar. By default hooks into `zskeleton_after_header_search` (ZSkeleton theme).

== Installation ==

1. Copy the `rocket-lms-category-sub-nav` folder to `wp-content/plugins/`.
2. Activate the plugin in WordPress admin.
3. Go to Settings → LMS Category Sub-Nav.
4. Enable the sub-nav and set LMS site URL (e.g. https://albyan.institute).

== Filters ==

* `rlms_cat_subnav_action_hook` — change the action hook (default: zskeleton_after_header_search)
* `rlms_cat_subnav_lms_url` — override LMS base URL
* `rlms_cat_subnav_api_locale` — override locale sent to LMS
* `rlms_cat_subnav_container_class` — wrapper class (default: container)
