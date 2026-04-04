# Rocket LMS SEO Setup Guide

This guide will help you set up SEO features (sitemap.xml and robots.txt) for your live Rocket LMS website.

## 📋 Prerequisites

- Rocket LMS installed and configured
- Access to your server via SSH or file manager
- Domain name configured and pointing to your server
- SSL certificate installed (recommended)

## 🚀 Step 1: Install Required Package

### Via SSH (Recommended)
```bash
# Navigate to your project directory
cd /path/to/your/rocket-lms

# Install the sitemap package
composer require spatie/laravel-sitemap
```

### Via File Manager
1. Upload the `composer.json` and `composer.lock` files
2. Run `composer install` via SSH or hosting control panel

## 📁 Step 2: Verify Files Are in Place

Ensure these files exist in your project:

```
rocket-lms/
├── app/Http/Controllers/SitemapController.php
├── app/Console/Commands/GenerateSitemap.php
├── app/Console/Kernel.php (updated)
├── routes/web.php (updated)
└── public/robots.txt
```

## ⚙️ Step 3: Configure Your Domain

### Update robots.txt
Edit `public/robots.txt` and replace all instances of:
```
https://albyan.institute
```
with your actual domain:
```
https://yourdomain.com
```

### Update SitemapController (Optional)
If your URL structure differs, edit `app/Http/Controllers/SitemapController.php`:

```php
// Update these URL patterns if needed
$url = '/course/' . $course->slug;        // Your course URL pattern
$url = '/blog/' . $post->slug;            // Your blog URL pattern
$url = '/upcoming-course/' . $course->slug; // Your upcoming course URL pattern
```

## 🔧 Step 4: Set Up Cron Job (Important!)

### For cPanel Hosting:
1. Go to **cPanel > Cron Jobs**
2. Add this command:
```bash
* * * * * cd /path/to/your/rocket-lms && php artisan schedule:run >> /dev/null 2>&1
```

### For VPS/Dedicated Server:
1. SSH into your server
2. Edit crontab:
```bash
crontab -e
```
3. Add this line:
```bash
* * * * * cd /path/to/your/rocket-lms && php artisan schedule:run >> /dev/null 2>&1
```

