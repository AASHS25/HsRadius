#!/bin/bash
# HsRadius Installation Script

set -e

echo "=================================="
echo "  HsRadius - Installation Script  "
echo "=================================="
echo ""

# Check PHP
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP is not installed. Please install PHP 8.3+"
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "✓ PHP $PHP_VERSION detected"

# Check Composer
if ! command -v composer &> /dev/null; then
    echo "ERROR: Composer is not installed."
    exit 1
fi
echo "✓ Composer detected"

# Install dependencies
echo ""
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Setup .env
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✓ .env file created from .env.example"
    echo ""
    echo "IMPORTANT: Edit .env and configure your database settings"
    echo "  DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
    echo ""
fi

# Generate app key
php artisan key:generate --force
echo "✓ Application key generated"

# Run migrations
echo ""
echo "Running database migrations..."
php artisan migrate --force
echo "✓ Database migrated"

# Ask for seeding
read -p "Seed database with sample data? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed --force
    echo "✓ Database seeded"
fi

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Caches built"

# Set permissions
chmod -R 775 storage bootstrap/cache
echo "✓ Permissions set"

echo ""
echo "=================================="
echo "  Installation Complete!          "
echo "=================================="
echo ""
echo "Login: admin@hsradius.local / admin123"
echo ""
echo "Run: php artisan serve"
echo "Open: http://localhost:8000"
echo ""
