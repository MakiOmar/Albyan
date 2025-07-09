# Laravel Upgrade Guide for Rocket LMS

This guide will help you upgrade your Rocket LMS from Laravel 9.19 to the latest Laravel version safely.

## 📊 **Current Status**

- **Current Version**: Laravel 9.19
- **Latest Available**: Laravel 12.19.3
- **Recommended Target**: Laravel 11.x
- **PHP Requirement**: 8.1+ (✅ You have 8.2.13)

## 🎯 **Recommended Upgrade Path**

```
Laravel 9.19 → Laravel 10.x → Laravel 11.x
```

**Why this path?**
- ✅ Gradual upgrade reduces risk
- ✅ Better package compatibility
- ✅ Easier troubleshooting
- ✅ Laravel 11 is the current LTS version

## 🔧 **Pre-Upgrade Checklist**

### **1. Backup Everything**
```bash
# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup code
tar -czf rocket-lms-backup-$(date +%Y%m%d_%H%M%S).tar.gz .

# Backup .env file
cp .env .env.backup
```

### **2. Check PHP Version**
```bash
php -v
# Ensure you have PHP 8.1+ (you have 8.2.13 ✅)
```

### **3. Check Current Dependencies**
```bash
composer show --installed | grep laravel
```

## 🚀 **Step 1: Upgrade to Laravel 10**

### **1.1 Update composer.json**
```json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "laravel/sanctum": "^3.2",
        "laravel/socialite": "^5.8",
        "laravel/tinker": "^2.8"
    },
    "require-dev": {
        "fakerphp/faker": "^1.9.1",
        "laravel/pint": "^1.0",
        "laravel/sail": "^1.18",
        "mockery/mockery": "^1.4.4",
        "nunomaduro/collision": "^7.0",
        "phpunit/phpunit": "^10.0",
        "spatie/laravel-ignition": "^2.0"
    }
}
```

### **1.2 Run the Upgrade**
```bash
# Update Laravel framework
composer update laravel/framework laravel/sanctum laravel/socialite laravel/tinker --with-all-dependencies

# Update dev dependencies
composer update --dev --with-all-dependencies

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### **1.3 Update Application Files**
```bash
# Publish new Laravel 10 files
php artisan vendor:publish --tag=laravel-assets --force

# Update bootstrap/app.php (if using Laravel 11 structure)
# Update config files
# Update middleware
```

### **1.4 Fix Breaking Changes**
- Update deprecated method calls
- Fix middleware changes
- Update service providers
- Fix route changes

## 🚀 **Step 2: Upgrade to Laravel 11**

### **2.1 Update composer.json**
```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "laravel/socialite": "^5.11",
        "laravel/tinker": "^2.9"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.26",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.0",
        "phpunit/phpunit": "^10.5",
        "spatie/laravel-ignition": "^2.4"
    }
}
```

### **2.2 Run the Upgrade**
```bash
# Update to Laravel 11
composer update laravel/framework laravel/sanctum laravel/socialite laravel/tinker --with-all-dependencies

# Update dev dependencies
composer update --dev --with-all-dependencies

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### **2.3 Update Application Structure**
```bash
# Update to new application structure
php artisan vendor:publish --tag=laravel-assets --force

# Update bootstrap/app.php for new structure
# Update service providers
# Update middleware
```

## 🔍 **Package Compatibility Check**

### **Critical Packages to Update**
```bash
# Update these packages for Laravel 11 compatibility
composer update spatie/laravel-sitemap:^7.0
composer update astrotomic/laravel-translatable:^12.0
composer update cviebrock/eloquent-sluggable:^9.0
composer update barryvdh/laravel-debugbar:^3.9
composer update intervention/image:^3.0
```

### **Potential Incompatible Packages**
- `fideloper/proxy` (deprecated, use `fruitcake/laravel-cors`)
- `tymon/jwt-auth` (may need updates)
- `unisharp/laravel-filemanager` (check compatibility)
- Custom payment gateways (may need updates)

## 🛠️ **Common Issues & Solutions**

### **1. Service Provider Changes**
```php
// Laravel 11: Update bootstrap/app.php
$app->register(\App\Providers\AppServiceProvider::class);
$app->register(\App\Providers\AuthServiceProvider::class);
$app->register(\App\Providers\RouteServiceProvider::class);
```

### **2. Middleware Updates**
```php
// Update middleware in routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Your routes
});
```

### **3. Database Changes**
```bash
# Run migrations
php artisan migrate

# Check for failed migrations
php artisan migrate:status
```

### **4. Cache & Session Issues**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

## 🧪 **Testing After Upgrade**

### **1. Basic Functionality Tests**
```bash
# Test basic Laravel functionality
php artisan --version
php artisan route:list
php artisan config:cache
php artisan view:cache
```

### **2. Application-Specific Tests**
- Test user registration/login
- Test course creation/editing
- Test payment gateways
- Test file uploads
- Test email sending
- Test sitemap generation

### **3. Database Tests**
```bash
# Test database connections
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

## 📊 **Performance Optimization**

### **After Upgrade Optimizations**
```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Clear old caches
php artisan optimize:clear
```

### **New Laravel 11 Features**
- Improved performance
- Better error handling
- Enhanced security
- Modern PHP 8.2+ features

## 🚨 **Rollback Plan**

If something goes wrong:

### **1. Database Rollback**
```bash
# Rollback migrations
php artisan migrate:rollback --step=1

# Or restore from backup
mysql -u username -p database_name < backup_file.sql
```

### **2. Code Rollback**
```bash
# Restore from backup
tar -xzf rocket-lms-backup-DATE.tar.gz

# Restore .env
cp .env.backup .env
```

### **3. Composer Rollback**
```bash
# Rollback to previous version
composer require laravel/framework:^9.19 --with-all-dependencies
```

## 📋 **Post-Upgrade Checklist**

- [ ] All routes working
- [ ] Database migrations successful
- [ ] User authentication working
- [ ] File uploads working
- [ ] Payment gateways working
- [ ] Email sending working
- [ ] Sitemap generation working
- [ ] Admin panel accessible
- [ ] Course creation/editing working
- [ ] Search functionality working
- [ ] Performance acceptable
- [ ] Error logs clean

## 🆘 **Support & Troubleshooting**

### **Common Error Solutions**

#### **Class Not Found Errors**
```bash
composer dump-autoload
php artisan clear-compiled
composer install
```

#### **Database Connection Errors**
```bash
php artisan config:clear
php artisan cache:clear
# Check .env database settings
```

#### **Permission Errors**
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### **Useful Commands**
```bash
# Check Laravel version
php artisan --version

# Check PHP version
php -v

# Check installed packages
composer show --installed

# Check for outdated packages
composer outdated

# Generate new application key
php artisan key:generate
```

## 📞 **Getting Help**

If you encounter issues:
1. Check Laravel upgrade guide: https://laravel.com/docs/upgrade
2. Check package compatibility
3. Review error logs: `storage/logs/laravel.log`
4. Test in staging environment first
5. Consider professional help for complex upgrades

---

**Last Updated**: January 2024  
**Laravel Version**: 9.19 → 11.x  
**PHP Version**: 8.2.13 ✅ 