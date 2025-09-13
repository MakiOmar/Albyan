# Multilingual Functionality - Comprehensive Guide

## Overview

The Rocket LMS application implements a comprehensive multilingual system that supports multiple languages for both the user interface and content. The system is built on Laravel's localization features and uses the Astrotomic Translatable package for database content translation.

## Architecture

### 1. Language Configuration

#### Supported Languages
The system supports 60+ languages including:
- **EN** - English (default)
- **AR** - Arabic (RTL support)
- **ES** - Spanish
- **FR** - French
- **DE** - German
- **IT** - Italian
- **PT** - Portuguese
- **RU** - Russian
- **ZH** - Chinese
- And many more...

#### Language Settings
- **Site Language**: Default language for the application
- **User Languages**: Available languages for users to choose from
- **Content Translation**: Enable/disable content translation features

### 2. Database Structure

#### Translation Tables
Each translatable model has a corresponding translation table following the pattern: `{model_name}_translations`

Example structure:
```sql
-- blog_translations table
CREATE TABLE blog_translations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    blog_id INT UNSIGNED,
    locale VARCHAR(191),
    title VARCHAR(255),
    description TEXT,
    content LONGTEXT,
    meta_description TEXT NULL,
    UNIQUE KEY unique_blog_locale (blog_id, locale),
    FOREIGN KEY (blog_id) REFERENCES blog(id) ON DELETE CASCADE
);
```

#### Translatable Models
Models that support translation implement the `Translatable` trait:

```php
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Blog extends Model implements TranslatableContract
{
    use Translatable;
    
    public $translatedAttributes = ['title', 'description', 'content', 'meta_description'];
}
```

### 3. Translation Models
Translation models are located in `app/Models/Translation/` and follow the pattern `{ModelName}Translation.php`:

```php
namespace App\Models\Translation;

class BlogTranslation extends Model
{
    protected $table = 'blog_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
```

### 4. Setting Up New Translatable Models

#### Step 1: Create Migration for Translation Table
```php
// Create migration: php artisan make:migration create_custom_model_translations_table
public function up()
{
    Schema::create('custom_model_translations', function (Blueprint $table) {
        $table->engine = "InnoDB";
        
        $table->bigIncrements('id');
        $table->unsignedInteger('custom_model_id');
        $table->string('locale', 191)->index();
        $table->string('title');
        $table->text('description')->nullable();
        $table->longText('content')->nullable();
        
        $table->unique(['custom_model_id', 'locale']);
        $table->foreign('custom_model_id')->references('id')->on('custom_models')->onDelete('cascade');
    });
    
    // Remove translatable columns from main table
    Schema::table('custom_models', function (Blueprint $table) {
        $table->dropColumn(['title', 'description', 'content']);
    });
}
```

#### Step 2: Create Translation Model
```php
// app/Models/Translation/CustomModelTranslation.php
namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class CustomModelTranslation extends Model
{
    protected $table = 'custom_model_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
```

#### Step 3: Update Main Model
```php
// app/Models/CustomModel.php
namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class CustomModel extends Model implements TranslatableContract
{
    use Translatable;
    
    protected $table = 'custom_models';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
    
    // Define translatable attributes
    public $translatedAttributes = ['title', 'description', 'content'];
    
    // Optional: Custom accessors for translated attributes
    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }
    
    public function getDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'description');
    }
    
    public function getContentAttribute()
    {
        return getTranslateAttributeValue($this, 'content');
    }
}
```

## Implementation Details

### 1. Language Files Structure

```
lang/
├── en/                    # English (source language)
│   ├── admin/            # Admin panel translations
│   ├── api.php          # API translations
│   ├── auth.php         # Authentication translations
│   ├── cart.php         # Shopping cart translations
│   ├── categories.php   # Category translations
│   ├── financial.php    # Financial translations
│   ├── forms.php        # Form translations
│   ├── home.php         # Home page translations
│   ├── instructors.php  # Instructor translations
│   ├── navbar.php       # Navigation translations
│   ├── notification.php # Notification translations
│   ├── panel.php        # User panel translations
│   ├── product.php      # Product translations
│   ├── public.php       # Public page translations
│   ├── quiz.php         # Quiz translations
│   ├── site.php         # Site settings translations
│   ├── update.php       # Update system translations
│   ├── validation.php   # Validation translations
│   └── webinars.php     # Webinar translations
├── ar/                  # Arabic translations
├── es/                  # Spanish translations
└── [other languages]/
```

