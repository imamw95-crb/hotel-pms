# 📚 Hotel PMS - Tutorial & Documentation

**Versi:** 2.2  
**Terakhir Diperbarui:** Juni 4, 2026  
**Framework:** Laravel 13.x (PHP 8.3+)  
**Database:** MySQL via Laragon  

---

## 🚀 Quick Start

### 1. Setup Environment

```bash
# Clone project (jika belum ada)
git clone https://github.com/imamw95-crb/hotel-pms.git

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed
```

### 2. Jalankan Server

```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Vite (Frontend)
npm run dev
```

---

## 📋 Daftar Fitur Utama

### 1. **AI Chat Assistant** (OpenRouter)
- ✅ Chat dengan AI via OpenRouter
- ✅ Deteksi booking dari bahasa alami
- ✅ Auto-create reservasi
- ✅ Widget floating di UI

**Endpoint:** `POST /api/ai/chat`

### 2. **Sistem Permission & Role**
- Role: `owner`, `admin`, `frontoffice`, `user_manager`
- Permission-based access control
- Menu dinamis berdasarkan permission

### 3. **Manajemen Reservasi**
- Booking manual & OTA
- Back-to-back booking support
- Room rack & availability calendar

### 4. **OTA Integration**
- Email parsing otomatis dari Tiket.com & Traveloka
- Sync booking otomatis
- Payment status tracking- **OTA Email Log** — Monitoring dashboard real-time
- Retry failed email parsing
- Refresh stats & filter by platform
### 5. **Front Office**
- Check-in / Check-out
- Housekeeping management (dengan checklist, log, inventory)
- Issue kartu kamar
- Lost & Found items
- Service charge & deposit kartu

---

## 🔧 Konfigurasi Penting

### Environment Variables (.env)

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_pms
DB_USERNAME=root
DB_PASSWORD=

# OpenRouter AI
OPENROUTER_API_KEY=sk-or-v1-xxx
OPENROUTER_MODEL=openrouter/owl-alpha
OPENROUTER_BASE_URL=https://openrouter.ai/api/v1

# IMAP (OTA Email)
IMAP_HOST=imap.hostinger.com
IMAP_PORT=993
IMAP_USERNAME=info@theicon.id
IMAP_PASSWORD=xxx

