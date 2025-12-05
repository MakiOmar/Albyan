# Sitemap Documentation

This document provides comprehensive information about the sitemap functionality in the Rocket LMS application.

## Overview

The sitemap system generates XML sitemaps dynamically from your database content. Sitemaps help search engines discover and index all pages on your website, improving SEO performance.

## Available Routes

### Main Sitemap
- **URL**: `/sitemap.xml`
- **Description**: Complete sitemap including all content types
- **Includes**:
  - Static pages (home, courses, blog, instructors, etc.)
  - Published courses/webinars
  - Published blog posts
  - Upcoming courses
  - Categories
  - Blog categories
  - Instructors with published courses

### Content-Specific Sitemaps

#### Courses Sitemap
- **URL**: `/sitemap-courses.xml`
- **Description**: Contains only published courses/webinars
- **Use Case**: Submit to search engines if you want to focus on course indexing

#### Blog Sitemap
- **URL**: `/sitemap-blog.xml`
- **Description**: Contains only published blog posts
- **Use Case**: Submit to search engines for blog content indexing

#### Upcoming Courses Sitemap
- **URL**: `/sitemap-upcoming-courses.xml`
- **Description**: Contains only published upcoming courses
- **Use Case**: Submit to search engines for upcoming course indexing

### Paginated Sitemaps (for large datasets)

#### Courses Sitemap Index
- **URL**: `/sitemap-courses-index.xml`
- **Description**: Master index listing all paginated course sitemaps
- **Use Case**: Use when you have 1000+ courses and need pagination

#### Paginated Courses Sitemaps
- **URL**: `/sitemap-courses-page-{page}.xml`
- **Examples**: 
  - `/sitemap-courses-page-1.xml` (courses 1-1000)
  - `/sitemap-courses-page-2.xml` (courses 1001-2000)
  - `/sitemap-courses-page-3.xml` (courses 2001-3000)
- **Description**: Each page contains up to 1000 courses
- **Use Case**: Automatically generated when you have large numbers of courses

## How It Works

### Generation Process

1. **Dynamic Generation**: Sitemaps are generated on-demand when accessed
2. **Database Queries**: Content is fetched from the database using optimized queries
3. **Chunking**: Large datasets are processed in chunks of 100 to prevent memory issues
4. **Caching**: Generated sitemaps are cached for 24 hours (86400 seconds) for performance

### Caching

- **Cache Duration**: 24 hours
- **Cache Keys**:
  - `sitemap.xml` - Main sitemap
  - `sitemap-courses.xml` - Courses sitemap
  - `sitemap-blog.xml` - Blog sitemap
  - `sitemap-upcoming-courses.xml` - Upcoming courses sitemap
  - `sitemap-courses-index.xml` - Courses index
  - `sitemap-courses-page-{page}.xml` - Paginated course sitemaps

### Content Filtering

- **Courses**: Only includes courses with `status = active` and excludes `type = text_lesson`
- **Blog Posts**: Only includes posts with `status = publish`
- **Upcoming Courses**: Only includes courses with `status = active`
- **Instructors**: Only includes instructors with `role_name = instructor` who have published courses

## Usage

### Accessing Sitemaps

Simply visit the sitemap URL in your browser or use it in search engine submission:

```
https://yourdomain.com/sitemap.xml
https://yourdomain.com/sitemap-courses.xml
https://yourdomain.com/sitemap-blog.xml
```

### Submitting to Search Engines

