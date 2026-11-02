#!/bin/bash
# Auto-deploy checker - jalankan via cron setiap 5 menit:
#   */5 * * * * /www/wwwroot/<site>/deploy/deploy-check.sh
#
# Path project diturunkan dari lokasi script ini, jadi file yang sama
# aman dipakai di semua instalasi (icon, embun, dst) tanpa saling tabrak.

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
LOG_FILE="$PROJECT_DIR/storage/logs/deploy.log"
SITE="$(basename "$PROJECT_DIR")"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [$SITE] $1" >> "$LOG_FILE"
}

if [[ ! -f "$PROJECT_DIR/artisan" ]]; then
    log "ERROR: bukan project Laravel ($PROJECT_DIR), script dihentikan"
    exit 1
fi

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
    
    # Seed permissions (idempotent — aman dijalankan berulang)
    log "Seeding permissions..."
    php artisan db:seed --class=PermissionSeeder --force
    
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