# Deployment
DEPLOY_SECRET=xxx
MHS_BRIDGE_URL=http://100.98.230.92/bridge_api.php
```

---

## 📊 Model & Database

### Core Models

| Model | Tabel | Keterangan |
|-------|-------|------------|
| `Room` | `rooms` | Data kamar & tipe |
| `Reservation` | `reservations` | Data reservasi |
| `Guest` | `guests` | Data tamu |
| `Transaction` | `transactions` | Transaksi pembayaran |
| `User` | `users` | Pengguna sistem |
| `Role` | `roles` | Role pengguna |
| `Permission` | `permissions` | Permission akses |

### Migration Terbaru (2026)

```
2026_07_20_000001_enhance_reservations_for_ota.php
2026_07_20_000002_enhance_processed_emails.php
2026_07_20_000003_fix_subject_length.php
2026_07_20_000004_add_paid_date_to_reservations.php
2026_07_20_000005_seed_ota_payment_methods.php
2026_07_20_000006_add_include_breakfast_to_reservations.php
```

---

## 🎯 Business Logic

### Waktu Standar
- **Check-in:** 14:00 (2 PM)
- **Check-out:** 12:00 (noon)
- **Back-to-back booking:** Check-out & check-in sampai jam 12:00 = **TIDAK konflik**

### Overlap Query Pattern
```php
// Untuk cek konflik booking
where('check_in', '<', $checkOut)
->where('check_out', '>', $checkIn)
```

### Room Status
- `available` - Tersedia
- `occupied` - Terisi
- `cleaning` - Sedang dibersihkan
- `maintenance` - Perawatan

### Reservation Status
- `pending` - Menunggu
- `checked_in` - Check-in sudah dilakukan
- `checked_out` - Check-out sudah dilakukan
- `cancelled` - Dibatalkan

---

## 🛠️ Services & Jobs

### Services

| Service | Fungsi |
|---------|--------|
| `AiChatService` | Chat dengan AI |
| `OpenRouterService` | Integrasi OpenRouter API |
| `AvailabilityService` | Cek ketersediaan kamar |
| `BookingSyncService` | Sync booking OTA |
| `BookingNotificationService` | Notifikasi booking |
| `ImapService` | Baca email OTA |
| `MHSBridgeService` | Integrasi ke MHS |
| `HousekeepingService` | Logika bisnis housekeeping |
| `EmailParserService` | Parse konten email OTA |
| `BookingMapperService` | Mapping data OTA ke sistem |

### Jobs

| Job | Fungsi |
|-----|--------|
| `ProcessBookingEmailJob` | Proses email booking OTA |

---

## 🧹 Housekeeping

Manajemen tugas pembersihan dan perawatan kamar.

**Fitur:**
- Buat Tugas — untuk satu kamar
- Bulk Create — buat tugas untuk banyak kamar sekaligus
- Assign petugas housekeeping (manual & auto-assign)
- Update status: Pending → In Progress → Completed
- **Checklist** — Checklist item per tugas (toggle selesai/belum)
- **Task Log** — Riwayat perubahan status tugas
- **Inventory** — Catat inventaris kamar (handuk, linen, amenities, dll)
- **Photo Dokumentasi** — Upload foto sebelum & sesudah
- Print laporan housekeeping
- Lihat semua tugas per kamar (Room Tasks)
- Charts: penyelesaian 7 hari & distribusi tipe tugas
- Staff workload dashboard

**Staff Management:**
- **My Tasks** — Staff bisa melihat tugas yang ditugaskan ke dirinya
- **Self-Assign** — Staff bisa mengambil tugas kamar yang tersedia
- **Auto-Assign** — Otomatis assign ke staff dengan beban kerja paling ringan
- Staff workload monitoring dengan progress bar

**Status Tugas:**
- Menunggu (Pending)
- Sedang Dikerjakan (In Progress)
- Selesai Hari Ini (Completed)
- Urgent
- Overdue (lewat batas waktu)

---

## 🔔 Notification System

Sistem notifikasi untuk booking dan event penting.

**Fitur:**
- Notifikasi booking baru dari OTA
- Notification bell di sidebar dengan unread count
- Mark as read
- Auto-refresh unread count

**Model:** `BookingNotification`  
**Controller:** `NotificationController`

---

## 🎫 Promo Pricing

Mengelola harga promosi per tipe kamar berdasarkan rentang tanggal.

**Fitur:**
- **Buat Promo** — Tentukan harga khusus untuk tipe kamar di tanggal tertentu
- **Date Range** — Promo berlaku untuk rentang tanggal
- **Room Type** — Promo per tipe kamar (bukan per kamar)
- **Harga Efektif** — Otomatis menggunakan harga promo jika ada
- **API** — Endpoint untuk cek promo price

**Menu:** `Promo Prices` di sidebar  
**Model:** `RoomTypeDatePrice`  
**Controller:** `PromoPriceController`, `Api\PromoPriceApiController`

**Langkah:**
1. Pilih tipe kamar
2. Tentukan tanggal mulai & selesai
3. Masukkan harga promo
4. Simpan — harga otomatis dipakai saat reservasi di tanggal tersebut

---

## 🔍 Lost & Found

Mencatat dan melacak barang hilang atau ditemukan di hotel.

**Fitur:**
- Catat barang hilang/ditemukan
- Status: `reported`, `found`, `returned`, `disposed`
- Link ke reservasi tamu
- Deskripsi detail barang
- Kategori item

**Menu:** `Lost & Found` di sidebar  
**Model:** `LostFound`  
**Controller:** `LostFoundController`

**Langkah:**
1. Klik **Tambah Item**
2. Isi deskripsi barang, kategori, lokasi ditemukan
3. Hubungkan dengan reservasi tamu (opsional)
4. Update status saat barang sudah diambil

---

## 💾 Database Backup

Manajemen backup database dari panel admin.

**Fitur:**
- **Create Backup** — Buat backup manual kapan saja
- **Download** — Download file backup
- **Restore** — Restore dari file backup tertentu
- **List Backups** — Lihat semua file backup yang tersedia
- Hanya bisa diakses oleh role **Owner**

**Menu:** `Admin → Backups`  
**Controller:** `Admin\DatabaseBackupController`

---

## ⚙️ Hotel Settings

Pengaturan konfigurasi hotel dari panel admin.

**Fitur:**
- Konfigurasi IMAP email (untuk OTA)
- Konfigurasi MHS Bridge
- Konfigurasi OpenRouter API
- Dynamic logo & theme
- Hanya bisa diakses oleh role **Owner**

**Menu:** `Admin → Settings`  
**Model:** `HotelSetting`  
**Controller:** `SettingController`

---

## 💰 Expense Tracking

Mencatat pengeluaran operasional hotel. Bisa di-export CSV & Print.

**Fitur:**
- Catat pengeluaran dengan kategori
- Filter berdasarkan tanggal
- Export & print laporan
- Terintegrasi dengan laporan keuangan

**Menu:** `Expenses` di sidebar  
**Model:** `Expense`  
**Controller:** `ExpenseController`

---

## 📊 Reports (Laporan)

Semua laporan bisa di-**Export CSV** dan **Print**.

| Laporan | Fungsi |
|---------|--------|
| Guest List Report | Daftar tamu yang sedang check-in |
| Occupancy | Laporan okupansi kamar per periode |
| Revenue | Pendapatan hotel per periode |
| Reservation Report | Semua reservasi dalam periode |
| Group Report | Laporan reservasi grup/rombongan |

---

## 🔐 Permission System

### Helper Functions (Blade)

```blade
{{-- Check single permission --}}
@if(hasPermission('view_reports'))
    <a href="{{ route('reports.index') }}">Laporan</a>