### 2. Helper Functions

#### Core Translation Functions

**`getLanguages($lang = null)`**
- Returns array of supported languages
- If `$lang` is provided, returns specific language name
- Used for language selection dropdowns

**`getDefaultLocale()`**
- Returns the default site language from settings
- Cached for 24 hours for performance

**`getTranslateAttributeValue($model, $key, $locale = null)`**
- Core function for retrieving translated content
- Handles fallback logic (default → English → first available)
- Supports admin content editing mode

**`localeToCountryCode($code, $reverse = false)`**
- Converts language codes to country codes for flag display
- Example: 'EN' → 'US', 'ES' → 'ES'

### 3. Middleware System

#### UserLocale Middleware
```php
// app/Http/Middleware/UserLocale.php
class UserLocale
{
    public function handle($request, Closure $next)
    {
        $generalSettings = getGeneralSettings();
        $defaultLocale = getDefaultLocale();
        $locale = $defaultLocale;

        // Check authenticated user language preference
        if (auth()->check()) {
            $user = auth()->user();
            $locale = !empty($user->language) ? $user->language : $defaultLocale;
        } else {
            // Check cookie for guest users
            $checkCookie = Cookie::get('user_locale');
            if (!empty($checkCookie)) {
                $locale = $checkCookie;
            }
        }

        // Validate against allowed languages
        $userLanguages = $generalSettings['user_languages'] ?? [];
        if (!in_array($locale, $userLanguages)) {
            $locale = $defaultLocale;
        }

        \Session::put('locale', mb_strtolower($locale));
        return $next($request);
    }
}
```

#### AdminLocale Middleware
- Sets locale for admin panel
- Shares general settings and default locale with views

#### API SetLocale Middleware
- Handles locale setting for API requests
- Supports `x-locale` header for language specification

### 4. Language Switching

#### Frontend Language Switcher
```php
// resources/views/web/default/includes/top_nav.blade.php
@if(!empty($localLanguage) && count($localLanguage) > 1)
    <form action="/locale" method="post" class="mr-15 mx-md-20">
        {{ csrf_field() }}
        <input type="hidden" name="locale">
        <div class="language-select">
            <div id="localItems"
                 data-selected-country="{{ localeToCountryCode(mb_strtoupper(app()->getLocale())) }}"
                 data-countries='{{ json_encode($localLanguage) }}'
            ></div>
        </div>
    </form>
@endif
```

#### Locale Controller
```php
// app/Http/Controllers/Web/LocaleController.php
class LocaleController extends Controller
{
    public function setLocale(Request $request)
    {
        $locale = $request->get('locale');
        $locale = localeToCountryCode(mb_strtoupper($locale), true);

        $generalSettings = getGeneralSettings();
        $userLanguages = $generalSettings['user_languages'] ?? [];

        if (in_array($locale, $userLanguages)) {
            if (auth()->check()) {
                // Update user's language preference
                $user = auth()->user();
                $user->update(['language' => $locale]);
            } else {
                // Set cookie for guest users
                Cookie::queue('user_locale', $locale, 30 * 24 * 60);
            }
        }

        return redirect()->back();
    }
}
```

## Content Translation System

### 1. Translatable Content Types

#### Models with Translation Support
- **Blog** - Blog posts and articles
- **Category** - Course categories
- **Page** - Static pages
- **Product** - E-commerce products
- **Webinar** - Live sessions
- **TextLesson** - Course lessons
- **Quiz** - Assessments
- **FAQ** - Frequently asked questions
- **Form** - Custom forms
- **Setting** - System settings
- **Badge** - Achievement badges
- **Testimonial** - User testimonials

### 2. Content Translation Workflow

#### Admin Content Editing
```php
// Enable content translation mode
@if(!empty(getGeneralSettings('content_translate')))
    <div class="form-group">
        <label class="input-label">{{ trans('auth.language') }}</label>
        <select name="locale" class="form-control js-edit-content-locale">
            @foreach($userLanguages as $lang => $language)
                <option value="{{ $lang }}" 
                    @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) 
                        selected 
                    @endif>
                    {{ $language }}
                </option>
            @endforeach
        </select>
    </div>
@endif
```

#### Content Storage
```php
// Store content with locale
$blog = new Blog();
$blog->translate('en')->title = 'English Title';
$blog->translate('es')->title = 'Título en Español';
$blog->save();
```