#### Google Search Console
1. Go to [Google Search Console](https://search.google.com/search-console)
2. Select your property
3. Navigate to **Sitemaps** in the left menu
4. Enter `sitemap.xml` and click **Submit**

#### Bing Webmaster Tools
1. Go to [Bing Webmaster Tools](https://www.bing.com/webmasters)
2. Select your site
3. Navigate to **Sitemaps**
4. Submit your sitemap URL

### Clearing Cache

To force regeneration of sitemaps (useful after adding new content):

```bash
# Clear all cache
php artisan cache:clear

# Or use the dedicated sitemap command
php artisan sitemap:generate
```

### Sitemap Generation Command

The application includes an Artisan command to regenerate sitemaps:

```bash
# Generate all sitemaps
php artisan sitemap:generate

# Generate specific sitemap
php artisan sitemap:generate courses
php artisan sitemap:generate blog
php artisan sitemap:generate upcoming-courses
```

## Technical Details

### Controller Location
- **File**: `app/Http/Controllers/SitemapController.php`
- **Namespace**: `App\Http\Controllers`

### Routes Location
- **File**: `routes/web.php`
- **Lines**: 477-484

### Dependencies
- No external packages required (custom XML generator)
- Uses Laravel's built-in caching system
- Uses Carbon for date formatting

### XML Format

The sitemap follows the [Sitemap Protocol 0.9](https://www.sitemaps.org/protocol.html):

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://yourdomain.com/course/example-course</loc>
    <lastmod>2024-01-15T10:30:00+00:00</lastmod>
    <priority>0.8</priority>
    <changefreq>weekly</changefreq>
  </url>
</urlset>
```

### Priority Values

- **1.0**: Homepage
- **0.9**: Main category pages (courses, webinars)
- **0.8**: Individual courses, blog listing
- **0.7**: Blog posts, categories
- **0.6**: Categories, instructors, upcoming courses
- **0.5**: Blog categories, terms, privacy

### Change Frequency

- **daily**: Homepage, main pages
- **weekly**: Courses, categories, instructors
- **monthly**: Blog posts, static pages

## Performance Optimization

### Memory Management
- Uses chunking (100 records per chunk) to prevent memory exhaustion
- Processes large datasets efficiently

### Caching Strategy
- 24-hour cache reduces database load
- Cache is automatically invalidated after expiration
- Manual cache clearing available via Artisan command

### Pagination
- For sites with 1000+ courses, paginated sitemaps are automatically available
- Each paginated sitemap contains maximum 1000 URLs (Google's recommendation)

## Troubleshooting

### Sitemap Returns PHP Code Instead of XML

**Problem**: Browser shows `<?php` or PHP code instead of XML

**Solution**:
1. Check that `public/sitemap.xml` file doesn't exist (static files take precedence)
2. Clear cache: `php artisan cache:clear`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify routes are properly registered

### Sitemap is Empty

**Possible Causes**:
- No published content in database
- Cache contains empty sitemap
- Database connection issues

**Solution**:
1. Verify you have published courses/blog posts
2. Clear cache: `php artisan cache:clear`
3. Check database connectivity
4. Review Laravel logs for errors

### Sitemap Not Updating

**Problem**: New content not appearing in sitemap

**Solution**:
1. Clear cache: `php artisan cache:clear`
2. Wait for cache expiration (24 hours)
3. Manually regenerate: `php artisan sitemap:generate`

### Memory Errors

**Problem**: PHP memory limit exceeded

**Solution**:
- The system already uses chunking to prevent this
- If issues persist, increase PHP memory limit in `php.ini`
- Consider using paginated sitemaps for very large datasets

## Maintenance

### Regular Tasks

1. **Monitor Sitemap Size**: Keep individual sitemaps under 50MB (Google's limit)
2. **Check Cache Performance**: Monitor cache hit rates
3. **Review Content**: Ensure all important pages are included
4. **Update Priorities**: Adjust priority values if needed based on SEO strategy

### Best Practices

1. **Submit Main Sitemap**: Submit `/sitemap.xml` to search engines
2. **Use Paginated Sitemaps**: For large sites, submit the index sitemap
3. **Regular Updates**: Clear cache after major content updates
4. **Monitor Search Console**: Check for sitemap errors in Google Search Console

## Configuration

### App URL

The sitemap uses `config('app.url')` for generating absolute URLs. Ensure this is set correctly in your `.env` file:

```env
APP_URL=https://albyaninstitute.com
```

### Cache Driver

The sitemap uses Laravel's default cache driver. Configure in `.env`:

```env
CACHE_DRIVER=file  # or redis, memcached, etc.
```

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review this documentation
3. Check search engine sitemap validation tools
4. Contact your development team

## Version History

- **v1.0**: Initial implementation with custom XML generator
- Removed dependency on Spatie Laravel Sitemap package
- Added error handling and proper XML generation
- Implemented caching for performance

