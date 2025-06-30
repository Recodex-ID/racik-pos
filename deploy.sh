#!/bin/bash

# RACIK POS Deployment Script
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production

set -e

ENVIRONMENT=${1:-production}
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="backups"

echo "🚀 Starting RACIK POS deployment for $ENVIRONMENT environment..."
echo "📅 Timestamp: $TIMESTAMP"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel root directory."
    exit 1
fi

# Function to check command status
check_status() {
    if [ $? -eq 0 ]; then
        echo "✅ $1 completed successfully"
    else
        echo "❌ Error: $1 failed"
        exit 1
    fi
}

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

echo "📋 Pre-deployment checks..."

# Check if required commands are available
command -v composer >/dev/null 2>&1 || { echo "❌ Composer is required but not installed."; exit 1; }
command -v npm >/dev/null 2>&1 || { echo "❌ NPM is required but not installed."; exit 1; }
command -v php >/dev/null 2>&1 || { echo "❌ PHP is required but not installed."; exit 1; }

echo "✅ All required commands are available"

# Backup current .env if exists
if [ -f ".env" ]; then
    cp .env "$BACKUP_DIR/.env.backup.$TIMESTAMP"
    echo "✅ Environment file backed up"
fi

# Install/Update Composer dependencies
echo "📦 Installing Composer dependencies..."
if [ "$ENVIRONMENT" = "production" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    composer install --optimize-autoloader --no-interaction
fi
check_status "Composer installation"

# Install/Update NPM dependencies
echo "📦 Installing NPM dependencies..."
npm ci
check_status "NPM installation"

# Build assets
echo "🏗️  Building frontend assets..."
if [ "$ENVIRONMENT" = "production" ]; then
    npm run build
else
    npm run dev
fi
check_status "Asset building"

# Laravel optimization commands
echo "⚡ Optimizing Laravel application..."

# Clear all caches
php artisan config:clear
php artisan cache:clear  
php artisan view:clear
php artisan route:clear
check_status "Cache clearing"

# Cache optimization for production
if [ "$ENVIRONMENT" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    check_status "Production cache optimization"
fi

# Run database migrations
echo "🗄️  Running database migrations..."
if [ "$ENVIRONMENT" = "production" ]; then
    php artisan migrate --force
else
    php artisan migrate
fi
check_status "Database migration"

# Create storage link if it doesn't exist
if [ ! -L "public/storage" ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link
    check_status "Storage symlink creation"
fi

# Set proper permissions
echo "🔐 Setting file permissions..."
find storage -type f -exec chmod 644 {} \;
find storage -type d -exec chmod 755 {} \;
find bootstrap/cache -type f -exec chmod 644 {} \;
find bootstrap/cache -type d -exec chmod 755 {} \;
chmod -R 775 storage
chmod -R 775 bootstrap/cache
check_status "Permission setting"

# Run tests if not in production
if [ "$ENVIRONMENT" != "production" ]; then
    echo "🧪 Running tests..."
    if [ -f "vendor/bin/pest" ]; then
        ./vendor/bin/pest
        check_status "Testing"
    else
        php artisan test
        check_status "Testing"
    fi
fi

# Check application health
echo "🏥 Checking application health..."
php artisan about --only=environment
check_status "Health check"

echo ""
echo "🎉 Deployment completed successfully!"
echo "📊 Summary:"
echo "   - Environment: $ENVIRONMENT"
echo "   - Timestamp: $TIMESTAMP"
echo "   - Backup created: $BACKUP_DIR/.env.backup.$TIMESTAMP"
echo ""

if [ "$ENVIRONMENT" = "production" ]; then
    echo "🌐 Production deployment checklist:"
    echo "   ✅ Dependencies installed (production mode)"
    echo "   ✅ Assets built and optimized"
    echo "   ✅ Application cached and optimized"
    echo "   ✅ Database migrated"
    echo "   ✅ Permissions set correctly"
    echo "   ✅ Storage symlink created"
    echo ""
    echo "🔍 Next steps:"
    echo "   1. Verify application is accessible"
    echo "   2. Check logs for any errors"
    echo "   3. Test critical functionality"
    echo "   4. Monitor application performance"
else
    echo "🛠️  Development deployment completed"
    echo "   ✅ All dependencies installed"
    echo "   ✅ Development assets built"
    echo "   ✅ Tests passed"
    echo "   ✅ Database migrated"
    echo ""
    echo "🚀 Ready for development!"
fi

echo "📝 Log files location: storage/logs/"
echo "🔧 For troubleshooting, check: php artisan about"