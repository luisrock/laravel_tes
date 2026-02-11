# Laravel 9 Upgrade Scripts

This directory contains helper scripts for testing and verifying the Laravel 9 upgrade.

## Available Scripts

### verify-laravel-9-upgrade.sh

A comprehensive verification script that checks:
- PHP and Laravel versions
- Required and deprecated packages
- Code changes (middleware, configuration)
- Application boot status

**Usage:**
```bash
./scripts/verify-laravel-9-upgrade.sh
```

**What it checks:**
- ✓ Dependencies are correctly updated
- ✓ Old packages are removed
- ✓ New packages are installed
- ✓ Code changes are implemented
- ✓ Application boots successfully

**Exit codes:**
- `0` - All checks passed
- `1` - Some checks failed (see output for details)

## Quick Start

After updating your code for Laravel 9:

```bash
# 1. Run the verification script
./scripts/verify-laravel-9-upgrade.sh

# 2. If all checks pass, update dependencies
composer update

# 3. Run the verification script again
./scripts/verify-laravel-9-upgrade.sh

# 4. Run automated tests
php artisan test

# 5. Run Laravel 9 specific tests
php artisan test --filter=Laravel9Upgrade
```

## Need Help?

See the following documentation:
- `LARAVEL_9_UPGRADE.md` - Complete upgrade documentation
- `TESTING_LARAVEL_9_UPGRADE.md` - Comprehensive testing guide
