# Laravel Upgrade - Phase 1: Preparation & Fixes

## 🎯 **Phase 1 Goal: Fix Current Issues & Prepare for Upgrade**

This phase focuses on fixing existing issues and updating packages that are safe to update before the major Laravel upgrade.

## 🔧 **Step 1: Fix Sodium Extension Issue**

### **Problem:**
```
Cannot use lcobucci/jwt 4.3.0 as it requires ext-sodium * which is missing from your platform.
```

### **Solution Options:**

#### **Option A: Enable Sodium Extension (Recommended)**
```bash
# For WAMP/XAMPP, edit php.ini
# Find and uncomment this line:
extension=sodium

# Restart your web server
```

#### **Option B: Use Compatibility Package**
```bash
composer require paragonie/sodium_compat_ext_sodium
```

#### **Option C: Update JWT Package**
```bash
composer update tymon/jwt-auth --with-all-dependencies
```

## 🔧 **Step 2: Update Safe Packages**

### **Critical Security Updates:**
```bash
# Update these packages first (safe updates)
composer update guzzlehttp/guzzle
composer update aws/aws-sdk-php
composer update twilio/sdk
composer update vonage/vonage-laravel
composer update xendit/xendit-php
```

### **Performance & Bug Fix Updates:**
```bash
# Update these packages for better performance
composer update barryvdh/laravel-debugbar
composer update barryvdh/laravel-dompdf
composer update intervention/image
composer update cviebrock/eloquent-sluggable
composer update astrotomic/laravel-translatable
```

### **Development Dependencies:**
```bash
# Update dev packages
composer update --dev fakerphp/faker
composer update --dev nunomaduro/collision
composer update --dev spatie/laravel-ignition
```

## 🔧 **Step 3: Replace Abandoned Packages**

### **Replace fruitcake/laravel-cors:**
```bash
# Remove abandoned package
composer remove fruitcake/laravel-cors

# Install replacement (if needed)
composer require fruitcake/laravel-cors:^4.0
```

### **Update Payment Gateway Packages:**
```bash
# Update these payment packages
composer update toshy/bunnynet-php
composer update shetabit/payment
composer update bitpay/sdk
composer update braintree/braintree_php
```

## 🔧 **Step 4: Test Current Functionality**

### **Before Proceeding, Test:**
```bash
# Test basic Laravel functionality
php artisan --version
php artisan route:list
php artisan config:cache

# Test database
php artisan migrate:status

# Test sitemap (our recent work)
php artisan sitemap:generate
```

### **Manual Testing Checklist:**
- [ ] User registration/login works
- [ ] Course creation/editing works
- [ ] Payment gateways work
- [ ] File uploads work
- [ ] Admin panel accessible
- [ ] Sitemap generation works
- [ ] Email sending works

## 🔧 **Step 5: Backup Everything**

### **Create Complete Backup:**
```bash
# Backup database
mysqldump -u root -p rocket_lms > backup_before_upgrade.sql

# Backup code
tar -czf rocket-lms-backup-$(date +%Y%m%d_%H%M%S).tar.gz .

# Backup .env
cp .env .env.backup

# Backup composer files
cp composer.json composer.json.backup
cp composer.lock composer.lock.backup
```

## 🔧 **Step 6: Update composer.json**

### **Update to Laravel 10 Requirements:**
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

## 🧪 **Testing After Phase 1**

### **Run These Tests:**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Test basic functionality
php artisan --version
php artisan route:list

# Test database
php artisan migrate:status

# Test sitemap
php artisan sitemap:generate
```

### **Check for Errors:**
```bash
# Check error logs
tail -f storage/logs/laravel.log

# Check for any broken functionality
# Test admin panel, user registration, course creation
```

## ✅ **Phase 1 Success Criteria**

- [ ] Sodium extension issue resolved
- [ ] All safe package updates completed
- [ ] Abandoned packages replaced
- [ ] All functionality working
- [ ] Complete backup created
- [ ] No errors in logs
- [ ] Ready for Laravel 10 upgrade

## 🚨 **If Issues Occur**

### **Rollback Plan:**
```bash
# Restore from backup
cp composer.json.backup composer.json
cp composer.lock.backup composer.lock
composer install

# Restore .env
cp .env.backup .env

# Clear caches
php artisan cache:clear
php artisan config:clear
```

### **Common Issues & Solutions:**

#### **Package Conflicts:**
```bash
composer update --with-all-dependencies
```

#### **Class Not Found Errors:**
```bash
composer dump-autoload
php artisan clear-compiled
```

#### **Database Issues:**
```bash
php artisan migrate:status
php artisan migrate --force
```

---

**Next Phase**: Laravel 9 → 10 Upgrade  
**Estimated Time**: 2-4 hours  
**Risk Level**: Low 