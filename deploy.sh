#!/bin/bash
set -e

# --- Configuration ---
# Update these paths if your cPanel uses specific PHP/Composer binaries
PHP_BIN="php" 
COMPOSER_BIN="composer" # or "/usr/local/bin/composer" or "php composer.phar"
# --------------------

echo "🚀 Starting deployment..."

# 1. Maintenance Mode
echo "🚧 Entering maintenance mode..."
($PHP_BIN artisan down --message="The app is being updated. Please try again in a minute.") || true

# 2. Update Code
# echo "📥 Pulling latest changes..."
# git pull origin main 

# 3. PHP Dependencies
echo "📦 Installing PHP dependencies..."
$COMPOSER_BIN install --no-dev --optimize-autoloader

# 4. Frontend Assets (Vite)
# Remove the Vite "hot" file if it exists (can block production assets)
if [ -f public/hot ]; then
    echo "🔥 Removing public/hot file..."
    rm public/hot
fi

# NOTE: Building on cPanel often hits memory limits. 
# RECOMMENDED: Build locally (npm run build) and push the 'public/build' directory.
echo "🏗️ Building assets..."
npm install
npm run build

# 5. Database
echo "🗄️ Running migrations..."
$PHP_BIN artisan migrate --force

# 6. Optimization
echo "⚡ Optimizing caches..."
$PHP_BIN artisan cache:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

# 7. Storage
echo "🔗 Linking storage..."
$PHP_BIN artisan storage:link || true

# 8. Service Restarts (Optional)
# echo "🔄 Restarting workers..."
# $PHP_BIN artisan queue:restart
# $PHP_BIN artisan reverb:restart

# 9. Online
echo "🌐 Coming back online..."
$PHP_BIN artisan up

echo "✅ Deployment finished successfully!"