@endif

{{-- Check multiple permissions --}}
@if(hasAllPermissions(['view_reports', 'export_reports']))
    <button>Export</button>
@endif

{{-- Check any permission --}}
@if(hasAnyPermission(['manage_users', 'manage_rooms']))
    <div class="admin-section">...</div>
@endif
```

### Middleware

```php
// Di routes/web.php
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('permission:view_reports');
```

---

## 🔄 Pindah Kamar (Room Change)

Untuk reservasi yang sudah check-in dan ingin dipindahkan ke kamar lain.

**Langkah:**
1. Buka menu **Front Desk → Pindah Kamar**
2. Pilih reservasi yang akan dipindah
3. Pilih kamar baru yang tersedia
4. Masukkan alasan pemindahan (opsional)
5. Klik **Pindahkan**

---

## 💳 Issue Card MHS

Menerbitkan kartu akses kamar melalui perangkat **MHS (Magic Hotel System)**.

**Langkah:**
1. Cari reservasi (berdasarkan no. reservasi, nama tamu, atau no. kamar)
2. Data tamu dan kamar terisi otomatis
3. Atur jumlah kartu (default 1)
4. Klik **Issue Card**

**Fitur Tambahan:**
- **Re-Issue** — Untuk kartu hilang/rusak
- **Test Connection** — Uji koneksi ke perangkat MHS
- **Read Card** — Baca data kartu yang sudah diterbitkan

---

## 💰 Deposit Kartu

Mengelola deposit/uang jaminan kartu tamu (nominal default Rp 100.000 per kartu).

**Alur:**
1. Saat check-in — catat deposit kartu tamu via **Tambah Deposit**
2. Filter daftar deposit berdasarkan tanggal atau cari no. receipt / nama tamu
3. Saat check-out — lakukan **Return Deposit** untuk mengembalikan uang jaminan

---

## 🧾 Service Charge

Mencatat biaya layanan tambahan ke kamar (minibar, laundry, telepon, snack, dll).

**Langkah:**
1. Pilih reservasi tujuan
2. Masukkan deskripsi biaya dan nominal
3. Simpan — total biaya otomatis masuk ke tagihan reservasi

---

## 🍽️ Pendapatan Resto

Mencatat transaksi restoran hotel. Bisa dikaitkan ke tagihan kamar tamu.

- **Tambah Transaksi** — catat penjualan makanan/minuman dari restoran
- Filter berdasarkan periode tanggal
- Transaksi bisa ditambahkan ke tagihan kamar tamu tertentu

---

## 🚀 Deployment

### GitHub Actions Flow

```
Push ke main → GitHub Actions → Webhook → deploy.php → git pull → migrate → optimize
```

### Setup Deployment

1. **Generate Secret:**
```bash
php -r "echo bin2hex(random_bytes(32));"
```

2. **Set di .env & GitHub Secrets:**
   - `DEPLOY_SECRET`
   - `DEPLOY_URL`

3. **GitHub Webhook:**
   - URL: `https://domain.com/deploy.php`
   - Events: Push event

---

## 🌙 Night Audit

### Night Audit v2 (Baru)

Night Audit adalah proses penutupan harian yang dilakukan setiap malam untuk mencatat semua transaksi dan status hotel.

**Fitur:**
- **Preview** — Lihat pratinjau data audit sebelum disimpan
- **Save Draft** — Simpan sebagai draft (masih bisa diedit)
- **Lock** — Kunci data final (tidak bisa diubah lagi)
- **History** — Lihat laporan night audit sebelumnya
- **Export** — Download file laporan

