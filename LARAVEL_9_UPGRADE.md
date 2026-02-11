# Laravel 9 Upgrade Guide

This document describes the changes made to upgrade this Laravel 8 application to Laravel 9.

## Summary of Changes

All changes have been implemented following the [Laravel 9 Upgrade Guide](https://laravel.com/docs/9.x/upgrade).

### 1. Composer Dependencies Updated

#### composer.json - require section:
- **PHP Version**: Changed from `^7.3|^8.0` to `^8.0` (Laravel 9 requires PHP 8.0.2+)
- **Laravel Framework**: Updated from `^8.0` to `^9.0`
- **Laravel UI**: Updated from `^3.0` to `^4.0`
- **Flysystem S3 Driver**: Updated from `^1.0` to `^3.0` (Major version update required)

#### composer.json - require-dev section:
- **nunomaduro/collision**: Updated from `^5.0` to `^6.1`
- **fzaninotto/faker**: Replaced with `fakerphp/faker` (original package is abandoned)
- **facade/ignition**: Replaced with `spatie/laravel-ignition` `^1.0` (facade/ignition is no longer maintained for Laravel 9)

### 2. Middleware Changes

#### app/Http/Middleware/TrustProxies.php
- **Changed import** from `Fideloper\Proxy\TrustProxies` to `Illuminate\Http\Middleware\TrustProxies`
- **Updated $headers property**: Changed from `Request::HEADER_X_FORWARDED_ALL` to explicit header flags:
  ```php
  protected $headers =
      Request::HEADER_X_FORWARDED_FOR |
      Request::HEADER_X_FORWARDED_HOST |
      Request::HEADER_X_FORWARDED_PORT |
      Request::HEADER_X_FORWARDED_PROTO |
      Request::HEADER_X_FORWARDED_AWS_ELB;
  ```

### 3. Configuration Changes

#### config/filesystems.php
- **Line 16**: Changed `env('FILESYSTEM_DRIVER', 'local')` to `env('FILESYSTEM_DISK', 'local')`
- **Reason**: Laravel 9 renames `FILESYSTEM_DRIVER` to `FILESYSTEM_DISK` for consistency

#### config/filament.php
- **Line 297**: Changed `env('FILAMENT_FILESYSTEM_DRIVER', 'public')` to `env('FILAMENT_FILESYSTEM_DISK', 'public')`
- **Reason**: Match Laravel 9's naming convention

#### config/database.php
- **Line 77**: Changed `'schema' => 'public'` to `'search_path' => 'public'` in Postgres configuration
- **Reason**: Laravel 9 renames the `schema` configuration option to `search_path` for Postgres connections

## Next Steps

### 1. Update Dependencies
Run the following command to update all dependencies:

```bash
composer update
```

**Expected behavior**: Composer will update Laravel from 8.x to 9.x and all related dependencies.

### 2. Clear Caches
After updating, clear all caches:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 3. Update Environment Variables (Optional)

If you're using custom environment variables, you may want to update:
- `FILESYSTEM_DRIVER` → `FILESYSTEM_DISK` (if set in .env)
- `FILAMENT_FILESYSTEM_DRIVER` → `FILAMENT_FILESYSTEM_DISK` (if set in .env)

**Note**: The old variable names will still work due to the fallback in the config files, but updating them is recommended for clarity.

### 4. Test Your Application

After the upgrade, thoroughly test:

1. **File uploads and storage operations** (due to Flysystem v3 changes)
2. **S3 storage** (if used, as the driver changed from v1 to v3)
3. **Proxy and trusted proxy functionality** (due to TrustProxies changes)
4. **Authentication and authorization**
5. **All major features and workflows**

### 5. Check Filament Compatibility

This application uses **Filament v2**. Verify compatibility:
- Filament v2 should work with Laravel 9
- Check [Filament documentation](https://filamentphp.com/docs) for any breaking changes
- Consider upgrading to Filament v3 in the future (separate task)

### 6. Review Third-Party Packages

Check compatibility of other packages:
- `spatie/laravel-permission` (^5.10) - Should be compatible
- `spatie/laravel-honeypot` (^4.3) - Should be compatible
- `spatie/laravel-sitemap` (^5.9) - Should be compatible
- `laravel/cashier` (^13.0) - Should be compatible

## Breaking Changes to Watch For

### Flysystem v3 (league/flysystem-aws-s3-v3)
- The Flysystem library was upgraded from v1 to v3
- If you have custom storage code, review the [Flysystem v3 upgrade guide](https://flysystem.thephpleague.com/docs/upgrade-from-1.x/)
- Most common Laravel storage operations should work without changes

### TrustProxies Middleware
- Now uses built-in Laravel class instead of fideloper/proxy package
- The `$headers` property uses explicit constants instead of `HEADER_X_FORWARDED_ALL`
- Behavior should be identical for most use cases

### String Helpers
- Some string helpers have changed behavior
- Review any custom string manipulation code

### Validation
- Some validation rules have changed
- Test all form validations after upgrade

## Rollback Plan

If you encounter issues:

1. **Revert composer.json changes**:
   ```bash
   git checkout HEAD composer.json composer.lock
   composer install
   ```

2. **Revert all changes**:
   ```bash
   git checkout HEAD .
   composer install
   ```

## Resources

- [Laravel 9 Upgrade Guide](https://laravel.com/docs/9.x/upgrade)
- [Laravel 9 Release Notes](https://laravel.com/docs/9.x/releases)
- [Flysystem v3 Documentation](https://flysystem.thephpleague.com/docs/)
- [Filament v2 Documentation](https://filamentphp.com/docs/2.x/admin/installation)

## Support

If you encounter any issues during the upgrade:
1. Check the Laravel 9 documentation
2. Review this document for missed steps
3. Check package-specific documentation for compatibility issues
4. Test in a development environment before deploying to production
