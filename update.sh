#!/bin/bash

# Exit on any error
set -e

echo "🚀 Running database migrations..."
php artisan migrate --force

echo "🧹 Clearing application caches..."
php artisan optimize:clear

echo "🏗️ Building frontend assets..."
npm run build

echo "✅ Update complete!"
