# OpenTimestamps (OTS) Integration — Hotel PMS

## 📋 Overview

Integrasi OpenTimestamps untuk memberikan bukti kriptografis bahwa setiap invoice
Hotel PMS telah ditandai waktu (timestamped) dan tidak berubah sejak diterbitkan.
Proof disimpan di Bitcoin Blockchain melalui OpenTimestamps.

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Public Invoice View                    │
│              (resources/views/invoices/public-show.blade) │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│               InvoiceController                          │
│         (app/Http/Controllers/InvoiceController.php)     │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│             OpenTimestampService                         │
│         (app/Services/OpenTimestampService.php)          │
├─────────────────────────────────────────────────────────┤
│  → timestampInvoice() → createRevision()                │
│  → timestampTransaction()                               │
│  → verifyInvoice() → verifyTransaction()               │
│  → upgradeProof() → downloadOtsFile()                  │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│          InvoiceTimestampRepository                      │
│     (app/Repositories/InvoiceTimestampRepository.php)    │
├─────────────────────────────────────────────────────────┤
│  → CRUD operations for invoice_timestamps table         │
│  → Pending/Confirmed/Failed status management           │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│              InvoiceTimestamp Model                      │
│          (app/Models/InvoiceTimestamp.php)               │
├─────────────────────────────────────────────────────────┤
│  Table: invoice_timestamps                               │
│  - invoice_id, invoice_type, revision                   │
│  - sha256, ots_file, ots_status                         │
│  - calendar, bitcoin_txid, bitcoin_block                │
│  - bitcoin_block_hash, timestamped_at, confirmed_at     │
└─────────────────────────────────────────────────────────┘
```

## 📁 File Structure

```
app/
├── Console/
│   └── Commands/
│       └── OtsUpgradeCommand.php          # php artisan ots:upgrade
├── Helpers/
│   └── OtsHelper.php                      # Helper functions for views
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   └── OpenTimestampController.php # REST API endpoints
│   │   ├── InvoiceController.php           # Updated with new OTS service
│   │   └── OpenTimestampWebController.php  # Web download & verify
│   └── ...
├── Models/
│   └── InvoiceTimestamp.php               # Eloquent Model
├── Repositories/
│   └── InvoiceTimestampRepository.php     # Repository Pattern
└── Services/
    └── OpenTimestampService.php           # Core OTS logic (enhanced)
database/
└── migrations/
    └── 2026_07_22_100000_create_invoice_timestamps_table.php
resources/
└── views/
    └── invoices/
        └── public-show.blade.php          # Enhanced OTS card
routes/
├── api.php                                # OTS API routes
└── web.php                                # OTS web routes
config/
└── services.php                           # OTS configuration
OTS_INTEGRATION.md                         # This documentation
```

## 🗄️ Database Table: `invoice_timestamps`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint, PK | Auto-increment |
| `invoice_id` | bigint | ID dari reservation atau transaction |
| `invoice_type` | varchar(50) | `reservation` atau `transaction` |
| `revision` | tinyint | Nomor revisi (0, 1, 2, ...) |
| `sha256` | char(64) | SHA-256 hash dari data invoice |
| `ots_file` | longtext, nullable | Base64-encoded .ots binary proof |
| `ots_status` | varchar(20) | `pending`, `confirming`, `confirmed`, `failed` |
| `calendar` | varchar(255), nullable | URL calendar OTS |
| `bitcoin_txid` | char(64), nullable | Bitcoin transaction ID |
| `bitcoin_block` | int, nullable | Nomor block Bitcoin |
| `bitcoin_block_hash` | char(64), nullable | Hash block Bitcoin |
| `timestamped_at` | timestamp, nullable | Waktu timestamp dibuat |
| `confirmed_at` | timestamp, nullable | Waktu dikonfirmasi blockchain |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

## 🔧 Configuration

Edit `.env` file:

```env
# OpenTimestamps
OTS_ENABLED=true
OTS_BIN_PATH=ots                           # Path ke binary OTS CLI
OTS_CALENDAR=https://a.pool.opentimestamps.org
```

Atau via `config/services.php`:

```php
'opentimestamps' => [
    'enabled' => env('OTS_ENABLED', true),
    'bin_path' => env('OTS_BIN_PATH', 'ots'),
    'calendar' => env('OTS_CALENDAR', 'https://a.pool.opentimestamps.org'),
],
```

## 🚀 Usage

### 1. Install OTS CLI

```bash
# Linux (Ubuntu/Debian)
pip3 install opentimestamps-client

# macOS
brew install opentimestamps-client

# Windows (via pip)
pip install opentimestamps-client

# Verifikasi instalasi
ots --version
```

### 2. Run Migration

```bash
php artisan migrate
```

### 3. Timestamp Invoice (otomatis saat public invoice dilihat)

Buka URL:
```
https://icon.cloudnod.my.id/invoice/RES-XXXXX?sig=xxxx
```

OTS proof akan dibuat otomatis jika belum ada.

### 4. Manual via API

```bash
# Timestamp invoice
curl -X POST https://icon.cloudnod.my.id/api/ots/timestamp/invoice/1 \
  -H "X-API-Key: your-api-key"

