## Rocket LMS → WordPress (LearnPress) Sync

This document explains how the Laravel side and the WordPress plugin work together to migrate/sync courses.

---

## 1. Overview

- **Source (Laravel / Rocket LMS)**:
  - Reads courses from the `webinars` table (+ some related tables).
  - Builds a JSON payload following `LEARNPRESS_MIGRATION_README.md`.
  - Sends the payload to WordPress over a secured REST endpoint.

- **Target (WordPress + LearnPress)**:
  - Custom plugin exposes a REST API endpoint.
  - Receives the payload, creates/updates an `lp_course` post.
  - Sets LearnPress meta, categories, tags, FAQs, featured status, etc.

You can:
- Send **one course** for testing.
- Later extend to bulk/queued sync using the same service.

---

## 2. Laravel Side (Rocket LMS)

### 2.1 Files Added

- `app/Services/WordpressCourseSyncService.php`
  - Builds the course payload from a `Webinar` model.
  - Sends it to WordPress via HTTP.
  - Handles errors and logging.

- `app/Console/Commands/SyncSingleCourseToWordpress.php`
  - Artisan command to send **one** course for testing.
  - Command name: `wp:sync-course`.

- `config/services.php`
  - New config section:

```php
'wordpress_sync' => [
    'base_url'  => env('WORDPRESS_SYNC_BASE_URL'),
    'api_token' => env('WORDPRESS_SYNC_API_TOKEN'),
],
```

### 2.2 Environment Variables

In `.env`:

```env
WORDPRESS_SYNC_BASE_URL=https://your-wordpress-site.com
WORDPRESS_SYNC_API_TOKEN=change-me-token
```

- `WORDPRESS_SYNC_BASE_URL`: Base URL of your WP site (no trailing slash needed).
- `WORDPRESS_SYNC_API_TOKEN`: Shared secret token, must match the token used by the WordPress plugin.

### 2.3 How the Service Works

`WordpressCourseSyncService::syncSingleCourse($webinarId)`:

1. Loads the `Webinar`:
   - Includes relations like `category`.
2. Builds a payload array:
   - Identification: `laravel_id`, `slug`, `type`, `status`.
   - Text: `title`, `description`, `seo_description`.
   - Media: `image_cover`, `thumbnail`, `video_demo`, `video_demo_source`.
   - Pricing: `price`, `organization_price`, `capacity`, `sales_count_number`.
   - Flags: `support`, `downloadable`, `certificate`, `private`, `forum`, `enable_waitlist`, `is_featured`.
   - Category: `category`, `category_slug`.
   - Tags: `tags` (array of tag titles).
   - FAQs: `faqs` (array of `{question, answer}`).
   - Prerequisites: `prerequisites` (array of Laravel course IDs).
   - Timestamps: `created_at`, `updated_at`.
3. Sends POST request to:
   - `{$WORDPRESS_SYNC_BASE_URL}/wp-json/rocket-lms/v1/course`
   - Headers:
     - `Accept: application/json`
     - `Content-Type: application/json`
     - `X-RocketLMS-Token: WORDPRESS_SYNC_API_TOKEN`
4. Returns a structured array:
   - `['success' => true/false, 'status' => ..., 'body' => ..., 'error' => ...]`.

### 2.4 Running a Single-Course Test

**Command:**

```bash
php artisan wp:sync-course {webinar_id}
```

Example:

```bash
php artisan wp:sync-course 2496
```

Output:
- Shows whether sync succeeded or failed.
- Prints any error message and the response body from WordPress.

Use this to test the integration with a single course **before** implementing bulk sync.

---

## 3. WordPress Side (LearnPress)

### 3.1 Plugin Files

- Folder: `wordpress-rocket-lms-sync-plugin/`
- Main file: `rocket-lms-sync.php`

Copy this folder to your WordPress installation:

- `wp-content/plugins/wordpress-rocket-lms-sync-plugin/rocket-lms-sync.php`

Then **activate** the plugin from:
- `WordPress Admin → Plugins`.

### 3.2 Authentication

The plugin uses a simple shared token:

