# ?? Hotel PMS � Tutorial & Dokumentasi

**Versi:** 2.4 | **Update:** Agustus 2026 | **Stack:** Laravel 13 / PHP 8.3 / MySQL / Blade + Tailwind

---

## ?? Daftar Isi
1. [Pendahuluan & Prasyarat](#-pendahuluan)
2. [Login & Test Credentials](#-login)
3. [Learning Path](#-learning-path)
4. [Navigasi Menu](#-navigasi-menu)
5. [Quick Start � Instalasi](#-quick-start)
6. [Fitur Step-by-Step](#-fitur-lengkap)
7. [Fitur Baru (Update Terbaru)](#-fitur-baru-update-terbaru)
8. [Allotment Kamar](#-allotment-kamar)
9. [Permission & Role](#-permission--role)
10. [Model, API & Struktur Project](#-model--api--struktur)
11. [Troubleshooting](#-troubleshooting)
12. [Glossary](#-glossary)
13. [Latihan Mandiri](#-latihan-mandiri)
14. [Deployment & Support](#-deployment--support)

---

## ?? Pendahuluan

**Hotel PMS** � Aplikasi manajemen hotel berbasis web: reservasi, check-in/out, housekeeping, keuangan, OTA, AI Chat, & laporan.

**Prasyarat:** PHP 8.3+, Composer, Node.js/NPM, MySQL, Git, Web Browser.  
?? Pakai Laragon? Semua sudah include � download di [laragon.org](https://laragon.org).

---

## ?? Login

| Username | Password | Role | Akses |
|----------|----------|------|-------|
| `owner` | `password` | Owner | **Semua fitur** + admin panel |
| `admin` | `password` | Admin | Semua kecuali settings sensitif |
| `frontoffice` | `password` | Front Office | Reservasi, check-in/out, HK, transaksi |

**Cara:** Buka `http://localhost:8000` ? Login ? isi username/password ? **Login**.

> ?? **Latihan 1:** Login dengan 3 akun, amati perbedaan menu!

---

## ??? Learning Path

| Level | Waktu | Topik |
|-------|-------|-------|
| ?? **Pemula** | 1-2 jam | Login, Dashboard, Kamar, Reservasi Dasar, Check-in/out |
| ?? **Menengah** | 3-5 jam | Pembayaran, Housekeeping, Laporan, Service Charge, Expense |
| ?? **Mahir** | 5-8 jam | OTA, Night Audit, Promo Pricing, AI Chat, Reports Export |
| ? **Expert** | 8+ jam | API, Role & Permission, Backup, Deployment, Troubleshooting |

---

## ?? Navigasi Menu

```
?? Sidebar
+-- ?? Front Desk       ? Reservasi, Check-In/Out, Pindah Kamar, Issue Card MHS
+-- ?? Housekeeping     ? Tasks, Checklists, Inventory, Lost & Found
+-- ?? Keuangan         ? Transaksi, Deposit, Service Charge, Expense, Pendapatan Resto
+-- ?? Laporan          ? Reports (Okupansi, Revenue, Reservations, Guest List, Group)
+-- ?? Allotment        ? Atur kuota kamar untuk website/API
+-- ?? Promo Pricing    ? Diskon harga per tipe kamar
+-- ?? API Keys         ? Generate & kelola API Key untuk integrasi
+-- ?? Admin            ? Users, Roles, OTA Log, Hotel Settings, Night Audit, Backups
```

> Menu tergantung **Role** Anda. Login sebagai `owner` untuk akses penuh.

---

## ?? Quick Start

```bash
# 1. Clone & install
git clone https://github.com/imamw95-crb/hotel-pms.git && cd hotel-pms
composer install && npm install

# 2. Setup env
cp .env.example .env
php artisan key:generate

# 3. Setup DB (edit .env: DB_DATABASE=hotel_pms, DB_USERNAME=root, DB_PASSWORD=)
php artisan migrate && php artisan db:seed

# 4. Jalankan (2 terminal)
php artisan serve    # Terminal 1
npm run dev          # Terminal 2
```

Buka `http://localhost:8000` � login `owner` / `password`.

**Konfigurasi Tambahan (.env):**
```env
OPENROUTER_API_KEY=sk-or-v1-xxx       # AI Chat
IMAP_HOST=imap.hostinger.com          # OTA Email
IMAP_USERNAME=info@theicon.id
DEPLOY_SECRET=xxx
MHS_BRIDGE_URL=http://100.98.230.92/bridge_api.php
```

---

## ?? Fitur Lengkap

### 1. ?? Dashboard
Statistik real-time: total kamar, check-in/out hari ini, pendapatan, reservasi aktif, notifikasi.

> ?? **Latihan 2:** Login sebagai `owner`, catat jumlah kamar occupied hari ini.

---

### 2. ??? Reservasi

**Buat Reservasi:** Reservasi ? + Tambah ? Pilih tipe kamar ? Pilih kamar (hijau = available) ? Isi data tamu ? Tentukan tanggal (CI: 14:00, CO: 12:00) ? Harga ? Pembayaran ? **Simpan**.

**Check-In:** Cari reservasi ? Verifikasi data & bayaran ? Klik **Check-In** ? (Opsional) Issue card MHS ? Status kamar ? `occupied`.

**Check-Out:** Buka reservasi ? Hitung tagihan + service charge ? Bayar final ? Return deposit ? Klik **Check-Out** ? Kamar ? `cleaning`, tugas HK otomatis.

> ?? **Back-to-back:** Check-out 12:00 & check-in 14:00 di kamar sama = **BUKAN konflik**.
> ?? **Latihan 3:** Buat reservasi ? check-in ? check-out. Amati perubahan status kamar.

---

### 3. ?? Housekeeping

**Akses:** Front Desk ? Housekeeping.

**Buat Tugas:** Buat Tugas ? Pilih kamar ? Tipe (`cleaning`/`deep_clean`/`maintenance`/`inspection`/`turndown`) ? Prioritas (`low`/`normal`/`high`/`urgent`) ? Deskripsi ? Assign staff ? **Simpan**.

**Bulk Create:** Buat tugas untuk banyak kamar sekaligus.

**Assign:** Manual (??) atau Auto-Assign (?) � otomatis ke staff dengan beban paling ringan.

**Kerjakan:** ?? (Mulai) ? timer berjalan ? ? (Selesai) ? upload foto, isi checklist ? durasi tercatat.

**Detail Tugas:** ??? Lihat checklist, log riwayat, foto, durasi.

> ?? **Tips:** Filter by status/tipe/prioritas/kamar/tanggal. Tugas overdue = badge merah.
> ?? **Latihan 4:** Buat 3 tugas, assign ke staff berbeda, selesaikan 1, amati statistik.

---

### 4. ?? Transaksi & Deposit

**Pembayaran:** Transaksi ? Tambah ? Pilih tipe (Bayar/Refund) ? Pilih reservasi ? Jumlah ? Metode ? **Simpan**.

**Deposit Kartu:** Saat CI: Deposit ? Tambah ? Pilih reservasi ? Rp 100.000 (default) ? Simpan.  
Saat CO: Cari deposit ? **Return Deposit** ? Masukkan jumlah kembali.

> ?? **Latihan 5:** Buat deposit untuk reservasi check-in, lalu return deposit.

---

### 5. ?? Laporan

| Laporan | Akses | Export |
|---------|-------|--------|
| Guest List | Reports ? Guest List | CSV / Print |
| Occupancy | Reports ? Occupancy | CSV / Print |
| Revenue | Reports ? Revenue | CSV / Print |
| Reservations | Reports ? Reservations | CSV / Print |
| Group Report | Reports ? Group | CSV / Print |

> ?? **Latihan 6:** Buka Occupancy bulan ini, export CSV, buka di Excel.

---

### 6. ?? Promo Pricing

Promo ? Tambah ? Pilih tipe kamar ? Tanggal mulai/selesai ? Harga promo ? **Simpan**.  
Saat reservasi di tanggal promo, harga otomatis terpakai.

> ?? **Latihan 7:** Buat promo Deluxe diskon 20% selama 3 hari, verifikasi di reservasi.

---

### 7. ?? Lost & Found

Lost & Found ? Tambah Item ? Isi nama, kategori, deskripsi, lokasi, penemu ? **Simpan**.  
Status: `reported` ? `found` ? `returned` ? `disposed`.

> ?? **Latihan 8:** Catat "Dompet Hitam" ditemukan di kamar 102, update ke `returned`.

---

### 8. ?? Hotel Settings (Owner Only)

Admin ? Settings. Konfigurasi: nama hotel, logo, IMAP (OTA), OpenRouter key, MHS Bridge URL.

> ?? Setelah ubah IMAP, cek OTA Email Log untuk verifikasi.

---

### 9. ?? Database Backup (Owner Only)

Admin ? Backups ? **Create Backup** ? download ?? atau **Restore**.  
?? Backup **setiap hari**, simpan di **2 tempat**, backup **sebelum update besar**.

---

### 10. ?? Service Charge

Service Charge ? Tambah Biaya ? Pilih reservasi ? Deskripsi (contoh: "Minibar 2x Coca Cola") ? Rp 35.000 ? **Simpan**.  
Otomatis masuk tagihan kamar.

> ?? **Latihan 9:** Tambah service charge laundry Rp 75.000 ke reservasi check-in.

---

### 11. ??? Pendapatan Resto & 12. ?? Issue Card MHS

**Resto:** Pendapatan Resto ? Tambah ? Deskripsi, jumlah ? (Opsional) hubungkan ke tagihan kamar.

**Issue Card MHS:** Cari reservasi ? Atur jumlah kartu ? **Issue Card**. Fitur: Test Connection, Read Card, Re-Issue.

---

### 13. ?? Pindah Kamar & 14. ?? Night Audit

**Pindah Kamar:** Pilih reservasi ? Pilih kamar baru (available) ? Alasan ? **Pindahkan**.

**Night Audit:** Admin ? Night Audit ? **Preview** ? Periksa data occupied, CI/CO, pendapatan ? **Save Draft** atau **Lock**. Lakukan **setiap malam**.

---

> **?? Detail lengkap?** Lihat bagian [**?? Allotment Kamar**](#-allotment-kamar) di atas.

---

### 16. 📧 OTA Integration

OTA Email Log: Lihat email dari Tiket.com/Traveloka ? ? Success / ? Pending / ? Failed ? Klik detail ? **Retry** jika gagal.

Pastikan IMAP dikonfigurasi di **Hotel Settings**.

> ?? **Latihan 12:** Cek OTA Email Log, retry email yang gagal.

---

### 17. ?? AI Chat Assistant

Klik ?? di pojok kanan bawah ? Ketik dalam Bahasa Indonesia, contoh:  
- *"Cari kamar deluxe tersedia 3 malam mulai besok"*  
- *"Booking deluxe 102 untuk 2 malam atas nama Budi Santoso"*

AI bisa auto-create reservasi. Pastikan `OPENROUTER_API_KEY` terisi.

---

### 18. ?? MCP Server (AI Integration)

MCP (Model Context Protocol) server terintegrasi untuk menyediakan tools AI ke aplikasi eksternal.

**Resources Tersedia:** `hotel://reservations`, `hotel://rooms`, `hotel://guests`, `hotel://stats`

**Tools Tersedia:** `create-reservation`, `search-reservations`, `get-room-availability`, `get-hotel-stats`

---

### 19. ?? API Key Management

Admin ? API Keys ? **Generate Key** ? Simpan key (hanya muncul sekali).

**Gunakan API Key:**
```bash
# Via header
curl -H "X-API-Key: hms_xxx" http://localhost:8000/api/stats

# Via query parameter
curl "http://localhost:8000/api/stats?api_key=hms_xxx"
```

> ?? **Latihan 13:** Generate API Key, gunakan untuk akses endpoint `/api/stats`.

---

### 20. ??? Out of Order

Menandai kamar yang tidak bisa dioperasikan (renovasi, rusak, dll).

**Cara:** Out of Order ? Tambah ? Pilih kamar ? Tanggal mulai & selesai ? Alasan ? **Simpan**.

Kamar OOO tidak akan muncul di pencarian kamar tersedia.

---

### 21. ?? Housekeeping Checklist & Inventory

**Checklist:** Setiap tugas HK memiliki daftar periksa yang harus diisi saat eksekusi. Staff centang item checklist saat mengerjakan tugas.

**Inventory:** Catat stok perlengkapan kamar (handuk, linen, amenities, dll). Inventory ? Tambah item ? Nama, jumlah, satuan ? **Simpan**.

---

### 22. ?? Expense & Refund

**Expense:** Catat pengeluaran hotel (listrik, gaji, maintenance, dll). Admin ? Expense ? Tambah ? Deskripsi, jumlah, kategori, tanggal ? **Simpan**.

**Refund:** Admin ? Transaksi ? Tambah ? Pilih tipe `Refund` ? Pilih reservasi ? Jumlah ? **Simpan**. Otomatis mengurangi `paid_amount`.

---

### 23. ?? OTA Payment Status

Booking dari OTA memiliki status pembayaran terpisah:
| Status | Arti |
|--------|------|
| `paid_ota` | Sudah dibayar OTA |
| `partial_ota` | Dibayar sebagian oleh OTA |
| `unpaid_ota` | Belum dibayar OTA |

Saat tambah pembayaran, jumlah OTA + hotel bisa diisi terpisah.

---

## ?? Fitur Baru (Update Terbaru)

Bagian ini mendokumentasikan fitur-fitur terbaru yang ditambahkan setelah versi 2.3.

### 24. ?? Booking Group (Reservasi Kelompok)

Membuat beberapa reservasi kamar sekaligus dalam satu group (misal: rombongan, tour, keluarga besar).

**Cara:** Booking Group ? Isi data tamu utama ? Pilih beberapa kamar ? Tentukan tanggal (CI 14:00, CO 12:00) ? Harga per kamar (bisa custom atau dinamis weekday/weekend) ? DP per kamar ? **Simpan**.

**Fitur Group:**
- **Pelunasan Group:** Bayar sisa tagihan semua reservasi dalam group sekaligus.
- **Tambah Kamar:** Tambah kamar baru ke group yang sudah ada.
- **Invoice / Kwitansi / Registration Card Group:** Cetak dokumen gabungan untuk seluruh group.
- **Ubah Tanggal Group:** Ganti tanggal check-in/check-out SEMUA reservasi dalam group sekaligus (lihat #25).

> **Tips:** Harga dinamis otomatis menghitung weekday/weekend. Jika isi harga custom, itu yang dipakai; jika kosong, sistem hitung otomatis.
> **Latihan:** Buat group 2 kamar untuk 3 malam, lalu cetak invoice group.

---

### 25. ?? Ubah Tanggal Group (Change Group Dates)

Mengubah tanggal check-in/check-out untuk **semua reservasi dalam satu booking group** sekaligus, tanpa harus ubah satu per satu.

**Cara:** Buka detail reservasi group ? Klik tombol **"Ubah Tanggal Group"** (oranye) ? Set tanggal baru ? Preview total ? **Simpan**.

**Yang terjadi otomatis:**
- Semua reservasi (status pending/menunggu_pembayaran/checked_in) ikut berubah tanggalnya.
- Sistem validasi ketersediaan semua kamar (back-to-back tetap aman).
- Allotment tanggal lama dikurangi, tanggal baru ditambah (untuk channel API).
- Total dihitung ulang (custom rate × malam ATAU dinamis weekday/weekend).
- Invoice proof (OTS) di-reset, dan dicatat transaksi `adjustment` berisi riwayat perubahan tanggal.

> **Akses:** Butuh permission `change_room` (sama seperti pindah kamar).
> **Latihan:** Buat group 2 kamar, ubah tanggal dari 02-05 Agu menjadi 04-08 Agu, verifikasi kedua reservasi ikut berubah.

---

### 26. ?? Room Rack & Occupancy Calendar (Drag & Drop)

**Room Rack:** Tampilan grid semua kamar per tanggal untuk melihat okupansi sekaligus.

**Fitur:**
- **Occupancy Calendar:** Kalender okupansi per kamar.
- **Drag & Drop Pindah Kamar:** Seret booking tamu dari kamar occupied ke kamar available lain ? sistem cek ketersediaan via AJAX ? konfirmasi modal ? kamar dipindahkan.
- **Forecast:** Prediksi okupansi ke depan.
- **Check Availability:** Cek ketersediaan kamar untuk tanggal tertentu.

> **Latihan:** Buka Room Rack ? Occupancy ? seret booking dari kamar 0101 ke 0102 (yang available) ? konfirmasi.

---

### 27. ?? Public Invoice & QR Code

Setiap reservasi punya invoice online yang bisa diakses publik (tanpa login) via link atau QR code.

**Cara akses:** `GET /invoice/{nomor_reservasi}` ? tampil invoice lengkap.

**QR Code:** Muncul di footer invoice print (individu & group). QR mengarah ke `/invoice/{nomor_reservasi}`. Tamu bisa scan untuk lihat invoice online.

> **Tips:** QR memakai `api.qrserver.com` (jangan ganti ke Google Charts — diblokir CORS).
> **Latihan:** Cetak invoice reservasi, scan QR-nya, verifikasi invoice online terbuka.

---

### 28. ?? OpenTimestamps (OTS) — Bukti Integritas Invoice

Sistem menandatangani invoice dengan **OpenTimestamps** untuk membuktikan invoice tidak diubah (tamper-proof).

**Alur:** Setiap invoice/transaksi di-hash (SHA256) ? proof dikirim ke blockchain Bitcoin ? status `pending` ? `confirming` ? `confirmed`.

**Fitur:**
- **Download OTS Proof:** Unduh bukti OTS per invoice atau per transaksi.
- **Verify:** `GET /ots/verify/{sha256}` ? cek keaslian proof.
- **Cron:** `php artisan ots:upgrade --limit=20` berjalan tiap jam untuk upgrade status.

> **Latihan:** Buka invoice online ? download OTS proof ? verifikasi hash-nya.

---

### 29. ?? Guest Place & Date of Birth

Data tamu kini menyimpan **tempat lahir** (`place_of_birth`) dan **tanggal lahir** (`date_of_birth`).

**Diisi saat:** Buat reservasi, booking, atau CRUD guest. Ditampilkan di detail reservasi & registration card.

> **Latihan:** Buat reservasi baru, isi tempat & tanggal lahir tamu, cek tampil di registration card.

---

### 30. ?? Night Audit v2 (Preview, Draft, Lock, History)

Versi baru night audit dengan alur lebih terkontrol.

**Alur:**
1. **Preview:** Lihat ringkasan data (occupied, CI/CO, pendapatan) sebelum disimpan.
2. **Save Draft:** Simpan sebagai draft (belum final).
3. **Lock:** Kunci night audit (final, tidak bisa diubah).
4. **History:** Lihat riwayat night audit sebelumnya + export.

> **Latihan:** Buka Night Audit v2 ? Preview ? Save Draft ? Lock ? cek history.

---

### 31. ?? Laporan Tambahan

| Laporan | Route | Export |
|---------|-------|--------|
| Expense (Pengeluaran) | Reports ? Expenses | CSV / Print |
| Monthly Compliance | Reports ? Compliance | CSV / Print |
| OTA Report | Reports ? OTA | CSV |
| MHS Audit | Reports ? MHS Audit | - |

**Expense Report:** Rekap pengeluaran hotel per periode.
**Compliance Report:** Laporan kepatuhan bulanan hotel.
**OTA Report:** Rekap booking dari OTA (Tiket.com/Traveloka).
**MHS Audit:** Audit aktivitas issue card MHS.

> **Latihan:** Buka Compliance Report bulan ini, export CSV.

---

### 32. ?? TV Welcome Screen

Layar sambutan tamu di TV kamar (publik, tanpa login).

**Route:** `GET /tv/{room}` ? tampil welcome screen. `GET /tv/{room}/status` ? status kamar.

**Setting:** Admin ? TV Settings ? atur tampilan (nama hotel, logo, pesan).

> **Latihan:** Buka `/tv/101` di browser, lihat welcome screen.

---

### 33. ?? Batch Check-in / Check-out

Proses check-in atau check-out **banyak reservasi sekaligus**.

**Batch Check-in:** Halaman Check-in ? pilih beberapa reservasi ? **Batch Check-in**.
**Batch Check-out:** Halaman Check-out ? pilih beberapa ? **Batch Check-out**.

> **Latihan:** Pilih 3 reservasi yang sudah waktunya check-out, lakukan batch check-out.

---

### 34. ?? Breakfast Toggle & Extend Stay

**Breakfast:** Tombol toggle sarapan di detail reservasi ? menambah/mengurangi biaya sarapan ke tagihan.

**Extend Stay:** Perpanjang masa menginap tamu yang sedang check-in (tambah malam).

> **Latihan:** Buka reservasi check-in ? toggle breakfast ? extend stay 1 malam.

---

### 35. ?? Auto-Cancel Pending

Dashboard punya tombol **Auto-Cancel Pending** untuk otomatis membatalkan reservasi `pending` yang sudah melewati batas waktu (no-show).

> **Latihan:** Klik Auto-Cancel Pending di dashboard, cek reservasi pending yang dibatalkan.

---

### 36. ?? Payment Method Management (Owner)

Kelola master metode pembayaran (tunai, transfer, QRIS, kartu, dll).

**Cara:** Admin ? Payment Methods ? Tambah/Edit/Hapus metode.

> **Latihan:** Tambah metode "QRIS", lalu gunakan saat tambah pembayaran reservasi.

---

## ?? Allotment Kamar

Mengatur kuota kamar yang tampil di website publik (`theicon.id`). Fitur ini penting agar tipe kamar yang stoknya terbatas atau sedang tidak aktif tidak muncul di website.

### Aturan Dasar
- Hanya tipe kamar yang **punya allotment** (`channel='api'`) yang tampil di website
- Tipe kamar **tanpa allotment** → **tidak tampil sama sekali**
- Jumlah kamar yang tampil = `min(allotment - booked)` di seluruh range tanggal
- Harga efektif: harga allotment (jika di-set) → fallback ke harga master kamar

### Cara Penggunaan

**Admin Panel:** Allotment → **Tambah** → Pilih tipe kamar → Isi tanggal, jumlah kuota, harga opsional → **Simpan**.

### Alur Sistem

```mermaid
flowchart LR
    A[Admin set allotment<br/>channel=api] --> B[Webhotel panggil<br/>GET /api/rooms/available]
    B --> C{Kamar dikelompokkan<br/>per room_type_id}
    C --> D[Cek allotment di<br/>range tanggal kunjungan]
    D -->|Tidak ada allotment| E[Tipe kamar<br/>TIDAK tampil]
    D -->|Ada allotment| F[Hitung sisa kuota<br/>min(allotment - booked)]
    F --> G[Tampilkan<br/>sisa kamar]
```

### Logika Cek Allotment

Saat website memanggil `GET /api/rooms/available`, sistem **wajib** mengecek apakah tipe kamar punya allotment di range tanggal yang diminta. Berikut inti logikanya:

**1. Query allotment per tipe kamar + range tanggal**
```php
$allotments = Allotment::where('room_type_id', $roomTypeId)
    ->where('date', '>=', $checkInDate)
    ->where('date', '<',  $checkOutDate)   // eksklusif check-out
    ->where(function ($q) {
        $q->where('channel', 'api')        // hanya channel api yg tampil di web
          ->orWhereNull('channel');        // atau yg belum di-set channel
    })
    ->orderBy('date')
    ->get();
```

**2. Keputusan "Ada" vs "Tidak Ada"**

| Kondisi | Hasil | Tindakan |
|---------|-------|----------|
| `$allotments->isEmpty()` | **TIDAK ADA** allotment | Tipe kamar **tidak tampil sama sekali** (`return collect()`) |
| `$allotments` terisi | **ADA** allotment | Lanjut hitung sisa kuota |

```php
if ($allotments->isEmpty()) {
    // Tidak ada allotment = jangan tampilkan tipe ini
    return collect();
}
```

**3. Hitung sisa kuota minimal (jika ADA)**
```php
$minAvailable = $allotments->min(fn($a) => $a->allotment - $a->booked);
$limit = max(0, (int) $minAvailable);   // jangan negatif
```

Tipe kamar hanya menampilkan `$limit` kamar = nilai terkecil dari `(allotment - booked)` di seluruh tanggal menginap.

### Helper `Allotment::isAvailable()`

Model `Allotment` juga punya method cepat untuk cek 1 tanggal:
```php
Allotment::isAvailable($roomTypeId, $date, 'api'); // true = masih sisa
```
- Return `true` jika **tidak ada baris allotment** (dianggap unlimited).
- Return `true`/`false` berdasarkan `booked < allotment` jika baris ada.

> **?? Ringkas:** Tidak ada allotment → tipe kamar **hilang** dari website. Ada allotment → tampil `min(allotment - booked)` kamar. Method `limitAvailablePerType()` **sudah tidak dipakai**.

### Latihan

> **?? Latihan A:** Set allotment 5 kamar Deluxe untuk 7 hari ke depan, lalu verifikasi di website bahwa hanya 5 kamar yang tampil.
> **?? Latihan B:** Hapus allotment sebuah tipe kamar → panggil `/api/rooms/available` → pastikan tipe tersebut tidak muncul. Lalu buat allotment 3 kamar → pastikan hanya 3 yang tampil.

---

## ?? Permission & Role

| Role | Level | Akses |
|------|-------|-------|
| `owner` | ????? | Semua fitur |
| `admin` | ???? | Semua kecuali settings sensitif |
| `frontoffice` | ??? | Operasional harian |
| `user_manager` | ??? | Kelola user (tanpa admin panel) |

**Kelola User:** Admin ? Users ? Tambah/Edit ? Atur Role.  
**Kelola Permission:** Admin ? Roles ? Edit Permission ? Centang ? Simpan.

```blade
@if(hasPermission('view_reports')) ... @endif
@if(hasAllPermissions(['view_reports','export_reports'])) ... @endif
@if(hasAnyPermission(['manage_users','manage_rooms'])) ... @endif
```

```php
// Middleware
Route::get('/reports', ...)->middleware('permission:view_reports');
Route::group(['middleware' => ['role:owner']], function () { ... });
```

> ?? **Latihan 14:** Login owner ? Admin ? Roles ? edit permission frontoffice, lalu cek perubahannya.

---

## ?? Model, API & Struktur

### Core Models

| Model | Tabel | Relasi |
|-------|-------|--------|
| Room | rooms | ? RoomType, hasMany Reservation |
| RoomType | room_types | hasMany Room, hasMany RoomTypeDatePrice |
| Allotment | allotments | ? RoomType |
| Reservation | reservations | ? Room, Guest, User |
| Guest | guests | hasMany Reservation |
| Transaction | transactions | ? Reservation, PaymentMethod |
| User | users | ? Role |
| Role | roles | hasMany User, belongsToMany Permission |
| Permission | permissions | belongsToMany Role |
| HousekeepingTask | housekeeping_tasks | ? Room, User |
| HousekeepingTaskChecklist | housekeeping_task_checklists | ? HousekeepingTask |
| HousekeepingInventory | housekeeping_inventories | - |
| Deposit | deposits | ? Reservation |
| ServiceCharge | service_charges | ? Reservation |
| Expense | expenses | - |
| RestoTransaction | resto_transactions | ? Reservation (opsional) |
| OutOfOrder | out_of_orders | ? Room |
| BookingNotification | booking_notifications | morphTo |
| NightAuditLog | night_audit_logs | - |
| LostFound | lost_founds | ? Reservation (opsional) |
| HotelSetting | hotel_settings | - |
| PaymentMethod | payment_methods | hasMany Transaction |
| InvoiceTimestamp | invoice_timestamps | - (OTS proof) |
| HousekeepingTaskLog | housekeeping_task_logs | ? HousekeepingTask |
| HousekeepingTaskChecklist | housekeeping_task_checklists | ? HousekeepingTask |
| MHSLog | mhs_logs | - |
| ProcessedEmail | processed_emails | - (OTA dedup) |
| RoomTypeDatePrice | room_type_date_prices | ? RoomType (promo) |

### Business Logic Penting
- **Check-in:** 14:00 | **Check-out:** 12:00
- **Back-to-back:** CI 14:00 setelah CO 12:00 di kamar sama = ? **AMAN**
- **Overlap query:** `where('check_in', '<', $checkOut)->where('check_out', '>', $checkIn)`
- **Room status:** `available` ?? ? `occupied` ?? ? `cleaning` ?? ? `maintenance` ?
- **Reservation status:** `pending` ? `checked_in` ? `checked_out` / `cancelled`

### Services

| Service | Fungsi |
|---------|--------|
| ReservationService | Logika bisnis reservasi (create, cancel) + DB locking |
| AiChatService / OpenRouterService | AI Chat |
| AvailabilityService | Cek ketersediaan kamar |
| BookingSyncService / ImapService / EmailParserService / BookingMapperService | OTA Integration |
| MHSBridgeService | Issue card MHS |
| HousekeepingService | Logika housekeeping (auto-assign, timer) |
| OpenTimestampService | OpenTimestamps (OTS) proof & verify |
| BookingNotificationService | Notifikasi booking |
| InvoiceSignatureService | Tanda tangan invoice |
| AiActionService | Aksi AI (auto-reservasi) |

### API Endpoints (Auth: `X-API-Key`)

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET | `/api/reservations` | Daftar reservasi (filter: search, status, date) |
| GET | `/api/reservations/{id}` | Detail reservasi + transaksi |
| POST | `/api/reservations` | Buat reservasi |
| PUT | `/api/reservations/{id}` | Update reservasi |
| POST | `/api/reservations/{id}/cancel` | Batalkan reservasi |
| POST | `/api/reservations/{id}/checkin` | Check-in |
| POST | `/api/reservations/{id}/checkout` | Check-out |
| POST | `/api/reservations/{id}/change-room` | Pindah kamar |
| POST | `/api/reservations/{id}/payments` | Tambah pembayaran |
| PATCH | `/api/reservations/{id}/total` | Update total amount |
| PATCH | `/api/reservations/{id}/room-rate` | Update harga kamar |
| GET | `/api/reservations/checked-in` | Daftar reservasi check-in |
| GET | `/api/rooms` | Daftar kamar |
| GET | `/api/rooms/available` | Kamar tersedia (dengan allotment) |
| GET | `/api/guests` | Daftar tamu |
| GET | `/api/stats` | Statistik dashboard |
| GET/POST | `/api/promo-prices` | Promo pricing |
| GET | `/api/promo-prices/check` | Cek harga promo |
| GET | `/api/room-types/prices` | Harga per tipe kamar |
| GET/POST/PUT/DELETE | `/api/allotments` | CRUD allotment |
| GET | `/api/allotments/check` | Cek ketersediaan allotment |
| GET | `/api/allotments/summary` | Ringkasan allotment |
| POST | `/api/ai/chat` | AI Chat |
| POST | `/api/v1/api-keys` | Generate API Key |
| GET | `/api/v1/api-keys` | List API Keys |
| GET | `/api/ots/invoices/{id}` | Detail OTS invoice |
| GET | `/api/ots/transactions/{id}` | Detail OTS transaksi |
| POST | `/api/ots/upgrade` | Upgrade status OTS |

### Struktur Project

```
hotel-pms/
+-- app/          ? Models, Services, Http/Controllers, Jobs, Providers
+-- config/       ? app.php, menus.php, services.php
+-- database/     ? migrations/, factories/, seeders/
+-- resources/    ? views/ (Blade), css/, js/
+-- routes/       ? web.php, api.php, console.php
+-- storage/      ? app/, logs/, framework/
+-- tests/        ? Feature/, Unit/
+-- public/       ? index.php, assets/, build/
```

---

## ?? Troubleshooting

| # | Masalah | Solusi |
|---|---------|--------|
| 1 | **Halaman putih/500** | `php artisan cache:clear` + `php artisan config:clear` |
| 2 | **Login gagal** | Reset password di DB |
| 3 | **AI tidak merespon** | Cek `OPENROUTER_API_KEY` di .env |
| 4 | **Email OTA tidak masuk** | Cek IMAP di Hotel Settings |
| 5 | **Permission denied** | `php artisan cache:clear` |
| 6 | **Kamar tidak muncul** | `php artisan db:seed --class=RoomSeeder` |
| 7 | **Menu tidak lengkap** | Login sebagai `owner` |
| 8 | **Chart tidak tampil** | `npm run build` atau refresh |
| 9 | **QR invoice tidak muncul** | Pastikan pakai `api.qrserver.com` (bukan Google Charts) |
| 10 | **OTS status stuck pending** | Jalankan `php artisan ots:upgrade` |
| 11 | **AI OTA gagal parse** | Cek `OPENROUTER_MODEL` di .env, retry dari OTA Email Log |
| 12 | **Drag & drop pindah kamar gagal** | Pastikan kamar tujuan available & tidak back-to-back false conflict |

**Common Mistakes:**
- ? Lupa `migrate` ? data tidak muncul
- ? Salah paham back-to-back ? dianggap konflik padahal aman
- ? Tidak pakai `with()` ? N+1 query (lambat)
- ? Lupa filter status `cancelled` di query
- ? Tidak backup sebelum migrasi ? data hilang
- ? Mengubah tanggal group tapi lupa cek ketersediaan semua kamar
- ? Menyimpan file dengan encoding non-UTF-8 ? emoji/special char rusak (jadi `??`)

**Debugging flow:** Error ? Cek `storage/logs/laravel.log` ? SQL error? ? `migrate:fresh --seed` | PHP error? ? `composer update` | JS error? ? F12 console ? Refresh & clear cache.

---

## ?? Glossary

| Istilah | Arti |
|---------|------|
| **OTA** | Online Travel Agent (Tiket.com, Traveloka) |
| **PMS** | Property Management System |
| **Back-to-back** | Tamu baru check-in di hari tamu lama check-out |
| **Night Audit** | Penutupan akhir hari |
| **MHS** | Magic Hotel System (pembuat kartu kamar) |
| **IMAP** | Protokol baca email |
| **OpenRouter** | Gateway API untuk AI (LLM) |
| **Eloquent** | ORM Laravel |
| **Blade** | Template engine Laravel |
| **Eager Loading** | Load relasi DB sekaligus (`with()`) |
| **OTS** | OpenTimestamps — bukti integritas invoice via blockchain |
| **Booking Group** | Kelompok beberapa reservasi dalam satu transaksi |
| **Room Rack** | Grid okupansi semua kamar per tanggal |
| **Allotment** | Kuota kamar yang tampil di website/API |
| **QRIS** | Metode pembayaran QR Indonesia |

---

## ?? Latihan Mandiri

### ?? Pemula
**A.** Login `frontoffice` ? buat reservasi "Siti Rahma" Deluxe ? check-in ? service charge Rp 50.000 ? check-out ? verifikasi kamar jadi `cleaning`.  
**B.** Login `owner` ? buka semua menu, catat fiturnya. Bandingkan dengan login `frontoffice`.

### ?? Menengah
**C.** Bulk create 5 tugas cleaning ? assign 2 ke staff A, 3 ke staff B ? selesaikan 3 tugas ? cek workload & chart.  
**D.** Buka Occupancy Report bulan ini ? filter Deluxe ? export CSV ? buka di Excel.

### ?? Mahir
**E.** Buat promo Standard Room diskon 25% (besok-3hari) ? reservasi di tanggal promo (harga promo) & di luar (harga normal).  
**F.** Login owner ? buat user `frontoffice` baru ? login sbg user itu ? catat menu ? edit permission-nya ? refresh.

### ? Expert
**G.** Dapatkan API Key ? coba `curl -H "X-API-Key: key" http://localhost:8000/api/rooms` ? buat reservasi via API.  
**H.** `php artisan cache:clear` ? hapus isi `storage/logs/` ? akses URL salah ? cek log ? identifikasi error.

### ?? Fitur Baru
**I.** Buat booking group 2 kamar ? ubah tanggal group ? verifikasi semua reservasi ikut berubah + transaksi `adjustment` tercatat.  
**J.** Buka Room Rack ? Occupancy ? drag & drop pindah kamar ? verifikasi kamar berpindah.  
**K.** Cetak invoice ? scan QR ? buka invoice online ? download OTS proof ? verifikasi.  
**L.** Buat reservasi dengan tempat & tanggal lahir tamu ? cek registration card.  
**M.** Jalankan Night Audit v2: Preview ? Save Draft ? Lock ? cek history & export.

---

## ?? Deployment & Support

### Deployment
```mermaid
flowchart LR
    A[Push main] --> B[GitHub Actions] --> C[Webhook] --> D[deploy.php]
    D --> E[git pull] --> F[composer install] --> G[migrate] --> H[optimize]
```

**Setup:** Generate secret (`php -r "echo bin2hex(random_bytes(32));"`) ? Set `DEPLOY_SECRET` & `DEPLOY_URL` di .env & GitHub Secrets ? Konfigurasi webhook (URL: `https://domain.com/deploy.php`, events: Push main).

### Support
1. Baca **Troubleshooting** di atas
2. Cek `storage/logs/laravel.log`
3. Buka **GitHub Issues**
4. `php artisan db:monitor` untuk debug query

---

> **?? Tip:** Praktek adalah cara terbaik belajar. Kerjakan latihan A?H berurutan. Selamat belajar! ??