# Verifikasi invoice
curl https://icon.cloudnod.my.id/api/ots/verify/invoice/1 \
  -H "X-API-Key: your-api-key"

# Download proof
curl https://icon.cloudnod.my.id/api/ots/proof/1 \
  -H "X-API-Key: your-api-key"

# Upgrade proof ke blockchain
curl -X POST https://icon.cloudnod.my.id/api/ots/upgrade/1 \
  -H "X-API-Key: your-api-key"

# Buat revision baru
curl -X POST https://icon.cloudnod.my.id/api/ots/revision/invoice/1 \
  -H "X-API-Key: your-api-key"

# Lihat pending timestamps
curl https://icon.cloudnod.my.id/api/ots/pending \
  -H "X-API-Key: your-api-key"
```

### 5. Artisan Commands

```bash
# Upgrade semua pending proofs
php artisan ots:upgrade

# Upgrade 10 proofs saja
php artisan ots:upgrade --limit=10

# Coba ulang yang gagal
php artisan ots:upgrade --retry-failed

# Dry-run (lihat daftar tanpa eksekusi)
php artisan ots:upgrade --dry-run
```

### 6. Cron Job (Otomatis setiap jam)

Sudah terdaftar di `app/Console/Kernel.php`:
```php
$schedule->command('ots:upgrade --limit=20')
    ->hourly()
    ->withoutOverlapping(60)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/ots-upgrade.log'));
```

## 🔐 Workflow

### Normal Flow

```
1. User booking → Reservation dibuat
2. Invoice PDF diterbitkan
3. Public Invoice dibuka → OTS auto-timestamp
   a. Hitung SHA-256 dari data invoice
   b. Submit hash ke OpenTimestamps calendar
   c. Simpan .ots proof (base64) ke database
   d. Tampilkan status "Pending" di invoice
4. Cron job `ots:upgrade` berjalan setiap jam
   a. Ambil semua pending timestamps
   b. Upgrade proof → dapatkan konfirmasi blockchain
   c. Simpan bitcoin_txid, bitcoin_block
   d. Update status jadi "Confirmed"
5. Invoice menampilkan badge hijau "Confirmed"
```

### Revision Flow

```
INV-001 Rev 0 → SHA256 A → OTS Proof A → Confirmed
INV-001 Rev 1 → SHA256 B → OTS Proof B → Pending
INV-001 Rev 2 → SHA256 C → OTS Proof C → Pending

Setiap revision memiliki proof sendiri.
Proof lama TIDAK PERNAH dihapus.
```

### Verification Flow

```
User buka invoice publik
  ↓
OTS Service hitung SHA-256 data terkini
  ↓
Bandingkan dengan SHA-256 yang tersimpan di database
  ↓
Match? → "Verified" (badge hijau)
  ↓
Tidak match? → "Tampered" (badge merah)
```

## 🛡️ Security

- **Input Validation**: Semua input user divalidasi sebelum diproses
- **Output Escaping**: Semua output HTML di-escape menggunakan Blade
- **SHA-256 Validation**: Hash dicek format 64 karakter hex
- **OTS File Storage**: Disimpan sebagai base64 di database, tidak expose path server
- **Transaction Safety**: Semua operasi database menggunakan transaksi
- **Constant-time Comparison**: `hash_equals()` untuk perbandingan hash
- **Prepared Statements**: Eloquent ORM using parameterized queries

## 🔍 Troubleshooting

### OTS CLI not found
```bash
# Cek instalasi
which ots
ots --version

# Jika pakai pip, tambahkan ke PATH
export PATH="$HOME/.local/bin:$PATH"
```

### Migration failed
```bash
# Rollback dan coba lagi
php artisan migrate:rollback --step=1
php artisan migrate
```

### Proof stuck at "Pending"
```bash
# Jalankan upgrade manual
php artisan ots:upgrade --limit=50

# Cek log
tail -f storage/logs/ots-upgrade.log
```

### Helper functions not found
```bash
# Dump autoload
composer dump-autoload
```

## 📚 Helper Functions

Tersedia di `app/Helpers/OtsHelper.php`:

| Function | Description |
|----------|-------------|
| `ots_verify_invoice($reservation)` | Verifikasi invoice via OTS |
| `ots_verify_transaction($transaction)` | Verifikasi transaksi via OTS |
| `ots_format_status_badge($status)` | Generate Bootstrap 5 badge |
| `ots_short_hash($hash, $length)` | Potong hash untuk display |
| `ots_download_url($model, $revision)` | URL download .ots file |
| `ots_verify_url($sha256)` | URL verifikasi publik |
| `ots_format_timestamp($timestamp)` | Format dengan timezone WIB |
| `ots_block_explorer_url($txid)` | URL block explorer Bitcoin |

## 📊 Status yang Mungkin

| Status | Badge | Keterangan |
|--------|-------|------------|
| `pending` | 🟡 Yellow | Proof dibuat, menunggu konfirmasi blockchain |
| `confirming` | 🔵 Blue | Proof sedang di-upgrade |
| `confirmed` | 🟢 Green | Terverifikasi di Bitcoin Blockchain |
| `failed` | 🔴 Red | Gagal di-upgrade (coba lagi via cron) |