#### Content Retrieval
```php
// Automatic translation based on current locale
$title = $blog->title; // Returns translated title

// Manual translation retrieval
$title = getTranslateAttributeValue($blog, 'title', 'es');
```

### 3. Database Content Translation Examples

#### Example 1: Creating a Translatable Page

```php
// Controller method for creating a page
public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'locale' => 'required|string|in:en,es,ar,fr'
    ]);

    $page = new Page();
    
    // Store in current editing locale
    $locale = $request->get('locale', 'en');
    $page->translate($locale)->title = $request->title;
    $page->translate($locale)->content = $request->content;
    $page->translate($locale)->seo_description = $request->seo_description;
    
    // Store non-translatable fields
    $page->status = 'published';
    $page->created_at = time();
    
    $page->save();
    
    return redirect()->route('admin.pages.index')
        ->with('success', 'Page created successfully');
}
```

#### Example 2: Creating a Course with Multiple Translations

```php
// Creating a course with translations in multiple languages
public function createCourseWithTranslations()
{
    $course = new Webinar(); // Assuming Webinar model represents courses
    
    // English translation
    $course->translate('en')->title = 'Advanced Laravel Development';
    $course->translate('en')->description = 'Learn advanced Laravel techniques and best practices';
    $course->translate('en')->seo_description = 'Master Laravel framework with advanced concepts';
    
    // Spanish translation
    $course->translate('es')->title = 'Desarrollo Avanzado con Laravel';
    $course->translate('es')->description = 'Aprende técnicas avanzadas de Laravel y mejores prácticas';
    $course->translate('es')->seo_description = 'Domina el framework Laravel con conceptos avanzados';
    
    // Arabic translation
    $course->translate('ar')->title = 'تطوير متقدم بلارافيل';
    $course->translate('ar')->description = 'تعلم تقنيات لارافيل المتقدمة وأفضل الممارسات';
    $course->translate('ar')->seo_description = 'أتقن إطار عمل لارافيل مع المفاهيم المتقدمة';
    
    // Non-translatable fields
    $course->teacher_id = auth()->id();
    $course->category_id = 1;
    $course->price = 99.99;
    $course->status = 'active';
    $course->created_at = time();
    
    $course->save();
    
    return $course;
}
```

#### Example 3: Updating Existing Content with Translations

```php
// Update page content in specific language
public function updatePage(Request $request, $pageId)
{
    $page = Page::findOrFail($pageId);
    $locale = $request->get('locale', 'en');
    
    // Update translation for specific locale
    $page->translate($locale)->title = $request->title;
    $page->translate($locale)->content = $request->content;
    $page->translate($locale)->seo_description = $request->seo_description;
    
    $page->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Page updated successfully in ' . strtoupper($locale)
    ]);
}
```

#### Example 4: Bulk Translation of Existing Content

```php
// Translate existing content to new language
public function translateExistingContent($modelId, $targetLocale)
{
    $page = Page::findOrFail($modelId);
    
    // Check if translation already exists
    if (!$page->hasTranslation($targetLocale)) {
        // Get English content as source
        $englishContent = $page->translate('en');
        
        // Create new translation
        $page->translate($targetLocale)->title = $englishContent->title;
        $page->translate($targetLocale)->content = $englishContent->content;
        $page->translate($targetLocale)->seo_description = $englishContent->seo_description;
        
        $page->save();
        
        return "Translation created for locale: $targetLocale";
    }
    
    return "Translation already exists for locale: $targetLocale";
}
```

#### Example 5: Retrieving Content with Fallback

```php
// Get content with intelligent fallback
public function getPageContent($pageId, $preferredLocale = null)
{
    $page = Page::findOrFail($pageId);
    $locale = $preferredLocale ?: app()->getLocale();
    
    // Try to get content in preferred locale
    if ($page->hasTranslation($locale)) {
        $content = $page->translate($locale);
    } 
    // Fallback to default locale
    elseif ($page->hasTranslation(getDefaultLocale())) {
        $content = $page->translate(getDefaultLocale());
    }
    // Fallback to English
    elseif ($page->hasTranslation('en')) {
        $content = $page->translate('en');
    }
    // Fallback to first available translation
    else {
        $firstTranslation = $page->translations->first();
        $content = $firstTranslation ? $page->translate($firstTranslation->locale) : null;
    }
    
    return $content;
}
```

