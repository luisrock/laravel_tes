# Testing the Laravel 9 Upgrade

This document provides comprehensive testing strategies to validate the Laravel 8 to Laravel 9 upgrade.

## Quick Start

```bash
# 1. Update dependencies first
composer update

# 2. Run automated tests
php artisan test

# 3. Run specific Laravel 9 upgrade tests
php artisan test --filter=Laravel9Upgrade

# 4. Manual verification (see sections below)
```

---

## Automated Testing

### Run All Tests

```bash
# Run the full test suite
php artisan test

# Or using PHPUnit directly
./vendor/bin/phpunit
```

### Run Specific Test Suites

```bash
# Run only feature tests
php artisan test --testsuite=Feature

# Run only unit tests
php artisan test --testsuite=Unit

# Run Laravel 9 upgrade specific tests
php artisan test --filter=Laravel9Upgrade
```

---

## Critical Areas to Test

### 1. ✅ Dependency Installation

**What Changed:** All dependencies updated to Laravel 9 compatible versions

**Test:**
```bash
# Verify composer.lock is generated correctly
composer update

# Check for any dependency conflicts
composer why-not laravel/framework 9.0

# Verify all packages installed
composer show | grep -E "(laravel|spatie|league)"
```

**Expected Output:**
- ✓ laravel/framework: 9.x
- ✓ spatie/laravel-ignition: 1.x
- ✓ league/flysystem-aws-s3-v3: 3.x
- ✓ nunomaduro/collision: 6.x
- ✓ No facade/ignition
- ✓ No fideloper/proxy

---

### 2. ✅ TrustProxies Middleware

**What Changed:** Migrated from `fideloper/proxy` to built-in Laravel middleware

**Automated Test:**
```bash
php artisan test --filter=TrustProxiesTest
```

**Manual Test:**
```bash
# 1. Check middleware is properly loaded
php artisan route:list

# 2. Verify headers are being set correctly
# Access your app through a proxy and check the X-Forwarded headers
curl -H "X-Forwarded-For: 203.0.113.1" https://your-app.test/
```

**Expected Behavior:**
- ✓ Middleware loads without errors
- ✓ Proxy headers are properly processed
- ✓ No "Class not found" errors for Fideloper\Proxy

---

### 3. ✅ File Storage (Flysystem 3.x)

**What Changed:** Upgraded from Flysystem 1.x to 3.x

**Automated Test:**
```bash
php artisan test --filter=FilesystemTest
```

**Manual Test:**
```bash
# Test local storage
php artisan tinker
```

Then in Tinker:
```php
// Test file write
Storage::disk('local')->put('test.txt', 'Laravel 9 test');

// Test file read
Storage::disk('local')->get('test.txt');

// Test file exists
Storage::disk('local')->exists('test.txt');

// Test file delete
Storage::disk('local')->delete('test.txt');

// If using S3, test it too
Storage::disk('s3')->put('test.txt', 'Laravel 9 S3 test');
Storage::disk('s3')->exists('test.txt');
Storage::disk('s3')->delete('test.txt');
```

**Expected Behavior:**
- ✓ All operations complete without errors
- ✓ Files are written and read correctly
- ✓ No Flysystem exceptions

---

### 4. ✅ PostgreSQL Configuration (if using Postgres)

**What Changed:** `schema` renamed to `search_path`

**Manual Test:**
```bash
# Test database connection
php artisan db:show

# Run a simple query
php artisan tinker
```

Then in Tinker:
```php
DB::connection('pgsql')->select('SELECT current_schema()');
```

**Expected Behavior:**
- ✓ Database connection successful
- ✓ No errors about 'schema' configuration
- ✓ Queries execute normally

---

### 5. ✅ Error Pages (Ignition)

**What Changed:** `facade/ignition` replaced with `spatie/laravel-ignition`

**Manual Test:**
```bash
# 1. Trigger a test error in a route
# Add this temporarily to routes/web.php:
Route::get('/test-error', function() {
    throw new Exception('Testing Laravel 9 Ignition');
});

# 2. Visit the error page in browser
# Navigate to: http://your-app.test/test-error

# 3. Check error page displays properly
```

**Expected Behavior:**
- ✓ Error page displays with Ignition styling
- ✓ Stack trace is visible
- ✓ No "Class not found" errors
- ✓ Debug information is accessible

---

### 6. ✅ Authentication & Sessions

**Manual Test:**
```bash
# Test login/logout flow
php artisan serve
```

Then test in browser:
- Visit login page
- Log in with credentials
- Verify session is maintained
- Log out
- Verify session is destroyed

**Expected Behavior:**
- ✓ Login works correctly
- ✓ Sessions persist between requests
- ✓ CSRF protection works
- ✓ Password reset functionality works

---

### 7. ✅ Cache & Queue

**Manual Test:**
```bash
# Test cache
php artisan tinker
```

Then in Tinker:
```php
// Cache test
Cache::put('test-key', 'test-value', 60);
Cache::get('test-key'); // Should return 'test-value'
Cache::forget('test-key');

// Queue test (if using queues)
dispatch(new \App\Jobs\TestJob());
```

**Expected Behavior:**
- ✓ Cache operations work correctly
- ✓ Queue jobs dispatch without errors

---

