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

# Check if .env file exists, if not create it from .env.example
echo "📝 Checking for .env file..."
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

# Check if APP_KEY is empty and generate if needed
echo "🔑 Checking application key..."
if grep -q "^APP_KEY=$" .env || ! grep -q "^APP_KEY=" .env; then
    echo "Generating new application key..."
    php artisan key:generate
fi

# Check SQLite database existence
echo "🔍 Checking SQLite database..."
DB_PATH=$(php artisan tinker --execute="echo config('database.connections.sqlite.database');" | grep -v ">>>" | grep -v "===" | tr -d '\n')

if [ ! -f "$DB_PATH" ]; then
    echo "❌ Error: SQLite database not found at: $DB_PATH"
    echo "Please create the database file manually before running migrations."
    exit 1
fi

# Run database migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Clear all Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear


# Optimize Laravel
echo "⚡ Optimizing Laravel..."
php artisan optimize

echo "✅ Deployment completed successfully!"