#### Example 6: Admin Interface for Content Translation

```php
// Admin controller method for editing content in multiple languages
public function editContent($contentId, Request $request)
{
    $page = Page::findOrFail($contentId);
    $editingLocale = $request->get('locale', 'en');
    
    // Store editing locale in session for admin interface
    storeContentLocale($editingLocale, 'pages', $contentId);
    
    $data = [
        'page' => $page,
        'editingLocale' => $editingLocale,
        'availableLocales' => getUserLanguagesLists(),
        'currentTranslation' => $page->translate($editingLocale)
    ];
    
    return view('admin.pages.edit', $data);
}
```

#### Example 7: Form Handling for Multi-language Content

```blade
{{-- Blade template for multi-language content editing --}}
<form action="{{ route('admin.pages.update', $page->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    {{-- Language selector --}}
    <div class="form-group">
        <label>Editing Language</label>
        <select name="locale" class="form-control js-edit-content-locale">
            @foreach($availableLocales as $code => $name)
                <option value="{{ $code }}" 
                    @if($code == $editingLocale) selected @endif>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </div>
    
    {{-- Content fields --}}
    <div class="form-group">
        <label>Title ({{ strtoupper($editingLocale) }})</label>
        <input type="text" name="title" 
               value="{{ $currentTranslation->title ?? '' }}" 
               class="form-control">
    </div>
    
    <div class="form-group">
        <label>Content ({{ strtoupper($editingLocale) }})</label>
        <textarea name="content" class="form-control" rows="10">{{ $currentTranslation->content ?? '' }}</textarea>
    </div>
    
    <div class="form-group">
        <label>SEO Description ({{ strtoupper($editingLocale) }})</label>
        <textarea name="seo_description" class="form-control">{{ $currentTranslation->seo_description ?? '' }}</textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Update Content</button>
</form>
```

#### Example 8: API Endpoint for Multi-language Content

```php
// API controller for multi-language content
public function getContent(Request $request, $contentId)
{
    $page = Page::findOrFail($contentId);
    $locale = $request->header('x-locale', app()->getLocale());
    
    // Get translated content
    $content = getTranslateAttributeValue($page, 'title', $locale);
    $description = getTranslateAttributeValue($page, 'content', $locale);
    $seoDescription = getTranslateAttributeValue($page, 'seo_description', $locale);
    
    return response()->json([
        'data' => [
            'id' => $page->id,
            'title' => $content,
            'content' => $description,
            'seo_description' => $seoDescription,
            'locale' => $locale,
            'available_locales' => $page->translations->pluck('locale')->toArray()
        ],
        'meta' => [
            'current_locale' => $locale,
            'fallback_used' => !$page->hasTranslation($locale)
        ]
    ]);
}
```

#### Example 9: Creating Translatable Blog Posts

```php
// Create blog post with translations
public function createBlogPost(Request $request)
{
    $blog = new Blog();
    
    // Get all available languages
    $languages = getUserLanguagesLists();
    
    foreach ($languages as $locale => $languageName) {
        $blog->translate($locale)->title = $request->input("title_$locale");
        $blog->translate($locale)->description = $request->input("description_$locale");
        $blog->translate($locale)->content = $request->input("content_$locale");
        $blog->translate($locale)->meta_description = $request->input("meta_description_$locale");
    }
    
    // Non-translatable fields
    $blog->author_id = auth()->id();
    $blog->category_id = $request->category_id;
    $blog->status = 'published';
    $blog->created_at = time();
    
    $blog->save();
    
    return redirect()->route('admin.blog.index')
        ->with('success', 'Blog post created in ' . count($languages) . ' languages');
}
```

#### Example 10: Querying Content by Language

```php
// Get all pages in specific language
public function getPagesByLanguage($locale = 'en')
{
    return Page::whereHas('translations', function($query) use ($locale) {
        $query->where('locale', $locale);
    })->with(['translations' => function($query) use ($locale) {
        $query->where('locale', $locale);
    }])->get();
}

// Get content with all translations
public function getContentWithAllTranslations($contentId)
{
    $page = Page::with('translations')->findOrFail($contentId);
    
    $translations = [];
    foreach ($page->translations as $translation) {
        $translations[$translation->locale] = [
            'title' => $translation->title,
            'content' => $translation->content,
            'seo_description' => $translation->seo_description
        ];
    }
    
    return [
        'id' => $page->id,
        'translations' => $translations,
        'available_locales' => array_keys($translations)
    ];
}
```