### 8. ✅ Filament Admin Panel

**What Changed:** Configuration variable updated

**Manual Test:**
```bash
# Access admin panel
php artisan serve
# Visit: http://your-app.test/painel (or your configured path)
```

**Test in Admin:**
- Log in to admin panel
- Upload a file/image
- Verify file uploads work
- Check all CRUD operations

**Expected Behavior:**
- ✓ Admin panel loads correctly
- ✓ File uploads work (tests new filesystem config)
- ✓ No JavaScript errors in console

---

## Regression Testing Checklist

Test all major features of your application:

### Core Functionality
- [ ] Homepage loads correctly
- [ ] User registration works
- [ ] User login/logout works
- [ ] Password reset works
- [ ] Email sending works (if configured)

### Database Operations
- [ ] Can create records
- [ ] Can read records
- [ ] Can update records
- [ ] Can delete records
- [ ] Relationships load correctly
- [ ] Transactions work correctly

### File Operations
- [ ] File uploads work
- [ ] File downloads work
- [ ] Image processing works (if applicable)
- [ ] S3 uploads work (if using S3)

### API Endpoints (if applicable)
- [ ] API authentication works
- [ ] GET endpoints return correct data
- [ ] POST endpoints create data correctly
- [ ] PUT/PATCH endpoints update data correctly
- [ ] DELETE endpoints remove data correctly

### Background Jobs (if applicable)
- [ ] Jobs dispatch correctly
- [ ] Jobs process successfully
- [ ] Failed jobs are logged

### Third-party Integrations
- [ ] Stripe/payment processing works (if applicable)
- [ ] Email service works (SES, Mailgun, etc.)
- [ ] Analytics tracking works (if applicable)

---

## Performance Testing

```bash
# Check application performance
php artisan optimize

# Clear and rebuild caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild optimized files
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Troubleshooting Common Issues

### Issue: "Class not found" errors

**Solution:**
```bash
composer dump-autoload
php artisan clear-compiled
php artisan optimize:clear
```

### Issue: Flysystem errors

**Check:**
- Verify `league/flysystem-aws-s3-v3` is version 3.x
- Update any custom filesystem code
- Check disk configurations in `config/filesystems.php`

### Issue: Proxy/trusted proxy errors

**Check:**
- Verify `app/Http/Middleware/TrustProxies.php` uses `Illuminate\Http\Middleware\TrustProxies`
- Check `$headers` property is set correctly
- Verify middleware is registered in `app/Http/Kernel.php`

### Issue: Database connection errors (Postgres)

**Check:**
- Verify `config/database.php` uses `search_path` not `schema`
- Clear config cache: `php artisan config:clear`
- Test connection: `php artisan db:show`

### Issue: Error pages not displaying

**Check:**
- Verify `spatie/laravel-ignition` is installed
- Check `APP_DEBUG=true` in `.env` for testing
- Clear all caches

---

## CI/CD Testing

If using CI/CD, update your pipeline configuration:

```yaml
# Example GitHub Actions
- name: Run Tests
  run: |
    composer install
    php artisan test
    
# Example GitLab CI
test:
  script:
    - composer install
    - php artisan test
```

---

## Production Deployment Checklist

Before deploying to production:

- [ ] All automated tests pass
- [ ] Manual testing completed on staging
- [ ] Database backups created
- [ ] `.env` variables updated (if needed)
- [ ] Composer dependencies updated on production
- [ ] Caches cleared on production
- [ ] Error monitoring configured
- [ ] Rollback plan ready

### Deployment Commands

```bash
# On production server:
composer install --optimize-autoloader --no-dev

# Clear and optimize
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations if any
php artisan migrate --force

# Restart queue workers if using queues
php artisan queue:restart
```

---

## Getting Help

If you encounter issues:

1. Check `LARAVEL_9_UPGRADE.md` for detailed change documentation
2. Review Laravel 9 upgrade guide: https://laravel.com/docs/9.x/upgrade
3. Check application logs: `storage/logs/laravel.log`
4. Enable debug mode temporarily: `APP_DEBUG=true`

---

## Test Results Template

Use this template to document your testing:

```
# Laravel 9 Upgrade Test Results

Date: _______________
Tested by: _______________
Environment: [ ] Local  [ ] Staging  [ ] Production

## Automated Tests
- [ ] All tests passing
- [ ] No deprecation warnings
- [ ] No new errors in logs

## Manual Tests
- [ ] TrustProxies middleware working
- [ ] File storage working (local)
- [ ] File storage working (S3)
- [ ] PostgreSQL connections working
- [ ] Error pages displaying correctly
- [ ] Admin panel working
- [ ] Authentication working

## Regression Tests
- [ ] All core features working
- [ ] No performance degradation
- [ ] Third-party integrations working

## Notes:
_________________________
_________________________
_________________________

## Issues Found:
_________________________
_________________________
_________________________

## Sign-off:
Ready for production: [ ] Yes  [ ] No
```

---

## Success Criteria

The upgrade is successful when:

✅ All automated tests pass
✅ All manual tests pass
✅ No regression issues found
✅ Performance is maintained or improved
✅ Error monitoring shows no new errors
✅ Users can access and use the application normally

---

**Good luck with your Laravel 9 upgrade testing!** 🚀