### Night Audit Report (v1)

Laporan ringkasan: total kamar, occupied, available, check-in/out hari ini, pendapatan (tunai, transfer, dll), expected revenue. Bisa filter tanggal, export CSV, dan print.

---

## 📧 OTA Email Monitoring Dashboard

Memantau dan mengelola email reservasi dari platform OTA secara real-time.

**Fitur Tambahan:**
- **Live Stats** — Statistik real-time jumlah email pending, sukses, dan gagal
- **Refresh Stats** — Perbarui statistik email terkini
- **Detail Parsing** — Lihat hasil parsing lengkap termasuk error detail
- **Retry** — Coba ulang proses parsing untuk email yang gagal
- **Auto-refresh** — Data otomatis diperbarui setiap 30 detik
- Filter berdasarkan **Platform OTA**, **Status**, dan **Tanggal**
- Pencarian berdasarkan subjek email atau nomor reservasi

**Tips:** Pastikan koneksi IMAP email sudah dikonfigurasi di **Setting Hotel** agar fitur ini dapat membaca email OTA secara otomatis.

---

## 📧 OTA Email Log

Memantau dan mengelola email reservasi dari platform OTA (Booking.com, Tiket.com, Traveloka) yang masuk ke sistem.

**Fitur:**
- **Refresh Stats** — Perbarui statistik email terkini
- **Detail Email** — Klik email untuk melihat isi lengkap dan data parsing
- **Retry** — Coba ulang proses parsing untuk email yang gagal
- Filter berdasarkan **Platform OTA** dan **Status**
- Pencarian berdasarkan subjek email atau nomor reservasi

**Tips:** Pastikan koneksi IMAP email sudah dikonfigurasi di **Setting Hotel** agar fitur ini dapat membaca email OTA secara otomatis.

---

## 🔌 API Documentation

### External API (API Key Auth)

Semua endpoint di bawah ini memerlukan API Key di header `X-API-Key` atau query parameter `?api_key=`.

#### API Endpoints

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `GET` | `/api/reservations` | Daftar reservasi (filter & pagination) |
| `GET` | `/api/reservations/{id}` | Detail reservasi |
| `POST` | `/api/reservations` | Buat reservasi baru |
| `PUT` | `/api/reservations/{id}` | Update reservasi |
| `POST` | `/api/reservations/{id}/cancel` | Batalkan reservasi |
| `POST` | `/api/reservations/{id}/checkin` | Check-in reservasi |
| `POST` | `/api/reservations/{id}/checkout` | Check-out reservasi |
| `POST` | `/api/reservations/{id}/change-room` | Pindah kamar |
| `POST` | `/api/reservations/{id}/payments` | Tambah pembayaran |
| `GET` | `/api/rooms` | Daftar kamar dengan status |
| `GET` | `/api/rooms/available` | Cek kamar tersedia |
| `GET` | `/api/guests` | Daftar tamu |
| `GET` | `/api/stats` | Statistik dashboard |
| `GET` | `/api/promo-prices` | Daftar promo price |
| `POST` | `/api/promo-prices` | Buat promo price baru |
| `GET` | `/api/promo-prices/effective` | Cek harga efektif untuk tanggal tertentu |

#### AI Chat

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `POST` | `/api/ai/chat` | Chat dengan AI assistant |

**Request Body:**
```json
{
  "message": "Cari kamar untuk besok",
  "current_page": "/reservations",
  "history": []
}
```

**Response:**
```json
{
  "success": true,
  "message": "Jawaban AI dalam Bahasa Indonesia"
}
```

---

## 📁 Struktur Project

```
hotel-pms/
├── app/
│   ├── Models/           # Eloquent Models
│   ├── Services/         # Business Logic
│   ├── Http/Controllers/ # Controllers
│   └── ...
├── database/
│   ├── migrations/       # Database migrations
│   └── seeders/          # Data seeders
├── resources/
│   ├── views/            # Blade templates
│   └── js/               # JavaScript
├── routes/
│   ├── web.php           # Web routes
│   └── api.php           # API routes
└── config/
    ├── menus.php         # Menu configuration
    └── services.php      # Third-party services
```

---

## 🆘 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| AI tidak merespons | Cek `OPENROUTER_API_KEY` di .env |
| Email OTA tidak masuk | Cek IMAP credentials & firewall |
| Permission denied | Clear cache: `php artisan cache:clear` |
| Deploy gagal | Cek `DEPLOY_SECRET` sama |

---

## 📞 Support

Untuk pertanyaan atau bug, buka issue di GitHub repository.