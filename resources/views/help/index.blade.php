@extends('layouts.app')

@section('title', 'Panduan Penggunaan')
@section('header', 'Panduan Penggunaan Dynamic PMS V.2')

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Tombol Aksi --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-2">
            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1.5 rounded-full">
                <i class="fas fa-book-open mr-1"></i> Panduan Interaktif
            </span>
            <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1.5 rounded-full">
                <i class="fas fa-graduation-cap mr-1"></i> Belajar Step-by-Step
            </span>
        </div>
        <button onclick="window.print()"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2 text-sm shadow-sm">
            <i class="fas fa-print"></i> Cetak Panduan
        </button>
    </div>

    {{-- Pencarian --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 no-print">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="helpSearch" placeholder="Cari panduan, fitur, atau istilah..." 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm outline-none transition">
            <button onclick="document.getElementById('helpSearch').value='';filterSections();" 
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1 hidden" id="clearSearch">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mt-2 flex flex-wrap gap-1.5 text-xs" id="quickFilters">
            <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full cursor-pointer hover:bg-blue-100 transition font-medium quick-filter" data-filter="frontdesk">🏨 Front Desk</span>
            <span class="bg-green-50 text-green-700 px-2.5 py-1 rounded-full cursor-pointer hover:bg-green-100 transition font-medium quick-filter" data-filter="housekeeping">🧹 Housekeeping</span>
            <span class="bg-purple-50 text-purple-700 px-2.5 py-1 rounded-full cursor-pointer hover:bg-purple-100 transition font-medium quick-filter" data-filter="report">📊 Laporan</span>
            <span class="bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full cursor-pointer hover:bg-amber-100 transition font-medium quick-filter" data-filter="admin">⚙️ Admin</span>
            <span class="bg-rose-50 text-rose-700 px-2.5 py-1 rounded-full cursor-pointer hover:bg-rose-100 transition font-medium quick-filter" data-filter="keuangan">💰 Keuangan</span>
            <span class="bg-teal-50 text-teal-700 px-2.5 py-1 rounded-full cursor-pointer hover:bg-teal-100 transition font-medium quick-filter" data-filter="all">📋 Semua</span>
        </div>
    </div>

    {{-- Quick Reference Card --}}
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-5 mb-6 no-print">
        <div class="flex items-center gap-2 mb-4">
            <i class="fas fa-bolt text-blue-500"></i>
            <h2 class="text-base font-bold text-blue-900">Quick Reference — Panduan Cepat</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white rounded-lg p-3 border border-blue-100 shadow-sm">
                <p class="font-bold text-blue-800 mb-1.5"><i class="fas fa-calendar-plus mr-1"></i> Booking Baru</p>
                <ol class="text-gray-600 space-y-0.5 ml-3 list-decimal">
                    <li>Klik kamar <span class="bg-emerald-100 text-emerald-700 px-1 rounded text-[10px]">Available</span></li>
                    <li>Isi data tamu & tanggal</li>
                    <li>Atur harga & DP</li>
                    <li>Klik Simpan</li>
                </ol>
            </div>
            <div class="bg-white rounded-lg p-3 border border-blue-100 shadow-sm">
                <p class="font-bold text-blue-800 mb-1.5"><i class="fas fa-sign-in-alt mr-1"></i> Check-In</p>
                <ol class="text-gray-600 space-y-0.5 ml-3 list-decimal">
                    <li>Buka menu Check-In</li>
                    <li>Cari reservasi</li>
                    <li>Klik tombol Check-in</li>
                    <li>Kamar jadi Occupied</li>
                </ol>
            </div>
            <div class="bg-white rounded-lg p-3 border border-blue-100 shadow-sm">
                <p class="font-bold text-blue-800 mb-1.5"><i class="fas fa-sign-out-alt mr-1"></i> Check-Out</p>
                <ol class="text-gray-600 space-y-0.5 ml-3 list-decimal">
                    <li>Verifikasi tagihan</li>
                    <li>Kembalikan deposit</li>
                    <li>Klik Checkout</li>
                    <li>Kamar jadi Available</li>
                </ol>
            </div>
            <div class="bg-white rounded-lg p-3 border border-blue-100 shadow-sm">
                <p class="font-bold text-blue-800 mb-1.5"><i class="fas fa-moon mr-1"></i> Night Audit</p>
                <ol class="text-gray-600 space-y-0.5 ml-3 list-decimal">
                    <li>Preview data</li>
                    <li>Save Draft</li>
                    <li>Lock final</li>
                    <li>Export laporan</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Daftar Isi --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 no-print">
        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-list text-blue-500"></i> Daftar Isi
            <span class="text-xs font-normal text-gray-400 ml-1">(klik untuk langsung ke bagian)</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-1 text-sm" id="tocContainer">
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
            <a href="#promo-prices" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-tag mr-1.5 w-4 text-center"></i> Promo Harga</a>
            <a href="#ai-chat" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-robot mr-1.5 w-4 text-center"></i> AI Chat Assistant</a>
            <a href="#admin" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-cog mr-1.5 w-4 text-center"></i> Administrasi (Owner)</a>
            <a href="#api" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-plug mr-1.5 w-4 text-center"></i> API Eksternal</a>
            <a href="#ota-email" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-envelope-open-text mr-1.5 w-4 text-center"></i> Log Email OTA</a>
            <a href="#faq" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-question-circle mr-1.5 w-4 text-center"></i> FAQ — Tanya Jawab</a>
            <a href="#glossary" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-book mr-1.5 w-4 text-center"></i> Glosarium Istilah</a>
            <a href="#tips" class="text-blue-600 hover:text-blue-800 hover:underline py-1.5 px-2 rounded hover:bg-blue-50 transition"><i class="fas fa-lightbulb mr-1.5 w-4 text-center"></i> Tips & Trik</a>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 1. LOGIN --}}
    {{-- ================================================================ --}}
    <div id="login" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
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
    <div id="dashboard" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
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
    <div id="room-rack" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
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
    <div id="booking" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
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
    <div id="checkin" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
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
    <div id="checkout" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
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
    <div id="reservation" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
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
    <div id="room-change" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
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
    <div id="issue-card" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
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
    <div id="deposit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="keuangan">
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
    <div id="service-charge" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="keuangan">
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
    <div id="resto" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="keuangan">
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
    <div id="housekeeping" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="housekeeping">
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
    <div id="night-audit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="keuangan">
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
    <div id="reports" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="report">
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
    <div id="guests" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
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
    <div id="rooms" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="admin">
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
    {{-- 18. PROMO HARGA --}}
    {{-- ================================================================ --}}
    <div id="promo-prices" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="admin">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-tag text-amber-500"></i> 18. Promo Harga
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <p class="text-sm text-gray-600 mb-3">Fitur untuk menetapkan <strong>harga khusus (promo)</strong> per tanggal untuk setiap Tipe Kamar. Harga promo otomatis digunakan saat menghitung total booking.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-4">
                <div class="bg-amber-50 rounded-lg p-4 border border-amber-200">
                    <h4 class="font-bold text-amber-800 mb-2">🎯 Cara Kerja</h4>
                    <ul class="space-y-1.5 text-xs text-amber-700">
                        <li><i class="fas fa-check-circle mr-1"></i> Promo berlaku per <strong>Tipe Kamar</strong>, bukan per kamar individu</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Setiap promo memiliki <strong>label</strong> (misal: "Promo Lebaran", "High Season")</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Bisa input <strong>range tanggal</strong> — otomatis create untuk setiap tanggal</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Jika sudah ada promo di tanggal yang sama, akan <strong>diupdate</strong></li>
                    </ul>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <h4 class="font-bold text-blue-800 mb-2">📊 Prioritas Harga</h4>
                    <div class="flex items-center gap-2 text-xs mb-2">
                        <span class="bg-amber-500 text-white px-2 py-0.5 rounded font-bold">1</span>
                        <span class="font-semibold">Harga Promo</span>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </div>
                    <div class="flex items-center gap-2 text-xs mb-2 ml-6">
                        <span class="bg-gray-300 text-gray-700 px-2 py-0.5 rounded font-bold">2</span>
                        <span class="font-semibold">Harga Weekend</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs mb-2 ml-6">
                        <span class="bg-gray-300 text-gray-700 px-2 py-0.5 rounded font-bold">3</span>
                        <span class="font-semibold">Harga Weekday</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs ml-6">
                        <span class="bg-gray-300 text-gray-700 px-2 py-0.5 rounded font-bold">4</span>
                        <span class="font-semibold">Default (price_per_night)</span>
                    </div>
                </div>
            </div>

            <div class="text-sm">
                <h4 class="font-bold text-gray-800 mb-2">📝 Cara Menggunakan</h4>
                <ol class="space-y-1.5 text-sm text-gray-700 ml-2 list-decimal list-inside">
                    <li>Buka menu <strong>Room Management → Promo Harga</strong> di sidebar</li>
                    <li>Klik tombol <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs">+ Tambah Promo</span></li>
                    <li>Pilih <strong>Tipe Kamar</strong> yang ingin diberi promo</li>
                    <li>Isi <strong>Dari Tanggal</strong> dan <strong>Sampai Tanggal</strong> (kosongkan jika 1 hari)</li>
                    <li>Masukkan <strong>Harga Promo</strong> per malam (Rp)</li>
                    <li>Isi <strong>Label Promo</strong> untuk identifikasi (contoh: "Promo Lebaran")</li>
                    <li>Klik <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs">Simpan Promo</span></li>
                </ol>
                <p class="text-xs text-gray-500 mt-2"><i class="fas fa-info-circle mr-1"></i> Harga promo akan otomatis terpakai saat membuat booking untuk tanggal dan tipe kamar yang sesuai.</p>
            </div>

            <div class="mt-4 text-sm">
                <h4 class="font-bold text-gray-800 mb-2">🔌 API Endpoint</h4>
                <p class="text-xs text-gray-600 mb-2">Endpoint API untuk integrasi eksternal (OTA, channel manager, dll):</p>
                <div class="bg-gray-800 text-gray-200 rounded-lg p-3 text-xs font-mono space-y-1">
                    <p><span class="text-green-400">GET</span> /api/promo-prices <span class="text-gray-500">— Daftar semua harga promo (filter: ?room_type_id=, ?date=, ?date_from=&date_to=)</span></p>
                    <p><span class="text-green-400">GET</span> /api/promo-prices/room-types <span class="text-gray-500">— Tipe kamar + promo prices masing-masing</span></p>
                    <p><span class="text-green-400">GET</span> /api/promo-prices/check?room_id=X&date=YYYY-MM-DD <span class="text-gray-500">— Cek harga efektif (dengan promo)</span></p>
                    <p><span class="text-green-400">GET</span> /api/promo-prices/check?room_id=X&check_in=...&check_out=... <span class="text-gray-500">— Cek range dengan breakdown per malam</span></p>
                </div>
                <p class="text-xs text-gray-500 mt-1"><i class="fas fa-key mr-1"></i> Semua endpoint menggunakan autentikasi <code class="bg-gray-100 px-1 rounded">X-API-Key</code>.</p>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 19. AI CHAT --}}
    {{-- ================================================================ --}}
    <div id="ai-chat" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-robot text-purple-500"></i> 19. AI Chat Assistant
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
    {{-- 20. ADMIN --}}
    {{-- ================================================================ --}}
    <div id="admin" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="admin">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-cog text-gray-600"></i> 20. Administrasi (Owner Only)
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
    <div id="api" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="admin">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-plug text-cyan-500"></i> 21. API Eksternal
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
    {{-- 22. LOG EMAIL OTA --}}
    {{-- ================================================================ --}}
    <div id="ota-email" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="frontdesk">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-envelope-open-text text-teal-500"></i> 22. Log Email OTA
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

    {{-- ================================================================ --}}
    {{-- 23. FAQ --}}
    {{-- ================================================================ --}}
    <div id="faq" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="all">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-question-circle text-pink-500"></i> 23. FAQ — Pertanyaan yang Sering Diajukan
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <div class="space-y-3 text-sm">
                <details class="bg-gray-50 rounded-lg p-4 border border-gray-200 group">
                    <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                        <span>❓ Bagaimana cara membuat booking baru?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="mt-3 text-gray-600 space-y-1">
                        <p>Bisa melalui 2 cara:</p>
                        <p><strong>Via Dashboard:</strong> Klik kamar dengan status <span class="bg-emerald-100 text-emerald-700 px-1.5 rounded text-xs">Available</span> → isi data → simpan.</p>
                        <p><strong>Via Menu:</strong> <em>Front Desk → Booking</em> → pilih kamar & tanggal → isi data tamu → simpan.</p>
                    </div>
                </details>

                <details class="bg-gray-50 rounded-lg p-4 border border-gray-200 group">
                    <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                        <span>❓ Kenapa kamar tidak muncul saat booking?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="mt-3 text-gray-600 space-y-1">
                        <p>Kamar hanya muncul jika <strong>Available</strong> dan tidak <strong>overlap</strong> dengan reservasi lain. Periksa:</p>
                        <ul class="list-disc ml-5 space-y-1">
                            <li>Status kamar bukan <code>maintenance</code> atau <code>cleaning</code></li>
                            <li>Tanggal check-in/out tidak bertabrakan dengan reservasi lain</li>
                            <li>Ingat: <strong>Back-to-Booking</strong> diperbolehkan (check-out 12:00, check-in 14:00)</li>
                        </ul>
                    </div>
                </details>

                <details class="bg-gray-50 rounded-lg p-4 border border-gray-200 group">
                    <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                        <span>❓ Apa itu Back-to-Back Booking?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="mt-3 text-gray-600">
                        <p>Ketika tamu A check-out <strong>jam 12:00</strong> dan tamu B check-in di kamar yang sama <strong>jam 14:00</strong> di hari yang sama. Sistem mengizinkan ini karena ada jeda 2 jam untuk cleaning.</p>
                    </div>
                </details>

                <details class="bg-gray-50 rounded-lg p-4 border border-gray-200 group">
                    <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                        <span>❓ Bagaimana cara mengubah harga booking?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="mt-3 text-gray-600">
                        <p>Buka <strong>Detail Reservasi</strong> → pada bagian <strong>Room Rate</strong>, klik <span class="bg-blue-100 text-blue-700 px-1.5 rounded text-xs">Edit</span> → ubah nominal → simpan. Total tagihan akan menyesuaikan otomatis.</p>
                    </div>
                </details>

                <details class="bg-gray-50 rounded-lg p-4 border border-gray-200 group">
                    <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                        <span>❓ Bagaimana cara membatalkan reservasi?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="mt-3 text-gray-600">
                        <p>Buka <strong>Detail Reservasi</strong> → klik tombol <span class="bg-red-100 text-red-700 px-1.5 rounded text-xs">Batalkan Reservasi</span> → konfirmasi. Status reservasi berubah menjadi <code>cancelled</code> dan kamar kembali <strong>Available</strong>.</p>
                    </div>
                </details>

                <details class="bg-gray-50 rounded-lg p-4 border border-gray-200 group">
                    <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                        <span>❓ Apa perbedaan role Owner, Admin, dan Front Office?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="mt-3 text-gray-600 space-y-2">
                        <p><strong>Owner</strong> — akses penuh termasuk manajemen user, permission, backup DB, API keys.</p>
                        <p><strong>Admin</strong> — akses operasional penuh tanpa fitur administrasi owner.</p>
                        <p><strong>Front Office</strong> — akses terbatas pada front desk (reservasi, check-in/out, pembayaran).</p>
                    </div>
                </details>

                <details class="bg-gray-50 rounded-lg p-4 border border-gray-200 group">
                    <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                        <span>❓ Bagaimana cara menambahkan pembayaran (DP) ke reservasi?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="mt-3 text-gray-600">
                        <p>Buka <strong>Detail Reservasi</strong> → pada bagian <strong>Riwayat Pembayaran</strong>, klik <span class="bg-blue-100 text-blue-700 px-1.5 rounded text-xs">Tambah Pembayaran</span> → isi nominal dan metode pembayaran → simpan.</p>
                    </div>
                </details>

                <details class="bg-gray-50 rounded-lg p-4 border border-gray-200 group">
                    <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                        <span>❓ Kenapa tombol Check-in tidak muncul?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="mt-3 text-gray-600">
                        <p>Tombol Check-in hanya muncul untuk reservasi berstatus <code>pending</code>. Pastikan tanggal check-in sudah sesuai (hari ini atau sebelumnya).</p>
                    </div>
                </details>

                <details class="bg-gray-50 rounded-lg p-4 border border-gray-200 group">
                    <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                        <span>❓ Bagaimana jika lupa melakukan Night Audit?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="mt-3 text-gray-600">
                        <p>Night Audit bisa dilakukan kapan saja. Pilih tanggal yang sesuai saat melakukan audit. Sebaiknya dilakukan <strong>setiap tengah malam</strong> untuk menjaga akurasi laporan.</p>
                    </div>
                </details>

                <details class="bg-gray-50 rounded-lg p-4 border border-gray-200 group">
                    <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                        <span>❓ Bagaimana cara export laporan ke Excel/CSV?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="mt-3 text-gray-600">
                        <p>Di halaman laporan, klik tombol <span class="bg-green-100 text-green-700 px-1.5 rounded text-xs">Export CSV</span>. File akan terdownload otomatis. Bisa dibuka dengan Excel atau Google Sheets.</p>
                    </div>
                </details>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 24. GLOSARIUM --}}
    {{-- ================================================================ --}}
    <div id="glossary" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="all">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-book text-amber-500"></i> 24. Glosarium — Istilah Penting
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 text-sm">
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Available</p>
                    <p class="text-xs text-gray-600">Kamar kosong dan siap dipesan</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Occupied</p>
                    <p class="text-xs text-gray-600">Kamar sedang ditempati tamu (checked-in)</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Cleaning / Dirty</p>
                    <p class="text-xs text-gray-600">Kamar sedang dibersihkan setelah check-out</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Maintenance</p>
                    <p class="text-xs text-gray-600">Kamar dalam perbaikan — tidak bisa dipesan</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Due Out</p>
                    <p class="text-xs text-gray-600">Kamar yang jadwal check-out-nya hari ini</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Pending</p>
                    <p class="text-xs text-gray-600">Reservasi sudah dibuat tapi belum check-in</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Checked In</p>
                    <p class="text-xs text-gray-600">Tamu sudah check-in dan menempati kamar</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Checked Out</p>
                    <p class="text-xs text-gray-600">Tamu sudah check-out dan kamar kosong</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Cancelled</p>
                    <p class="text-xs text-gray-600">Reservasi dibatalkan</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">DP / Down Payment</p>
                    <p class="text-xs text-gray-600">Uang muka yang dibayarkan saat booking</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Deposit Kartu</p>
                    <p class="text-xs text-gray-600">Uang jaminan kartu akses kamar (dikembalikan saat check-out)</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Service Charge</p>
                    <p class="text-xs text-gray-600">Biaya tambahan (minibar, laundry, snack) yang dibebankan ke kamar</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">OTA</p>
                    <p class="text-xs text-gray-600">Online Travel Agent (Booking.com, Tiket.com, Traveloka)</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Night Audit</p>
                    <p class="text-xs text-gray-600">Proses penutupan hari operasional — rekonsiliasi data harian</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Room Rack</p>
                    <p class="text-xs text-gray-600">Tampilan rack tradisional untuk melihat status kamar per lantai/tipe</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">MHS</p>
                    <p class="text-xs text-gray-600">Magic Hotel System — perangkat penerbit kartu akses kamar</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Okupansi / Occupancy</p>
                    <p class="text-xs text-gray-600">Tingkat hunian kamar (persentase kamar terisi)</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="font-bold text-gray-800">Walk-in Guest</p>
                    <p class="text-xs text-gray-600">Tamu yang datang langsung tanpa reservasi sebelumnya</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 25. TIPS & TRIK --}}
    {{-- ================================================================ --}}
    <div id="tips" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 section-card" data-category="all">
        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection(this)">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-lightbulb text-yellow-500"></i> 25. Tips & Trik Penggunaan
            </h3>
            <i class="fas fa-chevron-down text-gray-400 transition-transform section-arrow"></i>
        </div>
        <div class="section-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <h4 class="font-bold text-yellow-800 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-rocket"></i> Efisiensi Operasional
                    </h4>
                    <ul class="space-y-2 text-xs text-yellow-700">
                        <li><i class="fas fa-check-circle mr-1"></i> Gunakan <strong>Bulk Update Status</strong> di Dashboard untuk mengubah status banyak kamar sekaligus</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Manfaatkan <strong>AI Chat Assistant</strong> untuk mencari reservasi tanpa perlu navigasi manual</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Aktifkan <strong>IMAP Email</strong> di Setting Hotel agar reservasi OTA otomatis terbaca</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Gunakan <strong>Forecast</strong> untuk merencanakan okupansi minggu depan</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Booking <strong>Group</strong> untuk rombongan besar — lebih cepat daripada booking satu per satu</li>
                    </ul>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <h4 class="font-bold text-blue-800 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-shield-alt"></i> Best Practice
                    </h4>
                    <ul class="space-y-2 text-xs text-blue-700">
                        <li><i class="fas fa-check-circle mr-1"></i> Lakukan <strong>Night Audit</strong> setiap malam untuk menjaga akurasi data keuangan</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Catat <strong>Service Charge</strong> segera setelah tamu menggunakan layanan tambahan</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Selalu <strong>Return Deposit</strong> saat check-out untuk menghindari komplain</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Backup database secara rutin melalui menu <strong>Administrasi → Backup Database</strong></li>
                        <li><i class="fas fa-check-circle mr-1"></i> Atur <strong>Promo Harga</strong> jauh-jauh hari untuk musim liburan</li>
                    </ul>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <h4 class="font-bold text-green-800 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-tools"></i> Troubleshooting Cepat
                    </h4>
                    <ul class="space-y-2 text-xs text-green-700">
                        <li><i class="fas fa-wrench mr-1"></i> <strong>Gagal Issue Card?</strong> — Cek koneksi MHS via tombol Test Connection</li>
                        <li><i class="fas fa-wrench mr-1"></i> <strong>Email OTA tidak terbaca?</strong> — Periksa konfigurasi IMAP di Setting Hotel</li>
                        <li><i class="fas fa-wrench mr-1"></i> <strong>Data tidak muncul?</strong> — Coba refresh halaman atau filter tanggal</li>
                        <li><i class="fas fa-wrench mr-1"></i> <strong>Harga salah?</strong> — Cek apakah ada promo harga yang aktif di tanggal tersebut</li>
                        <li><i class="fas fa-wrench mr-1"></i> <strong>Kamar tidak bisa di booking?</strong> — Periksa apakah statusnya Maintenance</li>
                    </ul>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <h4 class="font-bold text-purple-800 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-keyboard"></i> Pintasan & Navigasi Cepat
                    </h4>
                    <ul class="space-y-2 text-xs text-purple-700">
                        <li><i class="fas fa-check-circle mr-1"></i> Gunakan <strong>? (tanda tanya)</strong> di keyboard untuk membuka pintasan keyboard</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Klik logo hotel di sidebar untuk kembali ke Dashboard</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Manfaatkan <strong>pencarian</strong> di halaman ini untuk menemukan panduan cepat</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Gunakan filter kategori untuk fokus pada modul tertentu</li>
                        <li><i class="fas fa-check-circle mr-1"></i> Buka semua section dengan klik <strong>Expand All</strong> saat ingin mencari</li>
                    </ul>
                </div>
            </div>

            {{-- Learning Path --}}
            <div class="mt-5 bg-white border border-gray-200 rounded-lg p-5">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-graduation-cap text-blue-500"></i> 🎯 Learning Path — Panduan Belajar untuk Pengguna Baru
                </h4>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <span class="bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">1</span>
                        <div>
                            <p class="font-semibold text-blue-800">📖 Mulai dari sini — Login & Dashboard</p>
                            <p class="text-xs text-blue-600">Pahami cara login, role pengguna, dan membaca status kamar di dashboard.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-green-50 rounded-lg border border-green-100">
                        <span class="bg-green-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">2</span>
                        <div>
                            <p class="font-semibold text-green-800">📅 Buat Reservasi & Check-in</p>
                            <p class="text-xs text-green-600">Praktik membuat booking, mengisi data tamu, dan melakukan check-in.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-lg border border-amber-100">
                        <span class="bg-amber-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">3</span>
                        <div>
                            <p class="font-semibold text-amber-800">💰 Kelola Keuangan Tamu</p>
                            <p class="text-xs text-amber-600">Belajar mencatat deposit, service charge, dan pembayaran.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-red-50 rounded-lg border border-red-100">
                        <span class="bg-red-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">4</span>
                        <div>
                            <p class="font-semibold text-red-800">✅ Check-out & Night Audit</p>
                            <p class="text-xs text-red-600">Proses check-out, return deposit, dan tutup hari dengan Night Audit.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-lg border border-purple-100">
                        <span class="bg-purple-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">5</span>
                        <div>
                            <p class="font-semibold text-purple-800">📊 Laporan & Evaluasi</p>
                            <p class="text-xs text-purple-600">Pelajari cara membaca laporan okupansi, revenue, dan evaluasi kinerja.</p>
                        </div>
                    </div>
                </div>
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
        transition: max-height 0.4s ease, opacity 0.3s ease, padding-top 0.3s ease, padding-bottom 0.3s ease;
        opacity: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
    .section-card.open .section-body {
        max-height: 3000px;
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

    /* Section highlight saat di-search */
    .section-card.highlight {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59,130,246,0.15), 0 1px 3px rgba(0,0,0,0.08);
        transition: box-shadow 0.3s ease;
    }
    .section-card.highlight .section-body {
        max-height: 3000px;
        opacity: 1;
        padding-top: 0.5rem;
        padding-bottom: 0.25rem;
    }
    .section-card.highlight .section-arrow {
        transform: rotate(180deg);
    }

    /* Hidden state untuk filter */
    .section-card.filtered-out {
        display: none;
    }

    /* Search highlight dalam teks */
    .search-highlight {
        background-color: #fef08a;
        padding: 0 2px;
        border-radius: 2px;
        box-shadow: 0 0 0 1px rgba(250,204,21,0.4);
    }

    /* Quick filter active state */
    .quick-filter.active {
        ring: 2px solid currentColor;
    }

    /* Smooth scroll */
    html {
        scroll-behavior: smooth;
    }

    /* Sticky TOC on scroll */
    .toc-sticky {
        position: sticky;
        top: 1rem;
        z-index: 10;
    }

    /* FAQ details styling */
    details[open] summary {
        margin-bottom: 0;
    }
    details summary::-webkit-details-marker {
        display: none;
    }

    /* Animasi fade in untuk section */
    .section-card {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .section-card.filtered-out {
        opacity: 0;
        transform: translateY(-10px);
    }

    /* Stats counter */
    .stat-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .search-highlight {
            background: none !important;
            box-shadow: none !important;
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

    function filterSections() {
        const query = document.getElementById('helpSearch').value.toLowerCase().trim();
        const clearBtn = document.getElementById('clearSearch');
        const cards = document.querySelectorAll('.section-card');
        const tocLinks = document.querySelectorAll('#tocContainer a');

        // Toggle clear button
        if (clearBtn) {
            clearBtn.style.display = query.length > 0 ? 'block' : 'none';
        }

        // Hapus highlight lama
        document.querySelectorAll('.search-highlight').forEach(el => {
            const parent = el.parentNode;
            parent.replaceChild(document.createTextNode(el.textContent), el);
            parent.normalize();
        });

        if (!query) {
            // Reset semua
            cards.forEach((card, index) => {
                card.classList.remove('filtered-out', 'highlight');
                if (index === 0) card.classList.add('open');
            });
            tocLinks.forEach(link => link.style.display = '');
            document.querySelectorAll('.quick-filter').forEach(f => f.classList.remove('ring-2', 'ring-blue-400'));
            return;
        }

        let visibleCount = 0;
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            // Cari di header h3
            const header = card.querySelector('h3');
            const headerText = header ? header.textContent.toLowerCase() : '';

            if (text.includes(query)) {
                card.classList.remove('filtered-out');
                card.classList.add('highlight');
                visibleCount++;

                // Highlight kata yang cocok di body (tidak di header)
                const body = card.querySelector('.section-body');
                if (body && !headerText.includes(query)) {
                    highlightText(body, query);
                }
            } else {
                card.classList.add('filtered-out');
                card.classList.remove('highlight');
            }
        });

        // Filter TOC
        tocLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (!href) return;
            const targetId = href.substring(1);
            const target = document.getElementById(targetId);
            const match = target && !target.classList.contains('filtered-out');
            link.style.display = match ? '' : 'none';
        });

        // Update hasil pencarian
        updateSearchStats(visibleCount, cards.length);
    }

    function highlightText(container, query) {
        if (!container || query.length < 1) return;
        const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT, null, false);
        const nodesToReplace = [];
        const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');

        while (walker.nextNode()) {
            const node = walker.currentNode;
            if (node.parentElement && node.parentElement.closest('script, style, summary, code')) continue;
            if (regex.test(node.textContent)) {
                nodesToReplace.push(node);
            }
            regex.lastIndex = 0;
        }

        nodesToReplace.forEach(node => {
            const fragment = document.createDocumentFragment();
            const text = node.textContent;
            regex.lastIndex = 0;
            let lastIndex = 0;
            let match;

            while ((match = regex.exec(text)) !== null) {
                if (match.index > lastIndex) {
                    fragment.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
                }
                const mark = document.createElement('mark');
                mark.className = 'search-highlight';
                mark.textContent = match[0];
                fragment.appendChild(mark);
                lastIndex = regex.lastIndex;
            }
            if (lastIndex < text.length) {
                fragment.appendChild(document.createTextNode(text.slice(lastIndex)));
            }
            node.parentNode.replaceChild(fragment, node);
        });
    }

    function updateSearchStats(visible, total) {
        const searchBox = document.getElementById('helpSearch');
        // Cari atau buat stats indicator
        let statsEl = document.getElementById('searchStats');
        if (!statsEl) {
            statsEl = document.createElement('div');
            statsEl.id = 'searchStats';
            statsEl.className = 'text-xs text-gray-500 mt-1.5';
            searchBox.parentNode.appendChild(statsEl);
        }
        if (visible > 0) {
            statsEl.innerHTML = `<i class="fas fa-search mr-1"></i> Menampilkan <strong>${visible}</strong> dari <strong>${total}</strong> bagian`;
        } else if (visible === 0 && searchBox.value.trim()) {
            statsEl.innerHTML = `<i class="fas fa-exclamation-circle text-amber-500 mr-1"></i> Tidak ditemukan. Coba kata kunci lain.`;
        } else {
            statsEl.innerHTML = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Buka section pertama sebagai default
        const firstCard = document.querySelector('.section-card');
        if (firstCard) firstCard.classList.add('open');

        // Buka section berdasarkan hash di URL
        if (window.location.hash) {
            const target = document.querySelector(window.location.hash);
            if (target) {
                // Tutup semua dulu
                document.querySelectorAll('.section-card.open').forEach(c => c.classList.remove('open'));
                target.classList.add('open');
                setTimeout(() => {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }
        }

        // Search dengan debounce
        const searchInput = document.getElementById('helpSearch');
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterSections, 200);
        });

        // Quick filter buttons
        document.querySelectorAll('.quick-filter').forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.dataset.filter;
                document.querySelectorAll('.quick-filter').forEach(f => f.classList.remove('ring-2', 'ring-blue-400'));

                if (filter === 'all') {
                    searchInput.value = '';
                    filterSections();
                    return;
                }

                this.classList.add('ring-2', 'ring-blue-400');

                // Map filter ke kata kunci pencarian
                const keywords = {
                    'frontdesk': '',
                    'housekeeping': '',
                    'report': '',
                    'admin': '',
                    'keuangan': ''
                };

                // Filter via data-category attribute
                const cards = document.querySelectorAll('.section-card');
                let visibleCount = 0;
                cards.forEach(card => {
                    const cat = card.dataset.category;
                    if (cat === filter) {
                        card.classList.remove('filtered-out');
                        card.classList.add('highlight');
                        if (!card.classList.contains('open')) card.classList.add('open');
                        visibleCount++;
                    } else {
                        card.classList.add('filtered-out');
                        card.classList.remove('highlight');
                    }
                });

                // Filter TOC
                document.querySelectorAll('#tocContainer a').forEach(link => {
                    const href = link.getAttribute('href');
                    if (!href) return;
                    const targetId = href.substring(1);
                    const target = document.getElementById(targetId);
                    const match = target && !target.classList.contains('filtered-out');
                    link.style.display = match ? '' : 'none';
                });

                updateSearchStats(visibleCount, cards.length);
            });
        });

        // Clear search
        const clearBtn = document.getElementById('clearSearch');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                document.getElementById('helpSearch').value = '';
                filterSections();
                document.getElementById('helpSearch').focus();
            });
        }

        // Keyboard shortcut: Ctrl+F fokus ke search
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                if (document.activeElement !== searchInput) {
                    e.preventDefault();
                    searchInput.focus();
                }
            }
        });
    });
</script>
@endpush