```php
if (!defined('ROCKET_LMS_API_TOKEN')) {
    define('ROCKET_LMS_API_TOKEN', 'change-me-token');
}
```

You can:
- Edit the token directly in `rocket-lms-sync.php`, or
- Define `ROCKET_LMS_API_TOKEN` in `wp-config.php`.

This token **must match** `WORDPRESS_SYNC_API_TOKEN` in Laravel.

Requests must send a header:

```text
X-RocketLMS-Token: <your-token>
```

### 3.3 REST Endpoint

The plugin registers:

```text
POST /wp-json/rocket-lms/v1/course
```

- Permission check: `X-RocketLMS-Token` header must be valid.
- Body: JSON payload from Laravel (see Section 2.3).

### 3.4 What the Endpoint Does

Handler: `rocket_lms_sync_handle_course()`.

Steps:

1. Validate payload:
   - Requires `laravel_id` and `title`.

2. Find or create an `lp_course` post:
   - Look up by meta `_rocket_lms_source_id = laravel_id`.
   - If found → `wp_update_post`.
   - If not found → `wp_insert_post`.
   - Sets:
     - `post_title`, `post_content`, `post_excerpt`, `post_name`.
     - `post_type = 'lp_course'`.
     - `post_status = 'draft'` (always draft for review).
     - `post_author` = first admin user.

3. Store mapping/meta:
   - `_rocket_lms_source = 'laravel'`
   - `_rocket_lms_source_id = {laravel_id}`
   - `_lp_price`, `_lp_max_students`, `_lp_students`, `_lp_points`,
     `_lp_block_expire_duration`, `_lp_timezone`, `_lp_course_type`,
     `_lp_certificate`, `_lp_support`, `_lp_downloadable`,
     `_lp_private`, `_lp_forum`, `_lp_featured`, `_lp_faqs`,
     `_lp_original_status`.

4. Assign category:
   - Uses `category_slug` (if present) or `category` (title).
   - Taxonomy: `course_category`.
   - Creates category if it does not exist.

5. Assign tags:
   - Uses `tags` array.
   - Taxonomy: `course_tag`.
   - Tags are created automatically if they do not exist.

6. Returns JSON:

```json
{
  "success": true,
  "post_id": 123,
  "message": "Course synced successfully."
}
```

If there is an error, returns `WP_Error` with HTTP 4xx/5xx.

### 3.5 What Is NOT Handled Yet

- Prerequisites are not yet applied on the WordPress side (they require that all courses exist and a second pass to map IDs).
- Lessons, quizzes, assignments, chapters/sections are not created.
- Sales/enrollments/orders are not created.

These can be added later as separate endpoints/commands if needed.

---

## 4. End-to-End Test Flow

1. **Configure Laravel**
   - Set `WORDPRESS_SYNC_BASE_URL` and `WORDPRESS_SYNC_API_TOKEN` in `.env`.
   - Clear config cache if needed:
     - `php artisan config:clear`

2. **Install & Configure WordPress Plugin**
   - Copy `wordpress-rocket-lms-sync-plugin` to `wp-content/plugins/`.
   - Ensure `ROCKET_LMS_API_TOKEN` matches `WORDPRESS_SYNC_API_TOKEN`.
   - Activate plugin in WP admin.

3. **Send a Test Course from Laravel**

```bash
php artisan wp:sync-course 123
```

4. **Verify in WordPress**
   - Go to **LearnPress → Courses**.
   - A new course (or updated course) should appear in **Draft** status.
   - Check:
     - Title, content, excerpt.
     - Price and other meta.
     - Category and tags.
     - Featured flag and FAQs (if present).

Once this works for a single course, you can:
- Add a second Artisan command to loop over all courses.
- Add a second-pass command/endpoint to apply prerequisites using the ID mapping meta.

---

## 5. Notes & Best Practices

- Always test on a **staging** WordPress site first.
- Keep a mapping of Laravel IDs ↔ WordPress post IDs (already stored in post meta).
- Use queues for bulk sync to avoid timeouts.
- Log failures on both sides:
  - Laravel: `storage/logs/laravel.log`.
  - WordPress: error log or custom logging.


