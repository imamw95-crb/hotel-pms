@extends('layouts.app')

@section('title', 'Panduan Penggunaan')
@section('header', 'Panduan Penggunaan Dynamic PMS V.2')

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Tombol Print --}}
    <div class="mb-6 flex justify-end no-print">
        <button onclick="window.print()"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2 text-sm shadow-sm">
            <i class="fas fa-print"></i> Cetak Panduan
        </button>
    </div>

    {{-- Daftar Isi --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 no-print">
        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-list text-blue-500"></i> Daftar Isi
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-1 text-sm">
            <a href="#login" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-sign-in-alt mr-1.5 w-4 text-center"></i> Login & Akses</a>
            <a href="#dashboard" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-th-large mr-1.5 w-4 text-center"></i> Dashboard Kamar</a>
            <a href="#room-rack" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-bed mr-1.5 w-4 text-center"></i> Room Rack & Availability</a>
            <a href="#booking" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-calendar-plus mr-1.5 w-4 text-center"></i> Booking / Reservasi</a>
            <a href="#checkin" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-sign-in-alt mr-1.5 w-4 text-center"></i> Check-in</a>
            <a href="#checkout" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-sign-out-alt mr-1.5 w-4 text-center"></i> Check-out</a>
            <a href="#reservation" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-file-alt mr-1.5 w-4 text-center"></i> Detail Reservasi</a>
            <a href="#room-change" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-exchange-alt mr-1.5 w-4 text-center"></i> Pindah Kamar</a>
            <a href="#issue-card" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-id-card mr-1.5 w-4 text-center"></i> Issue Card MHS</a>
            <a href="#deposit" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-credit-card mr-1.5 w-4 text-center"></i> Deposit Kartu</a>
            <a href="#service-charge" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-receipt mr-1.5 w-4 text-center"></i> Service Charge</a>
            <a href="#resto" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-utensils mr-1.5 w-4 text-center"></i> Pendapatan Resto</a>
            <a href="#housekeeping" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-broom mr-1.5 w-4 text-center"></i> Housekeeping</a>
            <a href="#night-audit" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-moon mr-1.5 w-4 text-center"></i> Night Audit</a>
            <a href="#reports" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-chart-bar mr-1.5 w-4 text-center"></i> Laporan (Reports)</a>
            <a href="#guests" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-users mr-1.5 w-4 text-center"></i> Manajemen Tamu</a>
            <a href="#rooms" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-door-open mr-1.5 w-4 text-center"></i> Kamar & Tipe Kamar</a>
            <a href="#ai-chat" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-robot mr-1.5 w-4 text-center"></i> AI Chat Assistant</a>
            <a href="#admin" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-cog mr-1.5 w-4 text-center"></i> Administrasi (Owner)</a>
            <a href="#api" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-plug mr-1.5 w-4 text-center"></i> API Eksternal</a>
            <a href="#ota-email" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-envelope-open-text mr-1.5 w-4 text-center"></i> Log Email OTA</a>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 1. LOGIN --}}
    {{-- ================================================================ --}}
    <div id="login" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-sign-in-alt text-blue-500"></i> 1. Login & Akses
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <ul class="space-y-2 text-sm text-gray-700 ml-2">
                <li><i class="fas fa-circle text-blue-400 text-[6px] mr-2 align-middle"></i>Buka URL sistem di browser (contoh: <code class="bg-gray-100 px-1.5 rounded text-xs">http://127.0.0.1:8000</code>)</li>
                <li><i class="fas fa-circle text-blue-400 text-[6px] mr-2 align-middle"></i>Masukkan <strong>email</strong> dan <strong>password</strong> yang terdaftar</li>
                <li><i class="fas fa-circle text-blue-400 text-[6px] mr-2 align-middle"></i>Klik tombol <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs">Login</span></li>
            </ul>
            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm">
                <p class="font-semibold text-blue-800 mb-2"><i class="fas fa-info-circle mr-1"></i> Role Pengguna</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    <div class="bg-white rounded p-3 border border-blue-100">
                        <span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded font-bold text-xs">Owner</span>
                        <p class="mt-1 text-gray-600">Akses penuh ke semua fitur termasuk administrasi, backup DB, manajemen user & permission</p>
                    </div>
                    <div class="bg-white rounded p-3 border border-blue-100">
                        <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-bold text-xs">Admin</span>
                        <p class="mt-1 text-gray-600">Akses operasional penuh tanpa fitur administrasi owner</p>
                    </div>
                    <div class="bg-white rounded p-3 border border-blue-100">
                        <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded font-bold text-xs">Front Office</span>
                        <p class="mt-1 text-gray-600">Akses terbatas pada front desk (reservasi, check-in/out, pembayaran)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 2. DASHBOARD --}}
    {{-- ================================================================ --}}
    <div id="dashboard" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-th-large text-emerald-500"></i> 2. Dashboard Kamar
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Halaman utama setelah login. Menampilkan status seluruh kamar secara real-time.</p>
            <ul class="space-y-2 text-sm text-gray-700 ml-2">
                <li><i class="fas fa-circle text-emerald-400 text-[6px] mr-2 align-middle"></i><strong>Ringkasan Statistik</strong> — Available, Occupied, Check-in hari ini, Check-out hari ini, Dirty, Maintenance, Okupansi (%)</li>
                <li><i class="fas fa-circle text-emerald-400 text-[6px] mr-2 align-middle"></i><strong>Grid Kamar</strong> — Semua kamar dengan warna status:</li>
            </ul>
            <div class="flex flex-wrap gap-2 my-3 ml-4 text-xs">
                <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full font-medium border border-emerald-200">🟢 Available</span>
                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full font-medium border border-red-200">🔴 Occupied</span>
                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-medium border border-yellow-200">🟡 Cleaning</span>
                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full font-medium border border-gray-200">⚪ Maintenance</span>
            </div>
            <ul class="space-y-2 text-sm text-gray-700 ml-2">
                <li><i class="fas fa-circle text-emerald-400 text-[6px] mr-2 align-middle"></i>Klik kamar <strong>Available</strong> untuk langsung membuat booking</li>
                <li><i class="fas fa-circle text-emerald-400 text-[6px] mr-2 align-middle"></i>Pilih banyak kamar lalu klik <strong>Bulk Update Status</strong> untuk ubah status massal</li>
            </ul>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 3. ROOM RACK --}}
    {{-- ================================================================ --}}
    <div id="room-rack" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-bed text-indigo-500"></i> 3. Room Rack & Availability
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Fitur untuk melihat dan mengecek ketersediaan kamar dengan 3 tampilan:</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-bold text-gray-800 mb-2">📊 Grid Kamar</h4>
                    <p class="text-gray-600 text-xs">Tampilan grid semua kamar dengan warna status — mirip Dashboard.</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-bold text-gray-800 mb-2">📋 Room Rack</h4>
                    <p class="text-gray-600 text-xs">Tampilan rack tradisional per lantai/tipe kamar — cocok untuk cek availability harian.</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-bold text-gray-800 mb-2">📈 Forecast</h4>
                    <p class="text-gray-600 text-xs">Prediksi ketersediaan kamar ke depan — membantu perencanaan okupansi.</p>
                </div>
            </div>
            <div class="mt-3 ml-2">
                <p class="text-sm text-gray-700"><i class="fas fa-calendar-alt text-indigo-400 mr-1"></i> <strong>Occupancy Calendar</strong> — Kalender okupansi harian untuk melihat tren tingkat hunian.</p>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 4. BOOKING --}}
    {{-- ================================================================ --}}
    <div id="booking" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-calendar-plus text-orange-500"></i> 4. Booking / Reservasi
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <h4 class="font-bold text-sm mb-2">📌 Booking Single</h4>
            <ol class="list-decimal ml-5 space-y-1.5 text-sm text-gray-700 mb-4">
                <li>Pilih tanggal <strong>Check-in</strong> (minimal pukul 14:00) dan <strong>Check-out</strong> (minimal pukul 12:00)</li>
                <li>Sistem otomatis menampilkan kamar yang tersedia (tanpa overlap jadwal)</li>
                <li>Pilih kamar yang diinginkan</li>
                <li>Isi data tamu: <em>Nama, No. Identitas, Telepon, Alamat (opsional), Email (opsional)</em></li>
                <li>Atur <strong>Harga per Malam</strong> — otomatis terisi harga weekday/weekend, bisa diubah manual. Kosongkan untuk harga otomatis.</li>
                <li>Centang <strong>Include Breakfast</strong> jika termasuk sarapan</li>
                <li>Isi <strong>DP (Down Payment)</strong> — nominal uang muka jika ada. Sistem akan otomatis mencatat pembayaran DP.</li>
                <li>Pilih <strong>Metode Pembayaran</strong> — Tunai, Transfer Bank, QRIS, atau Debit/Kredit Card</li>
                <li>Klik <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs">Simpan</span></li>
            </ol>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm mb-4">
                <p><i class="fas fa-lightbulb text-yellow-600 mr-1"></i> <strong>Back-to-Back Booking:</strong> Check-out jam 12:00 dan check-in jam 14:00 di hari yang sama <strong>DIPERBOLEHKAN</strong> — tidak dianggap konflik.</p>
            </div>
            <h4 class="font-bold text-sm mb-2 mt-4">📌 Booking Group</h4>
            <p class="text-sm text-gray-700 mb-2">Untuk reservasi beberapa kamar sekaligus (rombongan, wedding, dll).</p>
            <ol class="list-decimal ml-5 space-y-1.5 text-sm text-gray-700">
                <li>Masukkan <strong>Nama Grup</strong> (contoh: "Rombongan Wedding")</li>
                <li>Pilih tanggal Check-in dan Check-out</li>
                <li>Untuk setiap kamar: pilih kamar, isi data tamu, atur harga</li>
                <li>Klik <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs">Simpan</span></li>
            </ol>
            <h4 class="font-bold text-sm mb-2 mt-4">📌 Booking OTA</h4>
            <p class="text-sm text-gray-700 mb-2">Untuk reservasi yang berasal dari OTA (Booking.com, Tiket.com, Traveloka) yang perlu dicatat manual ke sistem.</p>
            <ol class="list-decimal ml-5 space-y-1.5 text-sm text-gray-700">
                <li>Buka menu <strong>Booking → Booking OTA</strong></li>
                <li>Pilih <strong>Platform OTA</strong> (Booking.com / Tiket.com / Traveloka / Lainnya)</li>
                <li>Masukkan <strong>No. Reservasi OTA</strong> — nomor reservasi dari platform OTA</li>
                <li>Isi data tamu dan detail reservasi seperti booking biasa</li>
                <li>Sistem otomatis menandai reservasi sebagai <strong>OTA</strong> untuk memudahkan rekonsiliasi</li>
                <li>Klik <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs">Simpan</span></li>
            </ol>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 5. CHECK-IN --}}
    {{-- ================================================================ --}}
    <div id="checkin" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-sign-in-alt text-green-500"></i> 5. Check-in
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Menampilkan daftar reservasi <strong>Pending</strong> yang siap check-in.</p>
            <p class="text-sm font-semibold mb-2">Langkah:</p>
            <ol class="list-decimal ml-5 space-y-1.5 text-sm text-gray-700">
                <li>Cari reservasi — filter berdasarkan nama, no. reservasi, kamar, atau rentang tanggal</li>
                <li>Klik tombol <span class="bg-green-600 text-white px-2 py-0.5 rounded text-xs">Check-in</span></li>
                <li>Sistem otomatis mengubah: status reservasi → <code>checked_in</code>, status kamar → <strong>Occupied</strong></li>
            </ol>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 6. CHECK-OUT --}}
    {{-- ================================================================ --}}
    <div id="checkout" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-sign-out-alt text-amber-500"></i> 6. Check-out
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Daftar kamar <strong>Occupied</strong> yang siap check-out. Kamar <strong>Due Out</strong> (jadwal check-out hari ini) disorot dengan latar merah.</p>
            <p class="text-sm font-semibold mb-2">Langkah:</p>
            <ol class="list-decimal ml-5 space-y-1.5 text-sm text-gray-700">
                <li>Verifikasi tagihan tamu — klik <strong>Detail</strong> untuk lihat rincian</li>
                <li>Klik tombol <span class="bg-yellow-500 text-white px-2 py-0.5 rounded text-xs">Checkout</span></li>
                <li>Konfirmasi — sistem mengubah status kamar menjadi <strong>Available</strong></li>
            </ol>
            <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm">
                <p><i class="fas fa-info-circle text-blue-500 mr-1"></i> Sebelum check-out, pastikan semua <strong>Service Charge</strong> dan <strong>Deposit</strong> sudah terproses.</p>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 7. DETAIL RESERVASI --}}
    {{-- ================================================================ --}}
    <div id="reservation" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-file-alt text-blue-500"></i> 7. Detail Reservasi
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Informasi lengkap satu reservasi — data tamu, kamar, keuangan, dan aksi.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm mb-3">
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-semibold text-gray-700 mb-1"><i class="fas fa-user text-blue-400 mr-1"></i> Info Tamu</p>
                    <p class="text-gray-600 text-xs">Nama, No. Identitas, Telepon, Email</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-semibold text-gray-700 mb-1"><i class="fas fa-bed text-green-400 mr-1"></i> Info Kamar</p>
                    <p class="text-gray-600 text-xs">No. Kamar, Tipe, Check-in/out, Status Sarapan (bisa toggle langsung)</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-semibold text-gray-700 mb-1"><i class="fas fa-money-bill text-yellow-400 mr-1"></i> Keuangan</p>
                    <p class="text-gray-600 text-xs">Total tagihan (bisa update), room rate (bisa update), riwayat pembayaran</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-semibold text-gray-700 mb-1"><i class="fas fa-print text-purple-400 mr-1"></i> Cetak Dokumen</p>
                    <p class="text-gray-600 text-xs">Kwitansi & Invoice — format siap print</p>
                </div>
            </div>
            <p class="text-sm text-gray-700"><i class="fas fa-globe text-purple-400 mr-1"></i> Jika reservasi dari <strong>OTA</strong> (Booking.com, Tiket.com, Traveloka), akan tampil Info OTA dengan nomor reservasi OTA dan status pembayaran.</p>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 8. ROOM CHANGE --}}
    {{-- ================================================================ --}}
    <div id="room-change" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-exchange-alt text-teal-500"></i> 8. Pindah Kamar (Room Change)
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <ol class="list-decimal ml-5 space-y-1.5 text-sm text-gray-700">
                <li>Buka menu <strong>Front Desk → Pindah Kamar</strong></li>
                <li>Pilih reservasi Checked In yang akan dipindah</li>
                <li>Pilih kamar baru yang tersedia (disarankan tipe yang sama)</li>
                <li>Masukkan alasan pemindahan (opsional)</li>
                <li>Klik <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs">Pindahkan</span></li>
            </ol>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 9. ISSUE CARD --}}
    {{-- ================================================================ --}}
    <div id="issue-card" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-id-card text-cyan-500"></i> 9. Issue Card MHS
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Menerbitkan kartu akses kamar melalui perangkat <strong>MHS (Magic Hotel System)</strong>.</p>
            <ol class="list-decimal ml-5 space-y-1.5 text-sm text-gray-700">
                <li>Cari reservasi (cari berdasarkan no. reservasi, nama tamu, atau no. kamar)</li>
                <li>Pilih reservasi — data tamu dan kamar terisi otomatis</li>
                <li>Atur <strong>jumlah kartu</strong> (default 1)</li>
                <li>Klik <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs">Issue Card</span></li>
            </ol>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-semibold text-xs"><i class="fas fa-redo text-blue-400 mr-1"></i> Re-Issue</p>
                    <p class="text-xs text-gray-600">Untuk kartu hilang/rusak</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-semibold text-xs"><i class="fas fa-plug text-green-400 mr-1"></i> Test Connection</p>
                    <p class="text-xs text-gray-600">Uji koneksi ke perangkat MHS</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-semibold text-xs"><i class="fas fa-credit-card text-purple-400 mr-1"></i> Read Card</p>
                    <p class="text-xs text-gray-600">Baca data kartu yang sudah diterbitkan</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 10. DEPOSIT --}}
    {{-- ================================================================ --}}
    <div id="deposit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-credit-card text-rose-500"></i> 10. Deposit Kartu
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Mengelola deposit/uang jaminan kartu tamu (nominal default Rp 100.000 per kartu).</p>
            <p class="text-sm font-semibold mb-2">Alur:</p>
            <ol class="list-decimal ml-5 space-y-1.5 text-sm text-gray-700">
                <li>Saat check-in — catat deposit kartu tamu via <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs">Tambah Deposit</span></li>
                <li>Filter daftar deposit berdasarkan tanggal atau cari no. receipt / nama tamu</li>
                <li>Saat check-out — lakukan <strong>Return Deposit</strong> untuk mengembalikan uang jaminan</li>
            </ol>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 11. SERVICE CHARGE --}}
    {{-- ================================================================ --}}
    <div id="service-charge" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-receipt text-sky-500"></i> 11. Service Charge
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Mencatat biaya layanan tambahan ke kamar (minibar, laundry, telepon, snack, dll).</p>
            <ol class="list-decimal ml-5 space-y-1.5 text-sm text-gray-700">
                <li>Pilih reservasi tujuan</li>
                <li>Masukkan deskripsi biaya dan nominal</li>
                <li>Simpan — total biaya otomatis masuk ke tagihan reservasi</li>
            </ol>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 12. RESTO --}}
    {{-- ================================================================ --}}
    <div id="resto" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-utensils text-orange-500"></i> 12. Pendapatan Resto
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Mencatat transaksi restoran hotel. Bisa dikaitkan ke tagihan kamar tamu.</p>
            <ul class="space-y-1.5 text-sm text-gray-700 ml-2">
                <li><i class="fas fa-circle text-orange-400 text-[6px] mr-2 align-middle"></i><strong>Tambah Transaksi</strong> — catat penjualan makanan/minuman dari restoran</li>
                <li><i class="fas fa-circle text-orange-400 text-[6px] mr-2 align-middle"></i>Filter berdasarkan periode tanggal</li>
                <li><i class="fas fa-circle text-orange-400 text-[6px] mr-2 align-middle"></i>Transaksi bisa ditambahkan ke tagihan kamar tamu tertentu</li>
            </ul>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 13. HOUSEKEEPING --}}
    {{-- ================================================================ --}}
    <div id="housekeeping" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-broom text-yellow-600"></i> 13. Housekeeping
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Manajemen tugas pembersihan dan perawatan kamar.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-3">
                <div>
                    <p class="font-semibold mb-2">📊 Statistik:</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">Menunggu</span>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">Sedang Dikerjakan</span>
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">Selesai Hari Ini</span>
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">Urgent</span>
                    </div>
                </div>
                <div>
                    <p class="font-semibold mb-2">⚙️ Fitur:</p>
                    <ul class="space-y-1 text-xs text-gray-600 ml-2 list-disc">
                        <li>Buat Tugas — untuk satu kamar</li>
                        <li>Bulk Create — buat tugas untuk banyak kamar sekaligus</li>
                        <li>Assign petugas housekeeping</li>
                        <li>Update status: Pending → In Progress → Completed</li>
                        <li>Print laporan housekeeping</li>
                        <li>Lihat semua tugas per kamar (Room Tasks)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 14. NIGHT AUDIT --}}
    {{-- ================================================================ --}}
    <div id="night-audit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-moon text-indigo-500"></i> 14. Night Audit
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                    <h4 class="font-bold text-indigo-800 mb-2 flex items-center gap-1.5">
                        <span class="bg-indigo-200 text-indigo-800 text-[10px] px-1.5 py-0.5 rounded font-bold">BARU</span> Night Audit v2
                    </h4>
                    <ol class="list-decimal ml-4 space-y-1 text-xs text-indigo-900">
                        <li><strong>Preview</strong> — lihat pratinjau data audit sebelum disimpan</li>
                        <li><strong>Save Draft</strong> — simpan sebagai draft (masih bisa diedit)</li>
                        <li><strong>Lock</strong> — kunci data final (tidak bisa diubah lagi)</li>
                        <li><strong>History</strong> — lihat laporan night audit sebelumnya</li>
                        <li><strong>Export</strong> — download file laporan</li>
                    </ol>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-bold text-gray-800 mb-2">📋 Night Audit Report (v1)</h4>
                    <p class="text-xs text-gray-600">Laporan ringkasan: total kamar, occupied, available, check-in/out hari ini, pendapatan (tunai, transfer, dll), expected revenue. Bisa filter tanggal, export CSV, dan print.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 15. REPORTS --}}
    {{-- ================================================================ --}}
    <div id="reports" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-chart-bar text-purple-500"></i> 15. Laporan (Reports)
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold mb-1"><i class="fas fa-list text-blue-400 mr-1"></i> Guest List Report</p>
                    <p class="text-xs text-gray-600">Daftar tamu yang sedang check-in. Bisa filter tanggal dan export CSV.</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold mb-1"><i class="fas fa-percentage text-green-400 mr-1"></i> Occupancy</p>
                    <p class="text-xs text-gray-600">Laporan okupansi kamar per periode — jumlah kamar terisi per malam dan tingkat hunian.</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold mb-1"><i class="fas fa-money-bill-wave text-yellow-400 mr-1"></i> Revenue</p>
                    <p class="text-xs text-gray-600">Pendapatan hotel per periode, breakdown sumber (kamar, resto, service charge).</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold mb-1"><i class="fas fa-calendar-check text-red-400 mr-1"></i> Reservation Report</p>
                    <p class="text-xs text-gray-600">Semua reservasi dalam periode tertentu — filter tanggal dan status.</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold mb-1"><i class="fas fa-users text-indigo-400 mr-1"></i> Group Report</p>
                    <p class="text-xs text-gray-600">Laporan khusus untuk reservasi grup/rombongan.</p>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-3"><i class="fas fa-download mr-1"></i> Semua laporan bisa di-<strong>Export CSV</strong> dan <strong>Print</strong>.</p>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 16. GUESTS --}}
    {{-- ================================================================ --}}
    <div id="guests" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i> 16. Manajemen Tamu
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Database semua tamu yang pernah menginap.</p>
            <ul class="space-y-1.5 text-sm text-gray-700 ml-2">
                <li><i class="fas fa-circle text-blue-400 text-[6px] mr-2 align-middle"></i>Cari tamu berdasarkan <strong>nama, telepon, email, atau no. identitas</strong></li>
                <li><i class="fas fa-circle text-blue-400 text-[6px] mr-2 align-middle"></i>Tambah tamu baru</li>
                <li><i class="fas fa-circle text-blue-400 text-[6px] mr-2 align-middle"></i>Edit data tamu yang sudah ada</li>
                <li><i class="fas fa-circle text-blue-400 text-[6px] mr-2 align-middle"></i><strong>Export CSV</strong> — download data semua tamu</li>
            </ul>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 17. ROOMS --}}
    {{-- ================================================================ --}}
    <div id="rooms" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-door-open text-emerald-500"></i> 17. Kamar & Tipe Kamar
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-bold text-gray-800 mb-1">📋 Room List</h4>
                    <p class="text-xs text-gray-600">Daftar semua kamar dengan informasi lengkap. Bisa di-print.</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-bold text-gray-800 mb-1">🛏️ Rooms</h4>
                    <p class="text-xs text-gray-600">Tambah/edit/hapus kamar. Atur: no. kamar, tipe, harga, fasilitas, status.</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-bold text-gray-800 mb-1">🏷️ Room Types</h4>
                    <p class="text-xs text-gray-600">Atur tipe kamar: nama tipe, harga weekday & weekend, kapasitas, fasilitas.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 18. AI CHAT --}}
    {{-- ================================================================ --}}
    <div id="ai-chat" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-robot text-purple-500"></i> 18. AI Chat Assistant
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Asisten AI untuk membantu operasional front office. Akses via tombol <span class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-2 py-0.5 rounded text-xs">🤖 AI</span> di halaman Reservasi.</p>
            <p class="text-sm font-semibold mb-2">Contoh perintah yang bisa digunakan:</p>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm space-y-2">
                <p class="text-gray-700"><i class="fas fa-comment text-blue-400 mr-1"></i> <em>"Buat reservasi untuk Budi, check-in besok, 2 malam, kamar deluxe"</em></p>
                <p class="text-gray-700"><i class="fas fa-comment text-blue-400 mr-1"></i> <em>"Cari reservasi a.n. Siti"</em></p>
                <p class="text-gray-700"><i class="fas fa-comment text-blue-400 mr-1"></i> <em>"Kamar apa saja yang available untuk 3-5 Juni?"</em></p>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 19. ADMIN --}}
    {{-- ================================================================ --}}
    <div id="admin" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-cog text-gray-600"></i> 19. Administrasi (Owner Only)
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div class="bg-purple-50 rounded-lg p-3 border border-purple-200">
                    <p class="font-bold text-purple-800 mb-1"><i class="fas fa-shield-alt mr-1"></i> Permission Management</p>
                    <p class="text-xs text-purple-700">Atur hak akses per role (Owner/Admin/Front Office) dan per user. Dashboard, daftar permission, user permissions.</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                    <p class="font-bold text-blue-800 mb-1"><i class="fas fa-users-cog mr-1"></i> Manage Users</p>
                    <p class="text-xs text-blue-700">Tambah/edit/hapus user. Atur role dan permission tambahan per user.</p>
                </div>
                <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                    <p class="font-bold text-green-800 mb-1"><i class="fas fa-database mr-1"></i> Backup Database</p>
                    <p class="text-xs text-green-700">Buat backup baru, download, restore, dan hapus backup database lama.</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-3 border border-orange-200">
                    <p class="font-bold text-orange-800 mb-1"><i class="fas fa-key mr-1"></i> API Keys</p>
                    <p class="text-xs text-orange-700">Generate API Key baru dan revoke key yang tidak digunakan.</p>
                </div>
                <div class="bg-teal-50 rounded-lg p-3 border border-teal-200">
                    <p class="font-bold text-teal-800 mb-1"><i class="fas fa-credit-card mr-1"></i> Metode Pembayaran</p>
                    <p class="text-xs text-teal-700">Atur metode pembayaran: Tunai, Transfer Bank, QRIS, Debit/Kredit Card.</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                    <p class="font-bold text-slate-800 mb-1"><i class="fas fa-sliders-h mr-1"></i> Setting Hotel</p>
                    <p class="text-xs text-slate-700">Konfigurasi nama hotel, alamat, telepon, logo, dan informasi hotel lainnya.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 20. API --}}
    {{-- ================================================================ --}}
    <div id="api" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-plug text-blue-500"></i> 20. API Eksternal
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">REST API untuk integrasi dengan OTA (Booking.com, Tiket.com, Traveloka), channel manager, atau aplikasi pihak ketiga.</p>
            <div class="bg-gray-50 rounded-lg p-4 mb-3 text-sm border border-gray-200">
                <p class="font-semibold mb-1">Autentikasi:</p>
                <p class="text-xs text-gray-600">Menggunakan API Key via header <code class="bg-gray-200 px-1 rounded text-xs">X-API-Key</code> atau query parameter <code class="bg-gray-200 px-1 rounded text-xs">?api_key=</code></p>
            </div>
            <div class="overflow-x-auto text-xs">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 p-2 text-left font-semibold">Method</th>
                            <th class="border border-gray-300 p-2 text-left font-semibold">Endpoint</th>
                            <th class="border border-gray-300 p-2 text-left font-semibold">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-green-600 font-mono">GET</td><td class="border border-gray-300 p-2 font-mono">/api/reservations</td><td class="border border-gray-300 p-2">Daftar reservasi (filter & pagination)</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-green-600 font-mono">GET</td><td class="border border-gray-300 p-2 font-mono">/api/reservations/{id}</td><td class="border border-gray-300 p-2">Detail reservasi</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-blue-600 font-mono">POST</td><td class="border border-gray-300 p-2 font-mono">/api/reservations</td><td class="border border-gray-300 p-2">Buat reservasi baru</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-orange-600 font-mono">PUT</td><td class="border border-gray-300 p-2 font-mono">/api/reservations/{id}</td><td class="border border-gray-300 p-2">Update reservasi</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-blue-600 font-mono">POST</td><td class="border border-gray-300 p-2 font-mono">/api/reservations/{id}/cancel</td><td class="border border-gray-300 p-2">Batalkan reservasi</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-blue-600 font-mono">POST</td><td class="border border-gray-300 p-2 font-mono">/api/reservations/{id}/checkin</td><td class="border border-gray-300 p-2">Check-in reservasi</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-blue-600 font-mono">POST</td><td class="border border-gray-300 p-2 font-mono">/api/reservations/{id}/checkout</td><td class="border border-gray-300 p-2">Check-out reservasi</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-blue-600 font-mono">POST</td><td class="border border-gray-300 p-2 font-mono">/api/reservations/{id}/change-room</td><td class="border border-gray-300 p-2">Pindah kamar</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-blue-600 font-mono">POST</td><td class="border border-gray-300 p-2 font-mono">/api/reservations/{id}/payments</td><td class="border border-gray-300 p-2">Tambah pembayaran</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-green-600 font-mono">GET</td><td class="border border-gray-300 p-2 font-mono">/api/rooms</td><td class="border border-gray-300 p-2">Daftar kamar dengan status</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-green-600 font-mono">GET</td><td class="border border-gray-300 p-2 font-mono">/api/rooms/available</td><td class="border border-gray-300 p-2">Cek kamar tersedia</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-green-600 font-mono">GET</td><td class="border border-gray-300 p-2 font-mono">/api/guests</td><td class="border border-gray-300 p-2">Daftar tamu</td></tr>
                        <tr class="hover:bg-gray-50"><td class="border border-gray-300 p-2 text-green-600 font-mono">GET</td><td class="border border-gray-300 p-2 font-mono">/api/stats</td><td class="border border-gray-300 p-2">Statistik dashboard</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 21. OTA EMAIL LOG --}}
    {{-- ================================================================ --}}
    <div id="ota-email" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-envelope-open-text text-purple-500"></i> 21. Log Email OTA
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Memantau dan mengelola email reservasi dari platform OTA (Booking.com, Tiket.com, Traveloka) yang masuk ke sistem. Fitur ini membaca email otomatis dan mengekstrak data reservasi.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-4">
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <h4 class="font-bold text-purple-800 mb-2">📊 Statistik</h4>
                    <p class="text-xs text-purple-700">Ringkasan jumlah email: <strong>Total, Diproses, Sukses, Gagal, Pending</strong> — tampil di halaman utama Log Email OTA.</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <h4 class="font-bold text-blue-800 mb-2">📋 Daftar Email</h4>
                    <p class="text-xs text-blue-700">Tabel semua email OTA dengan status, platform, subjek, tanggal, dan aksi.</p>
                </div>
            </div>
            <p class="text-sm font-semibold mb-2">Fitur:</p>
            <ul class="space-y-1.5 text-sm text-gray-700 ml-2">
                <li><i class="fas fa-circle text-purple-400 text-[6px] mr-2 align-middle"></i><strong>Refresh Stats</strong> — perbarui statistik email terkini</li>
                <li><i class="fas fa-circle text-purple-400 text-[6px] mr-2 align-middle"></i><strong>Detail Email</strong> — klik email untuk melihat isi lengkap email dan data parsing</li>
                <li><i class="fas fa-circle text-purple-400 text-[6px] mr-2 align-middle"></i><strong>Retry</strong> — coba ulang proses parsing untuk email yang gagal</li>
                <li><i class="fas fa-circle text-purple-400 text-[6px] mr-2 align-middle"></i>Filter berdasarkan <strong>Platform OTA</strong> dan <strong>Status</strong></li>
                <li><i class="fas fa-circle text-purple-400 text-[6px] mr-2 align-middle"></i>Pencarian berdasarkan <strong>subjek email</strong> atau <strong>nomor reservasi</strong></li>
            </ul>
            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm">
                <p><i class="fas fa-lightbulb text-yellow-600 mr-1"></i> <strong>Tips:</strong> Pastikan koneksi IMAP email sudah dikonfigurasi di <strong>Setting Hotel</strong> agar fitur ini dapat membaca email OTA secara otomatis.</p>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center text-xs text-gray-400 py-6 border-t border-gray-200">
        <p>Dynamic PMS V.2 — Property Management System</p>
        <p class="mt-1">Dokumentasi ini dicetak pada {{ now()->format('d/m/Y H:i') }}</p>
    </div>

