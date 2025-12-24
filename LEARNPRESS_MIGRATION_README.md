# LearnPress Migration Guide - Laravel to WordPress

This document provides a comprehensive mapping between the Laravel Rocket LMS course structure and LearnPress WordPress plugin database structure for course migration.

## Table of Contents

1. [Overview](#overview)
2. [Database Structure Comparison](#database-structure-comparison)
3. [Field Mapping](#field-mapping)
4. [Related Data Migration](#related-data-migration)
5. [Migration Script Considerations](#migration-script-considerations)
6. [Data Transformation Notes](#data-transformation-notes)

---

## Overview

### Migration Scope
This migration includes **course data and compatible relations**:
- ✅ Course basic information (title, description, images, pricing)
- ✅ Course metadata (duration, capacity, status, etc.)
- ✅ Categories (migrated if compatible with LearnPress)
- ✅ Tags (migrated if compatible with LearnPress)
- ✅ Featured courses status
- ✅ Prerequisites
- ✅ FAQs
- ✅ Other relations that fit LearnPress structure
- ❌ Lessons, quizzes, assignments (NOT migrated)
- ❌ Course chapters/sections (NOT migrated)
- ❌ Student enrollments/sales (NOT migrated)
- ❌ Any enrollment-related data (NOT migrated)

### Migration Settings
- **Post Author**: All courses assigned to WordPress admin user
- **Post Status**: All courses set to `draft` (for review before publishing)
- **Post Type**: `lp_course`

### Laravel Structure
- **Main Table**: `webinars` (stores courses, webinars, and text lessons)
- **Translation Tables**: `webinar_translations` (for multilingual support)
- **Related Tables**: Multiple related tables (not used in this migration)

### LearnPress Structure
- **Main Table**: `wp_posts` (WordPress posts table with `post_type = 'lp_course'`)
- **Meta Table**: `wp_postmeta` (stores course metadata as key-value pairs)
- **Custom Tables**: Not used in this basic migration

---

## Database Structure Comparison

### Laravel: `webinars` Table

The `webinars` table contains all course/webinar data in a single table with the following structure:

```sql
webinars
├── id (primary key)
├── teacher_id (foreign key → users.id)
├── creator_id (foreign key → users.id)
├── category_id (foreign key → categories.id)
├── type (enum: 'webinar', 'course', 'text_lesson')
├── title (varchar 255) - stored in translations table
├── slug (varchar 255)
├── description (text) - stored in translations table
├── seo_description (varchar 255) - stored in translations table
├── image_cover (varchar)
├── thumbnail (varchar)
├── video_demo (varchar, nullable)
├── video_demo_source (enum: 'upload', 'youtube', 'vimeo', 'external_link', 'secure_host')
├── start_date (integer, unix timestamp)
├── duration (integer, unsigned)
├── capacity (integer, unsigned, nullable)
├── price (float 15,3, unsigned, nullable)
├── organization_price (float 15,3, unsigned, nullable)
├── points (integer, nullable)
├── support (boolean, default false)
├── downloadable (boolean, default false)
├── certificate (boolean, default false)
├── partner_instructor (boolean, default false)
├── subscribe (boolean, default false)
├── private (boolean, default false)
├── forum (boolean, default false)
├── enable_waitlist (boolean, default false)
├── access_time (integer, nullable)
├── access_days (integer, nullable)
├── message_for_reviewer (text, nullable)
├── status (enum: 'active', 'pending', 'is_draft', 'inactive')
├── timezone (varchar, nullable)
├── sales_count_number (integer, unsigned, nullable)
├── created_at (integer, unix timestamp)
├── updated_at (integer, nullable, unix timestamp)
└── deleted_at (integer, nullable, unix timestamp)
```

### LearnPress: `wp_posts` + `wp_postmeta` Tables

LearnPress uses WordPress's standard post structure:

**wp_posts table:**
```sql
wp_posts
├── ID (primary key)
├── post_author (foreign key → wp_users.ID)
├── post_date (datetime)
├── post_date_gmt (datetime)
├── post_content (longtext) - course description
├── post_title (varchar 255) - course title
├── post_excerpt (text) - short description
├── post_status (varchar 20) - 'publish', 'draft', 'pending', etc.
├── comment_status (varchar 20)
├── ping_status (varchar 20)
├── post_password (varchar 255)
├── post_name (varchar 200) - slug
├── to_ping (text)
├── pinged (text)
├── post_modified (datetime)
├── post_modified_gmt (datetime)
├── post_content_filtered (longtext)
├── post_parent (bigint)
├── guid (varchar 255)
├── menu_order (integer)
├── post_type (varchar 20) - 'lp_course' for courses
└── post_mime_type (varchar 100)
```

**wp_postmeta table (key-value pairs):**
```sql
wp_postmeta
├── meta_id (primary key)
├── post_id (foreign key → wp_posts.ID)
├── meta_key (varchar 255) - LearnPress meta keys (see below)
└── meta_value (longtext) - meta value
```

---

## Field Mapping

### Direct Field Mappings (wp_posts table)

| Laravel Field | LearnPress Field | Notes |
|--------------|------------------|-------|
| `id` | `wp_posts.ID` | New ID will be generated, keep mapping |
| `title` | `wp_posts.post_title` | From `webinar_translations.title` |
| `description` | `wp_posts.post_content` | From `webinar_translations.description` |
| `seo_description` | `wp_posts.post_excerpt` | From `webinar_translations.seo_description` |
| `slug` | `wp_posts.post_name` | URL-friendly slug |
| `teacher_id` | `wp_posts.post_author` | **Always set to WordPress admin user ID** |
| `status` | `wp_posts.post_status` | **Always set to 'draft'** (regardless of Laravel status) |
| `created_at` | `wp_posts.post_date` | Convert unix timestamp to datetime |
| `created_at` | `wp_posts.post_date_gmt` | Convert to GMT |
| `updated_at` | `wp_posts.post_modified` | Convert unix timestamp to datetime |
| `updated_at` | `wp_posts.post_modified_gmt` | Convert to GMT |
| - | `wp_posts.post_type` | Always set to `'lp_course'` |
| - | `wp_posts.comment_status` | Set to `'open'` or `'closed'` based on your preference |
| - | `wp_posts.ping_status` | Set to `'closed'` |

### Meta Field Mappings (wp_postmeta table)

| Laravel Field | LearnPress Meta Key | Data Type | Notes |
|--------------|-------------------|-----------|-------|
| `price` | `_lp_price` or `_lp_regular_price` | float | Regular course price |
| `organization_price` | `_lp_organization_price` | float | Custom meta (may need custom handling) |
| `image_cover` | `_thumbnail_id` | integer | WordPress attachment ID (need to import image first) |
| `thumbnail` | `_lp_course_thumbnail` | varchar | Or use `_thumbnail_id` |
| `video_demo` | `_lp_video_demo` | varchar | Video URL |
| `video_demo_source` | `_lp_video_demo_source` | varchar | Source type |
| `duration` | `_lp_duration` | integer/string | Course duration (e.g., "10 weeks", "30 hours") |
| `capacity` | `_lp_max_students` | integer | Maximum students |
| `sales_count_number` | `_lp_students` | integer | Fake student count for display |
| `certificate` | `_lp_certificate` | boolean | Enable certificate (convert to 'yes'/'no') |
| `support` | `_lp_support` | boolean | Enable support (convert to 'yes'/'no') |
| `downloadable` | `_lp_downloadable` | boolean | Downloadable content (convert to 'yes'/'no') |
| `points` | `_lp_points` | integer | Course points/rewards |
| `access_days` | `_lp_block_expire_duration` | integer | Days until access expires |
| `private` | `_lp_private` | boolean | Private course (convert to 'yes'/'no') |
| `forum` | `_lp_forum` | boolean | Enable forum (convert to 'yes'/'no') |
| `enable_waitlist` | `_lp_waitlist` | boolean | Enable waitlist (convert to 'yes'/'no') |
| `type` | `_lp_course_type` | varchar | 'webinar', 'course', 'text_lesson' |
| `timezone` | `_lp_timezone` | varchar | Timezone setting |
| `start_date` | `_lp_start_date` | integer | Unix timestamp |
| `category_id` | Taxonomy: `course_category` | integer | Use WordPress taxonomy, not postmeta |
| - | `_lp_featured` | boolean | Featured course (from `feature_webinars` table) |
| - | `_lp_level` | varchar | Course level (beginner, intermediate, advanced) |
| - | `_lp_retake_count` | integer | Number of retakes allowed (default: 0) |
| - | `_lp_course_result` | varchar | Evaluation type ('evaluate_lesson', 'evaluate_quiz', etc.) |
| - | `_lp_passing_condition` | integer | Passing grade percentage (default: 80) |
| - | `_lp_no_required_enroll` | boolean | No enrollment required (convert to 'yes'/'no') |

### Status Mapping

**IMPORTANT**: All courses are migrated as `draft` status regardless of Laravel status.

| Laravel Status | LearnPress Status | Notes |
|---------------|-------------------|-------|
| `active` | `draft` | **Forced to draft** - review before publishing |
| `pending` | `draft` | **Forced to draft** - review before publishing |
| `is_draft` | `draft` | **Forced to draft** - review before publishing |
| `inactive` | `draft` | **Forced to draft** - review before publishing |

**Rationale**: This ensures all migrated courses require review before going live in WordPress.

### Type Mapping

| Laravel Type | LearnPress Handling | Notes |
|-------------|---------------------|-------|
| `course` | Standard course | Regular LearnPress course |
| `webinar` | Course with webinar features | May need custom meta or addon |
| `text_lesson` | Course with text content | Standard course with text lessons |

---

## Related Data Migration

> **IMPORTANT**: This migration includes course data and compatible relations, but **excludes lessons, quizzes, and enrollment-related data**.

### 1. Categories

**Laravel:**
- Table: `categories`
- Relationship: `webinars.category_id` → `categories.id`

**LearnPress:**
- WordPress Taxonomy: `course_category`
- Use `wp_set_object_terms()` to assign categories

**Migration:**
```php
// Map category_id to WordPress taxonomy term
// First, ensure category exists in WordPress taxonomy
$wp_category_id = map_category_id($course->category_id);
if ($wp_category_id) {
    wp_set_object_terms($wp_post_id, [$wp_category_id], 'course_category');
}
```

### 2. Course Chapters/Sections

**NOT MIGRATED** - This migration only includes basic course information. Chapters/sections will need to be created manually in LearnPress after migration.

### 3. Lessons

**NOT MIGRATED** - Lessons, sessions, and text lessons are not included in this migration. They will need to be created manually in LearnPress after migration.

### 4. Quizzes

**NOT MIGRATED** - Quizzes and quiz questions are not included in this migration. They will need to be created manually in LearnPress after migration.

### 5. Assignments

**NOT MIGRATED** - Assignments are not included in this migration.

### 6. FAQs

**Laravel:**
- Table: `faqs`
- Relationship: `faqs.webinar_id` → `webinars.id`
- Fields: `title`, `answer`, `order`

**LearnPress:**
- Meta Key: `_lp_faqs` (stored as JSON/array in postmeta)

**Migration:**
```php
// Get FAQs for this course
$faqs = DB::table('faqs')
    ->where('webinar_id', $course->id)
    ->orderBy('order', 'asc')
    ->get()
    ->map(function($faq) {
        return [
            'question' => $faq->title,
            'answer' => $faq->answer,
        ];
    })
    ->toArray();

if (!empty($faqs)) {
    update_post_meta($wp_post_id, '_lp_faqs', $faqs);
}
```

### 7. Tags

**Laravel:**
- Table: `tags`
- Relationship: `tags.webinar_id` → `webinars.id`

**LearnPress:**
- WordPress Taxonomy: `course_tag`
- Use `wp_set_object_terms()` to assign tags

**Migration:**
```php
// Get tags for course
$tags = DB::table('tags')
    ->where('webinar_id', $course->id)
    ->pluck('title')
    ->toArray();

if (!empty($tags)) {
    // Tags will be created automatically if they don't exist
    wp_set_object_terms($wp_post_id, $tags, 'course_tag');
}
```

### 8. Instructors/Teachers

**Laravel:**
- Table: `users` (with role 'instructor')
- Fields: `webinars.teacher_id`, `webinars.creator_id`
- Table: `webinar_partner_teachers` (for multiple instructors)

**LearnPress:**
- Primary: `wp_posts.post_author` (main instructor)

**Migration:**
- **All courses assigned to WordPress admin user** (ID: 1 or get admin user ID)
- Original `teacher_id` and `creator_id` stored in meta for reference:
  - `_lp_original_teacher_id` - Original Laravel teacher ID
  - `_lp_original_creator_id` - Original Laravel creator ID
- Partner teachers: Store in custom meta `_lp_partner_teachers` (array of original user IDs) for reference only

### 9. Reviews

**Laravel:**
- Table: `webinar_reviews`
- Relationship: `webinar_reviews.webinar_id` → `webinars.id`

**LearnPress:**
- May use WordPress comments system
- Or LearnPress-specific review system (check addons)

### 10. Sales/Enrollments

**NOT MIGRATED** - Sales and enrollment data are not included in this migration. Students will need to re-enroll after migration.

### 11. Prerequisites

**Laravel:**
- Table: `prerequisites`
- Relationship: `prerequisites.webinar_id` → `webinars.id`
- Field: `prerequisites.prerequisite_id` → `webinars.id` (the prerequisite course)

**LearnPress:**
- Meta Key: `_lp_prerequisites` (array of course IDs)

**Migration:**
```php
// Get prerequisites for this course
$prerequisites = DB::table('prerequisites')
    ->where('webinar_id', $course->id)
    ->pluck('prerequisite_id')
    ->toArray();

// Map Laravel course IDs to WordPress post IDs
$wp_prerequisite_ids = [];
foreach ($prerequisites as $laravel_course_id) {
    $wp_course_id = get_mapped_wp_id('webinars', $laravel_course_id);
    if ($wp_course_id) {
        $wp_prerequisite_ids[] = $wp_course_id;
    }
}

if (!empty($wp_prerequisite_ids)) {
    update_post_meta($wp_post_id, '_lp_prerequisites', $wp_prerequisite_ids);
}
```

**Note**: Prerequisites must be migrated after all courses are migrated, or in a second pass.

### 12. Certificates

**Laravel:**
- Table: `webinar_certificates`
- Field: `webinars.certificate` (boolean)

**LearnPress:**
- Meta Key: `_lp_certificate` ('yes'/'no')
- May require LearnPress Certificates addon

### 13. Translations

**Laravel:**
- Table: `webinar_translations`
- Fields: `title`, `description`, `seo_description`
- Relationship: `webinar_translations.webinar_id` → `webinars.id`

**LearnPress:**
- Use WordPress multilingual plugins (WPML, Polylang, etc.)
- Or store in postmeta with language suffix

**Migration:**
- If using WPML: Create separate posts per language
- If using Polylang: Link translations
- Or use custom meta: `_lp_title_ar`, `_lp_description_ar`, etc.

---

## Migration Script Considerations

### 1. ID Mapping

Create a mapping table to track Laravel IDs to WordPress IDs:

```sql
CREATE TABLE migration_map (
    laravel_table VARCHAR(50),
    laravel_id INT,
    wp_table VARCHAR(50),
    wp_id INT,
    PRIMARY KEY (laravel_table, laravel_id)
);
```

### 2. Image Migration

1. Download images from Laravel storage
2. Upload to WordPress media library using `wp_upload_bits()`
3. Create attachment post using `wp_insert_attachment()`
4. Store attachment ID in `_thumbnail_id` meta

### 3. User Migration

**Simplified Approach:**
1. Get WordPress admin user ID (usually ID: 1)
2. Assign all courses to admin user: `wp_posts.post_author = admin_user_id`
3. Store original teacher/creator IDs in meta for reference:
   - `_lp_original_teacher_id` - Original Laravel teacher ID
   - `_lp_original_creator_id` - Original Laravel creator ID
4. **Note**: User migration not required for this basic course migration

### 4. Date Conversion

```php
// Laravel: Unix timestamp (integer)
$laravel_timestamp = 1609459200; // Example

// WordPress: MySQL datetime format
$wp_datetime = date('Y-m-d H:i:s', $laravel_timestamp);
$wp_datetime_gmt = gmdate('Y-m-d H:i:s', $laravel_timestamp);
```

### 5. Boolean Conversion

```php
// Laravel: true/false (boolean or tinyint)
$laravel_bool = true;

// LearnPress: 'yes'/'no' (string)
$lp_bool = $laravel_bool ? 'yes' : 'no';
```

### 6. Content Sanitization

```php
// Sanitize content for WordPress
$wp_content = wp_kses_post($laravel_description);
$wp_title = sanitize_text_field($laravel_title);
$wp_excerpt = sanitize_textarea_field($laravel_seo_description);
```

### 7. Slug Generation

```php
// Ensure unique slug
$wp_slug = sanitize_title($laravel_slug);
$wp_slug = wp_unique_post_slug($wp_slug, $wp_post_id, 'publish', 'lp_course', 0);
```

---

## Data Transformation Notes

### 1. Price Handling

- LearnPress uses `_lp_price` for regular price
- Sale prices use `_lp_sale_price`, `_lp_sale_start`, `_lp_sale_end`
- If Laravel has special offers, map to LearnPress sale price system

### 2. Duration Format

- Laravel: Integer (seconds or days)
- LearnPress: String format (e.g., "10 weeks", "30 hours", "90 days")
- Convert: `$lp_duration = $laravel_duration . ' days';`

### 3. Course Access

- Laravel: `access_days` (integer)
- LearnPress: `_lp_block_expire_duration` (integer in seconds or days)
- May need to convert days to seconds or keep as days based on LearnPress version

### 4. Course Type

- Laravel has three types: 'webinar', 'course', 'text_lesson'
- LearnPress primarily uses 'lp_course' post type
- Store original type in meta: `_lp_course_type`
- Webinar features may require LearnPress addon

### 5. Multilingual Support

- Laravel uses translation tables
- WordPress uses plugins (WPML, Polylang) or custom meta
- Consider creating separate posts per language or using translation plugins

### 6. Featured Courses

- Laravel: `feature_webinars` table
- LearnPress: `_lp_featured` meta ('yes'/'no')

**Migration:**
```php
// Check if course is featured
$is_featured = DB::table('feature_webinars')
    ->where('webinar_id', $course->id)
    ->exists();
    
update_post_meta($wp_post_id, '_lp_featured', $is_featured ? 'yes' : 'no');
```

### 7. Course Capacity

- Laravel: `capacity` (max students)
- LearnPress: `_lp_max_students` (meta)
- Also map `sales_count_number` to `_lp_students` for fake count

### 8. Video Demo

- Laravel: `video_demo` + `video_demo_source`
- LearnPress: Store in custom meta or use LearnPress video features
- Meta keys: `_lp_video_demo`, `_lp_video_demo_source`

---

## Sample Migration Query Structure

```php
// Simplified migration - courses only, no lessons/quizzes

// 1. Get WordPress admin user ID
$wp_admin_id = 1; // Or get admin user: get_user_by('login', 'admin')->ID;

// 2. Get all Laravel courses (all types and statuses)
$laravel_courses = DB::table('webinars')
    ->whereIn('type', ['course', 'webinar', 'text_lesson']) // All types
    ->whereNotNull('slug') // Only courses with slugs
    ->get();

foreach ($laravel_courses as $course) {
    // 3. Get translations (default locale)
    $translation = DB::table('webinar_translations')
        ->where('webinar_id', $course->id)
        ->where('locale', 'en') // or your default locale
        ->first();
    
    // 4. Create WordPress post - ALWAYS AS DRAFT
    $wp_post_data = [
        'post_title'    => $translation->title ?? $course->title ?? 'Untitled Course',
        'post_content'  => $translation->description ?? '',
        'post_excerpt'  => $translation->seo_description ?? '',
        'post_name'     => $course->slug,
        'post_status'   => 'draft', // FORCED TO DRAFT
        'post_type'     => 'lp_course',
        'post_author'   => $wp_admin_id, // ASSIGNED TO ADMIN
        'post_date'     => date('Y-m-d H:i:s', $course->created_at),
        'post_date_gmt' => gmdate('Y-m-d H:i:s', $course->created_at),
        'post_modified' => $course->updated_at 
            ? date('Y-m-d H:i:s', $course->updated_at) 
            : date('Y-m-d H:i:s', $course->created_at),
        'post_modified_gmt' => $course->updated_at 
            ? gmdate('Y-m-d H:i:s', $course->updated_at) 
            : gmdate('Y-m-d H:i:s', $course->created_at),
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
    ];
    
    $wp_post_id = wp_insert_post($wp_post_data);
    
    if (is_wp_error($wp_post_id)) {
        error_log("Failed to create course: " . $wp_post_id->get_error_message());
        continue;
    }
    
    // 5. Store ID mapping for reference
    store_migration_map('webinars', $course->id, 'wp_posts', $wp_post_id);
    
    // 6. Add basic meta fields
    if ($course->price) {
        update_post_meta($wp_post_id, '_lp_price', floatval($course->price));
    }
    
    if ($course->duration) {
        update_post_meta($wp_post_id, '_lp_duration', $course->duration . ' days');
    }
    
    if ($course->capacity) {
        update_post_meta($wp_post_id, '_lp_max_students', intval($course->capacity));
    }
    
    if ($course->sales_count_number) {
        update_post_meta($wp_post_id, '_lp_students', intval($course->sales_count_number));
    }
    
    // Boolean fields
    update_post_meta($wp_post_id, '_lp_certificate', $course->certificate ? 'yes' : 'no');
    update_post_meta($wp_post_id, '_lp_support', $course->support ? 'yes' : 'no');
    update_post_meta($wp_post_id, '_lp_downloadable', $course->downloadable ? 'yes' : 'no');
    update_post_meta($wp_post_id, '_lp_private', $course->private ? 'yes' : 'no');
    update_post_meta($wp_post_id, '_lp_forum', $course->forum ? 'yes' : 'no');
    
    // Store original IDs for reference
    if ($course->teacher_id) {
        update_post_meta($wp_post_id, '_lp_original_teacher_id', $course->teacher_id);
    }
    if ($course->creator_id) {
        update_post_meta($wp_post_id, '_lp_original_creator_id', $course->creator_id);
    }
    
    // Store original status for reference
    update_post_meta($wp_post_id, '_lp_original_status', $course->status);
    
    // Course type
    update_post_meta($wp_post_id, '_lp_course_type', $course->type);
    
    // Additional fields
    if ($course->points) {
        update_post_meta($wp_post_id, '_lp_points', intval($course->points));
    }
    
    if ($course->access_days) {
        update_post_meta($wp_post_id, '_lp_block_expire_duration', intval($course->access_days));
    }
    
    if ($course->timezone) {
        update_post_meta($wp_post_id, '_lp_timezone', $course->timezone);
    }
    
    if ($course->video_demo) {
        update_post_meta($wp_post_id, '_lp_video_demo', $course->video_demo);
        if ($course->video_demo_source) {
            update_post_meta($wp_post_id, '_lp_video_demo_source', $course->video_demo_source);
        }
    }
    
    // Featured course
    $is_featured = DB::table('feature_webinars')
        ->where('webinar_id', $course->id)
        ->exists();
    update_post_meta($wp_post_id, '_lp_featured', $is_featured ? 'yes' : 'no');
    
    // FAQs
    $faqs = DB::table('faqs')
        ->where('webinar_id', $course->id)
        ->orderBy('order', 'asc')
        ->get()
        ->map(function($faq) {
            return [
                'question' => $faq->title ?? '',
                'answer' => $faq->answer ?? '',
            ];
        })
        ->toArray();
    
    if (!empty($faqs)) {
        update_post_meta($wp_post_id, '_lp_faqs', $faqs);
    }
    
    // 7. Set category (if exists)
    if ($course->category_id) {
        $wp_category_id = map_category_id($course->category_id);
        if ($wp_category_id) {
            wp_set_object_terms($wp_post_id, [$wp_category_id], 'course_category');
        }
    }
    
    // 8. Set tags (if exists)
    $tags = DB::table('tags')
        ->where('webinar_id', $course->id)
        ->pluck('title')
        ->toArray();
    
    if (!empty($tags)) {
        wp_set_object_terms($wp_post_id, $tags, 'course_tag');
    }
    
    // 9. Handle featured image (if exists)
    if ($course->image_cover || $course->thumbnail) {
        $image_path = $course->thumbnail ?? $course->image_cover;
        // Import image and set as featured image
        $attachment_id = import_image_as_attachment($image_path, $wp_post_id);
        if ($attachment_id) {
            set_post_thumbnail($wp_post_id, $attachment_id);
        }
    }
    
    // Note: 
    // - Categories, tags, FAQs, featured status are migrated
    // - Prerequisites need to be migrated in a second pass (after all courses exist)
    // - Chapters, lessons, quizzes, assignments are NOT migrated
    // - Sales/enrollments are NOT migrated
}

// SECOND PASS: Migrate Prerequisites (after all courses are migrated)
$all_courses = DB::table('webinars')->get();
foreach ($all_courses as $course) {
    $wp_post_id = get_mapped_wp_id('webinars', $course->id);
    if (!$wp_post_id) continue;
    
    // Get prerequisites
    $prerequisites = DB::table('prerequisites')
        ->where('webinar_id', $course->id)
        ->pluck('prerequisite_id')
        ->toArray();
    
    // Map to WordPress IDs
    $wp_prerequisite_ids = [];
    foreach ($prerequisites as $laravel_course_id) {
        $wp_course_id = get_mapped_wp_id('webinars', $laravel_course_id);
        if ($wp_course_id) {
            $wp_prerequisite_ids[] = $wp_course_id;
        }
    }
    
    if (!empty($wp_prerequisite_ids)) {
        update_post_meta($wp_post_id, '_lp_prerequisites', $wp_prerequisite_ids);
    }
}
```

---

## Important Notes

1. **Backup First**: Always backup both databases before migration
2. **Test Migration**: Run on a test/staging environment first
3. **Migration Scope**: This migration includes:
   - ✅ Course information (title, description, images)
   - ✅ Course metadata (price, duration, capacity, etc.)
   - ✅ Categories and tags
   - ✅ Featured courses status
   - ✅ FAQs
   - ✅ Prerequisites (migrated in second pass)
   - ❌ No lessons, quizzes, or assignments
   - ❌ No chapters/sections
   - ❌ No student enrollments/sales
   - ❌ No enrollment-related data
4. **All Courses as Draft**: All migrated courses will be in `draft` status for review
5. **All Courses to Admin**: All courses assigned to WordPress admin user
6. **Original Data Preserved**: Original teacher/creator IDs stored in meta for reference
7. **Media Files**: Plan for image/video file migration separately
8. **Manual Work Required**: After migration, you'll need to:
   - Review and publish courses manually
   - Create course content (lessons, quizzes) in LearnPress
   - Create course chapters/sections in LearnPress
   - Re-assign courses to proper instructors if needed
   - Verify prerequisites are correctly linked
9. **Two-Pass Migration**: Prerequisites require a second pass after all courses are migrated, as they reference other courses
9. **Performance**: Use batch processing for large datasets
10. **Validation**: Validate data after migration - check a sample of courses
11. **Rollback Plan**: Have a rollback strategy in case of issues

---

## LearnPress Meta Keys Reference

Common LearnPress meta keys used in courses:

- `_lp_price` - Regular price
- `_lp_regular_price` - Regular price (newer versions)
- `_lp_sale_price` - Sale price
- `_lp_sale_start` - Sale start date
- `_lp_sale_end` - Sale end date
- `_lp_duration` - Course duration
- `_lp_max_students` - Maximum students
- `_lp_students` - Fake student count
- `_lp_featured` - Featured course ('yes'/'no')
- `_lp_level` - Course level
- `_lp_retake_count` - Retake count
- `_lp_course_result` - Evaluation type
- `_lp_passing_condition` - Passing grade
- `_lp_certificate` - Enable certificate
- `_lp_no_required_enroll` - No enrollment required
- `_lp_block_expire_duration` - Access expiration duration
- `_lp_final_quiz` - Final quiz ID
- `_lp_prerequisites` - Prerequisite course IDs (array)

---

## Additional Resources

- [LearnPress Documentation](https://learnpress.github.io/learnpress/)
- [WordPress Post Meta API](https://developer.wordpress.org/reference/functions/update_post_meta/)
- [WordPress Taxonomy API](https://developer.wordpress.org/reference/functions/wp_set_object_terms/)

---

**Last Updated**: 2024
**Version**: 1.0

