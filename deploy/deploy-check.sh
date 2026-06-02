#!/bin/bash
# Auto-deploy checker - jalankan via cron setiap 5 menit
# */5 * * * * /www/wwwroot/icon.cloudnod.my.id/deploy/deploy-check.sh

PROJECT_DIR="/www/wwwroot/icon.cloudnod.my.id"
LOG_FILE="$PROJECT_DIR/storage/logs/deploy.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

log "=== Checking for updates ==="

cd "$PROJECT_DIR" || exit 1

# Pull latest changes
git fetch origin main
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)

if [[ $LOCAL != $REMOTE ]]; then
    log "Update detected, pulling..."
    git pull origin main
    
    # Install dependencies if composer changed
    if git diff --name-only $LOCAL $REMOTE | grep -q "composer.lock"; then
        log "Composer changes detected, running composer install..."
        composer install --no-dev --optimize-autoloader --no-interaction
    fi
    
    # Run migrations
    log "Running migrations..."
    php artisan migrate --force
    
    # Cache optimization
    log "Caching config, routes, views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Restart queue
    php artisan queue:restart
    
    log "Deploy completed successfully"
else
    log "No updates found"
fi