### For Shared Hosting (No Cron Access):
Set up a web-based cron service like:
- [EasyCron](https://www.easycron.com/)
- [Cron-job.org](https://cron-job.org/)
- [SetCronJob](https://www.setcronjob.com/)

URL to call: `https://yourdomain.com/artisan/schedule/run`

## 🧪 Step 5: Test Your Setup

### Test Sitemap Generation
```bash
# Generate all sitemaps
php artisan sitemap:generate

# Test specific sitemaps
php artisan sitemap:generate courses
php artisan sitemap:generate blog
php artisan sitemap:generate upcoming-courses
```

### Test Sitemap URLs
Visit these URLs in your browser:
- `https://yourdomain.com/sitemap.xml`
- `https://yourdomain.com/sitemap-courses.xml`
- `https://yourdomain.com/sitemap-blog.xml`
- `https://yourdomain.com/sitemap-upcoming-courses.xml`

### Test robots.txt
Visit: `https://yourdomain.com/robots.txt`

## 🔍 Step 6: Submit to Search Engines

### Google Search Console
1. Go to [Google Search Console](https://search.google.com/search-console)
2. Add your property if not already added
3. Go to **Sitemaps** section
4. Submit: `https://yourdomain.com/sitemap.xml`

### Bing Webmaster Tools
1. Go to [Bing Webmaster Tools](https://www.bing.com/webmasters)
2. Add your site if not already added
3. Go to **Sitemaps** section
4. Submit: `https://yourdomain.com/sitemap.xml`

### Yandex Webmaster
1. Go to [Yandex Webmaster](https://webmaster.yandex.com/)
2. Add your site
3. Submit sitemap URL

## 📊 Step 7: Monitor Performance

### Check Sitemap Status
- Google Search Console > Sitemaps > Check for errors
- Monitor indexed pages count
- Check for crawl errors

### Monitor robots.txt
- Google Search Console > Coverage > Check for blocked URLs
- Ensure important pages aren't accidentally blocked

## 🔧 Step 8: Customization Options

### Add More Content Types
Edit `app/Http/Controllers/SitemapController.php`:

```php
// Add new content types
private function addNewContentType($sitemap)
{
    $items = YourModel::where('status', 'published')->get();
    
    foreach ($items as $item) {
        $url = '/your-url/' . $item->slug;
        
        $sitemap->add(
            Url::create($url)
                ->setPriority(0.7)
                ->setChangeFrequency('weekly')
                ->setLastModificationDate($item->updated_at)
        );
    }
}
```

### Customize robots.txt
Edit `public/robots.txt` to:
- Add/remove blocked directories
- Change crawl delay
- Add more file type restrictions

### Adjust Sitemap Priorities
In `SitemapController.php`, modify priority values:
- `1.0` = Highest priority (homepage)
- `0.9` = Very important (courses, main pages)
- `0.8` = Important (blog posts, categories)
- `0.7` = Medium (instructors, tags)
- `0.6` = Lower (about, contact pages)

## 🚨 Troubleshooting

### Sitemap Not Generating
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear

# Check permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### Cron Job Not Working
```bash
# Test cron manually
php artisan schedule:run

# Check if cron is running
ps aux | grep cron

# Test sitemap generation manually
php artisan sitemap:generate
```

### robots.txt Not Accessible
- Ensure file is in `public/` directory
- Check file permissions: `chmod 644 public/robots.txt`
- Clear web server cache if using caching

### Search Engines Not Indexing
- Wait 24-48 hours for initial indexing
- Check Google Search Console for crawl errors
- Ensure your site isn't blocked by robots.txt
- Verify sitemap is accessible and valid

### DateTimeInterface Error
If you get this error:
```
Spatie\Sitemap\Tags\Url::setLastModificationDate(): Argument #1 ($lastModificationDate) must be of type DateTimeInterface, string given
```

**Solution**: The error occurs because the sitemap package expects DateTime objects, not strings. This has been fixed in the SitemapController by using:
```php
// Instead of: date('Y-m-d', $course->updated_at)
// Use: Carbon::createFromTimestamp($course->updated_at)
```

**Status**: ✅ **Fixed** - The SitemapController now properly converts timestamps to Carbon DateTime objects.

### Large Dataset Issues (280+ Courses)
If you have many courses and experience issues:

#### Memory Limit Errors
```bash
# Increase PHP memory limit in php.ini
memory_limit = 512M

# Or temporarily increase via .htaccess
php_value memory_limit 512M
```

#### Sitemap Generation Timeout
```bash
# Increase max execution time
max_execution_time = 300

# Or via .htaccess
php_value max_execution_time 300
```

#### File Size Too Large
- Use paginated sitemaps: Submit `sitemap-courses-index.xml` instead of `sitemap-courses.xml`
- Check sitemap file sizes: Keep individual sitemaps under 50MB
- Monitor with: `ls -lh public/sitemap*.xml`

#### Database Performance
```sql
-- Add indexes for better performance
ALTER TABLE webinars ADD INDEX idx_status_updated (status, updated_at);
ALTER TABLE blogs ADD INDEX idx_status_updated (status, updated_at);
ALTER TABLE upcoming_courses ADD INDEX idx_status_updated (status, updated_at);
```

#### Monitoring Large Sitemaps
```bash
# Check sitemap file sizes
du -h public/sitemap*.xml

# Count URLs in sitemap
grep -c "<url>" public/sitemap-courses.xml

# Monitor memory usage during generation
php -d memory_limit=512M artisan sitemap:generate courses
```

## 📈 Performance Tips

### Optimize Sitemap Generation
```php
// In SitemapController.php, add pagination for large datasets
$courses = Webinar::where('status', Webinar::$active)
    ->chunk(100, function ($chunk) use ($sitemap) {
        foreach ($chunk as $course) {
            // Add to sitemap
        }
    });
```

### Handle Large Datasets (280+ Courses)
For websites with large numbers of courses, the system now includes:

1. **Chunked Processing**: Courses are processed in chunks of 100 to prevent memory issues
2. **Paginated Sitemaps**: For very large datasets, courses are split into multiple sitemap files
3. **Sitemap Index**: A master sitemap that references all paginated sitemaps

#### Available URLs for Large Datasets:
- `https://yourdomain.com/sitemap-courses-index.xml` - Master sitemap index
- `https://yourdomain.com/sitemap-courses-page-1.xml` - First 1000 courses
- `https://yourdomain.com/sitemap-courses-page-2.xml` - Next 1000 courses
- And so on...

#### When to Use Paginated Sitemaps:
- **280-1000 courses**: Use the main `sitemap-courses.xml` (optimized with chunking)
- **1000+ courses**: Submit `sitemap-courses-index.xml` to search engines

#### Memory Optimization Features:
- **Chunked Database Queries**: Processes courses in batches of 100
- **24-Hour Caching**: Sitemaps are cached to reduce server load
- **Efficient Memory Usage**: Prevents PHP memory limit issues
- **Separate Content Types**: Different sitemaps for courses, blog, and upcoming courses

### Cache Configuration
```php
// In .env file
CACHE_DRIVER=redis  # or memcached for better performance
```

### Regular Maintenance
- Monitor sitemap file sizes (keep under 50MB)
- Split large sitemaps if needed
- Update robots.txt when adding new private areas
- Review blocked URLs periodically

## 📞 Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify file permissions
3. Test commands manually
4. Check server error logs
5. Contact your hosting provider if cron issues persist

## ✅ Checklist

- [ ] Package installed via composer
- [ ] All files uploaded to server
- [ ] Domain updated in robots.txt
- [ ] Cron job configured
- [ ] Sitemaps accessible via browser
- [ ] robots.txt accessible
- [ ] Submitted to Google Search Console
- [ ] Submitted to Bing Webmaster Tools
- [ ] Tested manual sitemap generation
- [ ] Monitored for errors

---

**Last Updated**: June 2024  
**Rocket LMS Version**: Compatible with all versions  
**PHP Version**: 7.4+  
**Laravel Version**: 8.0+ 