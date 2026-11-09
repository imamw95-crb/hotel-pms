#!/bin/bash
# Auto-deploy checker - jalankan via cron setiap menit:
#   * * * * * /www/wwwroot/<site>/deploy/deploy-check.sh
#
# Path project diturunkan dari lokasi script ini, jadi file yang sama
# aman dipakai di semua instalasi (icon, embun, dst) tanpa saling tabrak.

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
LOG_FILE="$PROJECT_DIR/storage/logs/deploy.log"
SITE="$(basename "$PROJECT_DIR")"
CACHE_DIR="$PROJECT_DIR/bootstrap/cache"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [$SITE] $1" >> "$LOG_FILE"
}

# Cache 0 byte membuat Laravel fatal ("Target class [config] does not exist")
# sebelum sempat boot. Hapus biar aplikasi tetap jalan dari file config/.
drop_empty_cache() {
    local file
    for file in "$CACHE_DIR/config.php" "$CACHE_DIR/routes-v7.php"; do
        if [[ -f "$file" && ! -s "$file" ]]; then
            log "WARNING: $(basename "$file") kosong, dihapus agar aplikasi bisa boot"
            rm -f "$file"
        fi
    done
}

fail() {
    log "ERROR: $1"
    drop_empty_cache
    exit 1
}

if [[ ! -f "$PROJECT_DIR/artisan" ]]; then
    log "ERROR: bukan project Laravel ($PROJECT_DIR), script dihentikan"
    exit 1
fi

# Cegah dua deploy berjalan bersamaan (deploy bisa > 1 menit saat composer install)
exec 9>"/tmp/deploy-${SITE}.lock"
if ! flock -n 9; then
    exit 0
fi

drop_empty_cache

log "=== Checking for updates ==="

cd "$PROJECT_DIR" || exit 1

# Pull latest changes
git fetch origin main || { log "WARNING: git fetch gagal, deploy dilewati"; exit 1; }
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)

if [[ $LOCAL != $REMOTE ]]; then
    log "Update detected, pulling..."

    # Jangan pakai "git pull" polos: kalau riwayat server divergen dari
    # origin/main (ada commit lokal), git berhenti minta strategi merge dan
    # deploy mengulang tiap menit tanpa pernah benar-benar update.
    if git merge-base --is-ancestor "$LOCAL" "$REMOTE"; then
        git merge --ff-only "$REMOTE" || fail "git merge --ff-only gagal"
    else
        log "WARNING: riwayat server divergen dari origin/main, reset paksa (commit lokal disimpan di branch backup-$LOCAL)"
        git branch -f "backup-$LOCAL" "$LOCAL"
        git reset --hard "$REMOTE" || fail "git reset --hard gagal"
    fi

    # Install dependencies if composer changed
    if git diff --name-only $LOCAL $REMOTE | grep -q "composer.lock"; then
        log "Composer changes detected, running composer install..."
        composer install --no-dev --optimize-autoloader --no-interaction || fail "composer install gagal"
    fi
    
    # Run migrations
    log "Running migrations..."
    php artisan migrate --force || fail "migrate gagal"
    
    # Seed permissions (idempotent — aman dijalankan berulang)
    log "Seeding permissions..."
    php artisan db:seed --class=PermissionSeeder --force || fail "PermissionSeeder gagal"
    
    # Cache optimization — tiap file cache diverifikasi tidak kosong, karena
    # config.php/routes-v7.php 0 byte membuat aplikasi down total.
    log "Caching config, routes, views..."
    php artisan config:cache || fail "config:cache gagal"
    [[ -s "$CACHE_DIR/config.php" ]] || fail "bootstrap/cache/config.php kosong setelah config:cache"
    php artisan route:cache || fail "route:cache gagal"
    [[ -s "$CACHE_DIR/routes-v7.php" ]] || fail "bootstrap/cache/routes-v7.php kosong setelah route:cache"
    php artisan view:cache || fail "view:cache gagal"
    
    # Restart queue
    php artisan queue:restart || log "WARNING: queue:restart gagal"
    
    log "Deploy completed successfully"
else
    log "No updates found"
fi