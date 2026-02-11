# Quick Test Guide for Laravel 9 Upgrade

This is a quick reference for testing your Laravel 9 upgrade. For complete details, see `TESTING_LARAVEL_9_UPGRADE.md`.

## 🚀 Quick Start (5 Minutes)

### Option 1: Run the Verification Script
```bash
./scripts/verify-laravel-9-upgrade.sh
```
This will check all dependencies and code changes.

### Option 2: Run Automated Tests
```bash
php artisan test --filter=Laravel9Upgrade
```
This will run 11 comprehensive tests.

### Option 3: Both!
```bash
./scripts/verify-laravel-9-upgrade.sh && php artisan test --filter=Laravel9Upgrade
```

## 📝 Before Running `composer update`

The verification script will check that your code changes are in place:

```bash
./scripts/verify-laravel-9-upgrade.sh
```

Expected: Code changes should be ready, but packages may show warnings until you run `composer update`.

## 📦 After Running `composer update`

1. **Run verification again:**
   ```bash
   composer update
   ./scripts/verify-laravel-9-upgrade.sh
   ```
   Expected: All checks should PASS

2. **Run automated tests:**
   ```bash
   php artisan test --filter=Laravel9Upgrade
   ```
   Expected: All tests should PASS

3. **Run full test suite:**
   ```bash
   php artisan test
   ```
   Expected: All existing tests should still pass

## 🧪 What Gets Tested

### Verification Script Tests:
- ✅ PHP 8.0.2+ requirement
- ✅ Laravel 9.x installed
- ✅ Old packages removed
- ✅ New packages added
- ✅ Code changes implemented
- ✅ App boots successfully

### Automated Tests Validate:
- ✅ Framework version
- ✅ Package dependencies
- ✅ TrustProxies middleware
- ✅ Filesystem configuration
- ✅ Storage operations (Flysystem 3.x)
- ✅ Cache functionality
- ✅ Database connectivity
- ✅ Configuration loading

## 🔍 Manual Testing (Critical Areas)

After automated tests pass, manually test:

1. **File Storage**
   ```bash
   php artisan tinker
   Storage::disk('local')->put('test.txt', 'test content');
   Storage::disk('local')->get('test.txt');
   Storage::disk('local')->delete('test.txt');
   ```

2. **Error Pages**
   - Trigger an error and verify Ignition displays correctly
   - Check that error details are visible

3. **Authentication**
   - Test login/logout
   - Test password reset

4. **Admin Panel (Filament)**
   - Access your admin panel
   - Test file uploads
   - Verify CRUD operations work

5. **Database Operations**
   ```bash
   php artisan tinker
   // Test your models
   App\Models\User::count();
   ```

## ⚠️ Troubleshooting

### Tests failing?

1. **Clear caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

2. **Rebuild autoloader:**
   ```bash
   composer dump-autoload
   ```

3. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Common Issues:

**Issue: "Class not found" errors**
```bash
composer dump-autoload
php artisan optimize:clear
```

**Issue: Tests fail but app works**
- Check test environment configuration in `phpunit.xml`
- Verify test database is configured correctly

**Issue: Verification script shows warnings**
- Follow the specific guidance in the output
- Check `LARAVEL_9_UPGRADE.md` for details

## 📊 Expected Test Results

### ✅ All Passing (Ready for Production)
```
./scripts/verify-laravel-9-upgrade.sh
Passed:   15
Failed:   0
Warnings: 0

php artisan test --filter=Laravel9Upgrade
Tests: 11 passed
```

### ⚠️ Some Warnings (Review Needed)
```
Passed:   13
Failed:   0
Warnings: 2

Review warnings and determine if they're critical.
```

### ❌ Some Failures (Action Required)
```
Passed:   10
Failed:   3
Warnings: 2

Fix the failed checks before proceeding.
```

## 🎯 Success Checklist

Before deploying to production:

- [ ] Verification script passes all checks
- [ ] All automated tests pass
- [ ] Manual testing completed
- [ ] File storage works (local & S3)
- [ ] Error pages display correctly
- [ ] Authentication works
- [ ] Admin panel functional
- [ ] No errors in logs
- [ ] Performance is acceptable
- [ ] Staging environment tested

## 📚 Need More Details?

- **Complete Testing Guide:** `TESTING_LARAVEL_9_UPGRADE.md`
- **Upgrade Documentation:** `LARAVEL_9_UPGRADE.md`
- **Script Documentation:** `scripts/README.md`

## 🆘 Getting Help

If you encounter issues:

1. Check the troubleshooting section above
2. Review `TESTING_LARAVEL_9_UPGRADE.md`
3. Check `storage/logs/laravel.log`
4. Review Laravel 9 docs: https://laravel.com/docs/9.x/upgrade

---

**Quick Commands Summary:**

```bash
# Before composer update
./scripts/verify-laravel-9-upgrade.sh

# Update dependencies
composer update

# After composer update
./scripts/verify-laravel-9-upgrade.sh
php artisan test --filter=Laravel9Upgrade
php artisan test

# Clear caches
php artisan optimize:clear

# Check logs
tail -f storage/logs/laravel.log
```

---

**Ready to upgrade? Good luck! 🚀**
