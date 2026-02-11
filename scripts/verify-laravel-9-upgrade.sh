#!/bin/bash

##############################################################################
# Laravel 9 Upgrade Verification Script
# This script performs quick checks to verify the Laravel 9 upgrade
##############################################################################

set -e

echo "╔══════════════════════════════════════════════════════════════════════╗"
echo "║         Laravel 9 Upgrade Verification Script                       ║"
echo "╚══════════════════════════════════════════════════════════════════════╝"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
PASSED=0
FAILED=0
WARNINGS=0

# Function to print test result
print_result() {
    local test_name=$1
    local status=$2
    local message=$3
    
    if [ "$status" = "PASS" ]; then
        echo -e "${GREEN}✓${NC} ${test_name}: ${GREEN}PASSED${NC}"
        ((PASSED++))
    elif [ "$status" = "FAIL" ]; then
        echo -e "${RED}✗${NC} ${test_name}: ${RED}FAILED${NC} - ${message}"
        ((FAILED++))
    elif [ "$status" = "WARN" ]; then
        echo -e "${YELLOW}⚠${NC} ${test_name}: ${YELLOW}WARNING${NC} - ${message}"
        ((WARNINGS++))
    fi
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1. Checking Dependencies"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check if composer.lock exists
if [ -f "composer.lock" ]; then
    print_result "composer.lock exists" "PASS"
else
    print_result "composer.lock exists" "FAIL" "Run 'composer update' first"
fi

# Check if vendor directory exists
if [ -d "vendor" ]; then
    print_result "vendor directory exists" "PASS"
else
    print_result "vendor directory exists" "FAIL" "Run 'composer install' first"
fi

# Check Laravel version
if [ -f "vendor/laravel/framework/src/Illuminate/Foundation/Application.php" ]; then
    LARAVEL_VERSION=$(grep "const VERSION" vendor/laravel/framework/src/Illuminate/Foundation/Application.php | grep -oP "'\K[^']+")
    if [[ $LARAVEL_VERSION == 9.* ]]; then
        print_result "Laravel version ($LARAVEL_VERSION)" "PASS"
    else
        print_result "Laravel version ($LARAVEL_VERSION)" "FAIL" "Expected version 9.x"
    fi
else
    print_result "Laravel version" "WARN" "Could not detect Laravel version"
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
if php -r 'exit(version_compare(PHP_VERSION, "8.0.2", ">=") ? 0 : 1);'; then
    print_result "PHP version ($PHP_VERSION)" "PASS"
else
    print_result "PHP version ($PHP_VERSION)" "FAIL" "Laravel 9 requires PHP 8.0.2+"
fi

# Check for removed packages
if ! grep -q "fideloper/proxy" composer.json 2>/dev/null; then
    print_result "fideloper/proxy removed" "PASS"
else
    print_result "fideloper/proxy removed" "FAIL" "This package should be removed in Laravel 9"
fi

if ! grep -q "facade/ignition" composer.json 2>/dev/null; then
    print_result "facade/ignition removed" "PASS"
else
    print_result "facade/ignition removed" "FAIL" "Replace with spatie/laravel-ignition"
fi

if ! grep -q "fzaninotto/faker" composer.json 2>/dev/null; then
    print_result "fzaninotto/faker removed" "PASS"
else
    print_result "fzaninotto/faker removed" "FAIL" "Replace with fakerphp/faker"
fi

# Check for required new packages
if grep -q "spatie/laravel-ignition" composer.json 2>/dev/null; then
    print_result "spatie/laravel-ignition installed" "PASS"
else
    print_result "spatie/laravel-ignition installed" "FAIL" "This package is required for Laravel 9"
fi

if grep -q "fakerphp/faker" composer.json 2>/dev/null; then
    print_result "fakerphp/faker installed" "PASS"
else
    print_result "fakerphp/faker installed" "WARN" "Consider using fakerphp/faker instead of fzaninotto/faker"
fi

# Check Flysystem version
if grep -q '"league/flysystem-aws-s3-v3".*"^3.0"' composer.json 2>/dev/null; then
    print_result "Flysystem 3.x installed" "PASS"
else
    print_result "Flysystem 3.x installed" "WARN" "Should use league/flysystem-aws-s3-v3 ^3.0"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2. Checking Code Changes"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check TrustProxies middleware
if grep -q "Illuminate\\\\Http\\\\Middleware\\\\TrustProxies" app/Http/Middleware/TrustProxies.php 2>/dev/null; then
    print_result "TrustProxies uses Laravel 9 middleware" "PASS"
else
    print_result "TrustProxies uses Laravel 9 middleware" "FAIL" "Should use Illuminate\Http\Middleware\TrustProxies"
fi

if ! grep -q "Fideloper" app/Http/Middleware/TrustProxies.php 2>/dev/null; then
    print_result "TrustProxies no Fideloper reference" "PASS"
else
    print_result "TrustProxies no Fideloper reference" "FAIL" "Remove Fideloper\Proxy references"
fi

# Check filesystem configuration
if grep -q "FILESYSTEM_DISK" config/filesystems.php 2>/dev/null; then
    print_result "Filesystem uses FILESYSTEM_DISK" "PASS"
else
    print_result "Filesystem uses FILESYSTEM_DISK" "FAIL" "Should use FILESYSTEM_DISK instead of FILESYSTEM_DRIVER"
fi

# Check Postgres configuration (if exists)
if grep -q "pgsql" config/database.php 2>/dev/null; then
    if grep -q "'search_path'" config/database.php 2>/dev/null; then
        print_result "Postgres uses search_path" "PASS"
    else
        print_result "Postgres uses search_path" "WARN" "Consider using 'search_path' instead of 'schema'"
    fi
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3. Testing Application"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check if artisan works
if php artisan --version > /dev/null 2>&1; then
    print_result "Artisan command works" "PASS"
else
    print_result "Artisan command works" "FAIL" "php artisan --version failed"
fi

# Check if app can boot
if php artisan list > /dev/null 2>&1; then
    print_result "Application boots successfully" "PASS"
else
    print_result "Application boots successfully" "FAIL" "Application failed to boot"
fi

# Check config cache status
if [ -f "bootstrap/cache/config.php" ]; then
    print_result "Config cache status" "WARN" "Config is cached. Run 'php artisan config:clear' to test fresh config"
else
    print_result "Config cache status" "PASS"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4. Summary"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo ""
echo -e "${GREEN}Passed:${NC}   $PASSED"
echo -e "${RED}Failed:${NC}   $FAILED"
echo -e "${YELLOW}Warnings:${NC} $WARNINGS"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}╔══════════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║  ✓ All critical checks passed!                                      ║${NC}"
    echo -e "${GREEN}║  Your application appears ready for Laravel 9                       ║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "Next steps:"
    echo "  1. Run automated tests: php artisan test"
    echo "  2. Test manually: See TESTING_LARAVEL_9_UPGRADE.md"
    echo "  3. Deploy to staging for full testing"
    exit 0
else
    echo -e "${RED}╔══════════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║  ✗ Some checks failed!                                               ║${NC}"
    echo -e "${RED}║  Please fix the issues above before proceeding                      ║${NC}"
    echo -e "${RED}╚══════════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "For help, see:"
    echo "  - LARAVEL_9_UPGRADE.md (upgrade documentation)"
    echo "  - TESTING_LARAVEL_9_UPGRADE.md (testing guide)"
    exit 1
fi