</div>
@endsection

@push('styles')
<style>
    /* Collapse/Expand */
    .section-card .section-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s ease, opacity 0.25s ease, padding-top 0.25s ease, padding-bottom 0.25s ease;
        opacity: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
    .section-card.open .section-body {
        max-height: 2000px;
        opacity: 1;
        padding-top: 0.5rem;
        padding-bottom: 0.25rem;
    }
    .section-card.open .section-arrow {
        transform: rotate(180deg);
    }
    .section-card .section-arrow {
        transition: transform 0.3s ease;
    }

    /* Print styles */
    @media print {
        .app-sidebar, .sidebar-spacer, .app-header, .no-print {
            display: none !important;
        }
        .main-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
        .section-body {
            max-height: none !important;
            opacity: 1 !important;
            overflow: visible !important;
            padding-top: 0.5rem !important;
            padding-bottom: 0.25rem !important;
        }
        .section-card .section-arrow {
            display: none !important;
        }
        .section-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        body {
            background: white !important;
            font-size: 12px;
        }
        #app-layout {
            display: block !important;
        }
        .max-w-5xl {
            max-width: 100% !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleSection(headerEl) {
        const card = headerEl.closest('.section-card');
        card.classList.toggle('open');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Buka section pertama sebagai default
        const firstCard = document.querySelector('.section-card');
        if (firstCard) firstCard.classList.add('open');

        // Buka section berdasarkan hash di URL
        if (window.location.hash) {
            const target = document.querySelector(window.location.hash);
            if (target) target.classList.add('open');
        }
    });
</script>
@endpush
