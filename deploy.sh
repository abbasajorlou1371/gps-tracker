#!/bin/bash

# Print every command before executing and exit on any error
set -ex

echo "🚀 Starting deployment process..."

# Pull the latest changes from the main branch
echo "📥 Pulling latest changes from git..."
git pull origin main
git branch -M main

# Install/update Composer dependencies
echo "📦 Installing/updating Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Install/update NPM dependencies and build assets
echo "🎨 Installing/updating NPM packages and building assets..."
npm install
npm run build

# Check if APP_KEY is empty and generate if needed
echo "🔑 Checking application key..."
if grep -q "^APP_KEY=$" .env || ! grep -q "^APP_KEY=" .env; then
    echo "Generating new application key..."
    php artisan key:generate
fi

# Clear all Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Optimize Laravel
echo "⚡ Optimizing Laravel..."
php artisan optimize

# Restart queue workers
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Restart Laravel Reverb server
echo "🔄 Restarting Reverb server..."
supervisorctl restart reverb-server

echo "✅ Deployment completed successfully!"