### 3. Translation Fallback System

The system implements a sophisticated fallback mechanism:

1. **Current Locale** - Try to get translation in current language
2. **Default Locale** - Fallback to site default language
3. **English** - Fallback to English if available
4. **First Available** - Use first available translation
5. **Empty String** - Return empty if no translation exists

### 4. Common Translation Patterns

#### Pattern 1: Single Language Creation
```php
// Create content in one language initially
$page = new Page();
$page->translate('en')->title = 'English Title';
$page->translate('en')->content = 'English content...';
$page->save();

// Later add translations
$page->translate('es')->title = 'Título en Español';
$page->translate('es')->content = 'Contenido en español...';
$page->save();
```

#### Pattern 2: Multi-language Form Handling
```php
// Handle form with multiple language inputs
public function storeMultiLanguage(Request $request)
{
    $page = new Page();
    $languages = ['en', 'es', 'ar'];
    
    foreach ($languages as $locale) {
        if ($request->has("title_$locale")) {
            $page->translate($locale)->title = $request->input("title_$locale");
            $page->translate($locale)->content = $request->input("content_$locale");
        }
    }
    
    $page->save();
}
```

#### Pattern 3: Conditional Translation
```php
// Only create translation if content is provided
public function updateWithConditionalTranslation(Request $request, $pageId)
{
    $page = Page::findOrFail($pageId);
    $locale = $request->get('locale');
    
    if ($request->filled('title')) {
        $page->translate($locale)->title = $request->title;
    }
    
    if ($request->filled('content')) {
        $page->translate($locale)->content = $request->content;
    }
    
    $page->save();
}
```

#### Pattern 4: Translation Status Tracking
```php
// Track which languages have translations
public function getTranslationStatus($contentId)
{
    $page = Page::with('translations')->findOrFail($contentId);
    $availableLanguages = getUserLanguagesLists();
    
    $status = [];
    foreach ($availableLanguages as $locale => $name) {
        $status[$locale] = [
            'name' => $name,
            'has_translation' => $page->hasTranslation($locale),
            'is_complete' => $this->isTranslationComplete($page, $locale)
        ];
    }
    
    return $status;
}

private function isTranslationComplete($page, $locale)
{
    if (!$page->hasTranslation($locale)) {
        return false;
    }
    
    $translation = $page->translate($locale);
    return !empty($translation->title) && !empty($translation->content);
}
```

## Translation Management

### 1. Admin Translator Tool

#### Features
- **Bulk Translation** - Translate entire language files
- **Selective Translation** - Translate specific files only
- **Google Translate Integration** - Automatic translation using Google Translate API
- **Manual Review** - Edit translations after automatic generation

#### Usage
```php
// app/Http/Controllers/Admin/TranslatorController.php
public function translate(Request $request)
{
    $language = mb_strtolower($data['language']);
    
    $translateService = (new TranslateService());
    
    if (count($specificFilesPath)) {
        // Translate specific files
        foreach ($specificFilesPath as $filePath) {
            $translateService->to($language)->from($filePath, false, 'en')->translate();
        }
    } else {
        // Translate entire language folder
        $translateService->to($language)->from('en')->translate();
    }
}
```

### 2. Translation Service

#### Google Translate Integration
```php
// app/Mixins/Lang/TranslateService.php
private function setUpGoogleTranslate(): GoogleTranslate
{
    $google = new GoogleTranslate();
    return $google->setSource($this->translate_from)
        ->setTarget($this->translate_to);
}

private function translateRecursive($content, $google): array
{
    $trans_data = [];
    
    foreach ($content as $key => $value) {
        if (!is_array($value)) {
            $trans_data[$key] = $google->translate($value);
        } else {
            $trans_data[$key] = $this->translateRecursive($value, $google);
        }
    }
    
    return $trans_data;
}
```

### 3. Language File Management

#### Creating New Language Files
1. Copy English files to new language folder
2. Translate content manually or use translator tool
3. Update language settings in admin panel

#### Adding New Translation Keys
1. Add key to English language file
2. Run translator tool to generate translations
3. Review and edit generated translations

## Configuration

### 1. Admin Settings

#### Basic Settings
- **Site Language**: Set default application language
- **User Languages**: Configure available languages for users
- **Content Translation**: Enable/disable content translation features

