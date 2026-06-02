# 🚀 Deployment Guide — Hotel PMS

Otomatisasi deploy ke production via GitHub Webhook + GitHub Actions.

## Cara Kerja

```
Push ke main → GitHub Actions → Webhook ke server → git pull → migrate → optimize
```

## Setup Pertama Kali

### 1. Generate Secret

```bash
php -r "echo bin2hex(random_bytes(32));"
```

### 2. Set Environment

Di server production, edit `.env`:

```env
DEPLOY_SECRET=hasil_generate_di_atas
```

### 3. GitHub Secrets

Di repo GitHub → **Settings → Secrets and variables → Actions → New repository secret**:

| Secret Name     | Value                          |
|-----------------|--------------------------------|
| `DEPLOY_SECRET` | Secret yang sama dengan `.env` |
| `DEPLOY_URL`    | `https://icon.cloudnod.my.id/deploy.php` |

### 4. GitHub Webhook (Opsional — jika tidak pakai GitHub Actions)

Di repo GitHub → **Settings → Webhooks → Add webhook**:

- **Payload URL**: `https://icon.cloudnod.my.id/deploy.php`
- **Content type**: `application/json`
- **Secret**: sama dengan `DEPLOY_SECRET`
- **Events**: Just the push event

### 5. Pastikan Permissions

```bash
# storage/logs harus writable
chmod -R 775 storage/logs/
chown -R www-data:www-data storage/logs/

# Pastikan git pull bisa jalan
chown -R www-data:www-data .
```

## Deploy Flow

Setiap push ke `main`, server menjalankan:

| Step | Command | Keterangan |
|------|---------|------------|
| 1 | `git pull origin main` | Pull latest code |
| 2 | `composer install --no-dev --optimize-autoloader` | Hanya jika composer.json/lock berubah |
| 3 | `php artisan migrate --force` | **Auto migrate** — berhenti jika gagal |
| 4 | `php artisan config:cache` | Cache config untuk production |
| 5 | `php artisan route:cache` | Cache routes |
| 6 | `php artisan view:cache` | Cache Blade templates |
| 7 | `php artisan queue:restart` | Restart queue workers |

## Log

Semua proses deploy di-log ke `storage/logs/deploy.log`:

```bash
tail -f storage/logs/deploy.log
```

## Testing Webhook

```bash
# Test ping
curl -X POST https://icon.cloudnod.my.id/deploy.php \
  -H "Content-Type: application/json" \
  -H "X-GitHub-Event: ping" \
  -H "X-Hub-Signature-256: sha256=$(echo -n '' | openssl dgst -sha256 -hex -hmac 'YOUR_SECRET' | awk '{print $NF}')" \
  -d ''

# Test deploy (replace YOUR_SECRET)
curl -X POST https://icon.cloudnod.my.id/deploy.php \
  -H "Content-Type: application/json" \
  -H "X-GitHub-Event: push" \
  -H "X-Hub-Signature-256: sha256=$(echo -n '{"ref":"refs/heads/main","after":"abc123"}' | openssl dgst -sha256 -hex -hmac 'YOUR_SECRET' | awk '{print $NF}')" \
  -d '{"ref":"refs/heads/main","after":"abc123"}'
```

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `403 Invalid signature` | Cek `DEPLOY_SECRET` di `.env` sama dengan GitHub secret |
| `500 DEPLOY_SECRET not configured` | Tambahkan `DEPLOY_SECRET` di `.env` |
| `Git pull failed` | Cek permission folder, pastikan `www-data` bisa `git pull` |
| `Migration failed` | Cek `storage/logs/laravel.log` untuk detail error |
| `deploy.log` tidak terisi | Pastikan `storage/logs/` writable |

## Safety Notes

- ⚠️ **Selalu backup database sebelum pertama kali menggunakan auto-deploy**
- `migrate --force` langsung jalan — pastikan migration sudah di-test di local/staging
- Webhook pakai HMAC SHA256 signature verification — tidak bisa diakses tanpa secret
- Deploy berhenti otomatis jika migration gagal (tidak lanjut ke cache/optimize)