#### Language Configuration
```php
// Example settings structure
$settings = [
    'site_language' => 'EN',
    'user_languages' => ['EN', 'ES', 'AR', 'FR'],
    'content_translate' => true
];
```

### 2. Translatable Configuration

#### config/translatable.php
```php
return [
    'locales' => ['en', 'es', 'ar', 'fr'],
    'locale_separator' => '-',
    'use_fallback' => false,
    'use_property_fallback' => true,
    'fallback_locale' => 'en',
    'translation_model_namespace' => 'App\Models\Translation',
    'translation_suffix' => 'Translation'
];
```

## Best Practices

### 1. Content Management

#### Creating Translatable Content
```php
// Good practice - Always provide translations
$blog = new Blog();
$blog->translate('en')->title = 'English Title';
$blog->translate('es')->title = 'Título en Español';
$blog->save();

// Avoid - Missing translations
$blog->title = 'English Title'; // Only English
```

#### Retrieving Content
```php
// Use helper function for consistent behavior
$title = getTranslateAttributeValue($blog, 'title');

// Or use model accessor
$title = $blog->title; // Automatic translation
```

### 2. Language File Organization

#### File Structure
- Keep related translations in separate files
- Use descriptive file names
- Group admin translations in `admin/` folder

#### Translation Keys
```php
// Use descriptive, hierarchical keys
return [
    'course' => [
        'title' => 'Course Title',
        'description' => 'Course Description',
        'lessons' => [
            'title' => 'Lessons',
            'count' => 'Lesson Count'
        ]
    ]
];
```

### 3. Performance Optimization

#### Caching
- Language settings are cached for 24 hours
- Translation files are cached by Laravel
- Use Redis/Memcached for better performance

#### Database Optimization
- Index translation tables on `locale` column
- Use eager loading for related translations
- Consider denormalization for frequently accessed content

## Troubleshooting

### 1. Common Issues

#### Missing Translations
```php
// Check if translation exists
if ($model->hasTranslation('es')) {
    $title = $model->translate('es')->title;
} else {
    $title = $model->translate('en')->title; // Fallback
}
```

#### Language Not Switching
- Check middleware configuration
- Verify language is in `user_languages` array
- Clear application cache

#### Translation Not Saving
- Ensure model implements `TranslatableContract`
- Check `$translatedAttributes` array
- Verify translation table exists

### 2. Debug Tools

#### Translation Debugging
```php
// Enable translation debugging
config(['app.debug' => true]);

// Check current locale
dd(app()->getLocale());

// List available translations
dd($model->translations);
```

#### Language File Validation
```php
// Validate language file syntax
php artisan lang:check

// Clear language cache
php artisan lang:clear
```

## API Integration

### 1. API Language Support

#### Request Headers
```http
X-Locale: ES
Accept-Language: es-ES,es;q=0.9,en;q=0.8
```

#### Response Format
```json
{
    "data": {
        "title": "Título del Curso",
        "description": "Descripción del curso"
    },
    "meta": {
        "locale": "es"
    }
}
```

### 2. API Translation Middleware
```php
// app/Http/Middleware/Api/SetLocale.php
public function handle($request, Closure $next)
{
    $locale = $request->header('x-locale', getDefaultLocale());
    
    if (in_array($locale, $userLanguages)) {
        App::setLocale(strtolower($locale));
    }
    
    return $next($request);
}
```

## Security Considerations

### 1. Input Validation
- Validate locale parameters
- Sanitize translation content
- Prevent XSS in translated content

### 2. Access Control
- Restrict translation management to authorized users
- Validate language permissions
- Audit translation changes

## Future Enhancements

### 1. Planned Features
- **Translation Memory** - Reuse existing translations
- **Machine Learning** - Improve automatic translation quality
- **Translation Workflow** - Review and approval process
- **Version Control** - Track translation changes

### 2. Performance Improvements
- **Lazy Loading** - Load translations on demand
- **CDN Integration** - Cache translations globally
- **Database Optimization** - Improve query performance

## Conclusion

The multilingual system in Rocket LMS provides a robust, scalable solution for internationalization. It supports both interface and content translation, with comprehensive admin tools for management. The system is designed to be maintainable, performant, and user-friendly while following Laravel best practices.

For questions or support, refer to the Laravel documentation on localization and the Astrotomic Translatable package documentation.
