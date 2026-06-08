@extends('layouts.app')

@section('title', 'Laporan Bulanan Hotel')
@section('header', 'Laporan Bulanan Hotel — ' . Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon\Carbon::parse($endDate)->format('d/m/Y'))

@section('content')
{{-- Filter & Actions --}}
<div class="mb-6 no-print">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-3 py-2">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-search mr-1"></i> Tampilkan
            </button>
            <a href="{{ route('reports.compliance.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">
                <i class="fas fa-download mr-1"></i> Export CSV
            </a>
            <a href="{{ route('reports.compliance.print', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 inline-flex items-center">
                <i class="fas fa-print mr-1"></i> Print
            </a>
        </div>
    </form>
</div>

{{-- ─── Print Header ──────────────────────────────────────────────── --}}
<div class="hidden print:block mb-6 text-center">
    @php $hotel = \App\Models\HotelSetting::get(); @endphp
    @if($hotel->logo_path)
        <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" class="h-12 mx-auto mb-2">
    @endif
    <h2 class="text-lg font-bold uppercase tracking-wider">{{ $hotel->hotel_name ?? 'Dynamic PMS V.2' }}</h2>
    @if($hotel->address)<p class="text-xs text-gray-500">{{ $hotel->address }}</p>@endif
    @if($hotel->phone)<p class="text-xs text-gray-500">Telp: {{ $hotel->phone }}</p>@endif
    <h1 class="text-2xl font-bold uppercase tracking-wider mt-2">LAPORAN BULANAN HOTEL</h1>
    <p class="text-gray-600">Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    <p class="text-xs text-gray-400">Dicetak: {{ Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    <hr class="my-4 border-t-2 border-gray-800">
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- A. RINGKASAN PENDAPATAN --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded shadow p-5 mb-6">
    <h3 class="text-lg font-bold border-b pb-2 mb-4 flex items-center gap-2">
        <i class="fas fa-chart-pie text-blue-600"></i> A. Ringkasan Pendapatan
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="text-blue-600 text-xs font-semibold uppercase tracking-wider">Pendapatan Kamar</div>
            <div class="text-2xl font-bold text-blue-700">Rp {{ number_format($roomRevenue, 0, ',', '.') }}</div>
            @if($roomRevenuePrev > 0)
                <div class="text-xs {{ $roomRevenue >= $roomRevenuePrev ? 'text-green-600' : 'text-red-600' }}">
                    <i class="fas fa-{{ $roomRevenue >= $roomRevenuePrev ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ number_format(abs($roomRevenue - $roomRevenuePrev), 0, ',', '.') }} dr bln lalu
                </div>
            @endif
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="text-green-600 text-xs font-semibold uppercase tracking-wider">Pendapatan Resto</div>
            <div class="text-2xl font-bold text-green-700">Rp {{ number_format($restoRevenue, 0, ',', '.') }}</div>
            @if($restoRevenuePrev > 0)
                <div class="text-xs {{ $restoRevenue >= $restoRevenuePrev ? 'text-green-600' : 'text-red-600' }}">
                    <i class="fas fa-{{ $restoRevenue >= $restoRevenuePrev ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ number_format(abs($restoRevenue - $restoRevenuePrev), 0, ',', '.') }} dr bln lalu
                </div>
            @endif
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="text-purple-600 text-xs font-semibold uppercase tracking-wider">Other Revenue</div>
            <div class="text-2xl font-bold text-purple-700">Rp {{ number_format($scRevenue, 0, ',', '.') }}</div>
            @if($scRevenuePrev > 0)
                <div class="text-xs {{ $scRevenue >= $scRevenuePrev ? 'text-green-600' : 'text-red-600' }}">
                    <i class="fas fa-{{ $scRevenue >= $scRevenuePrev ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ number_format(abs($scRevenue - $scRevenuePrev), 0, ',', '.') }} dr bln lalu
                </div>
            @endif
        </div>
        <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
            <div class="text-amber-600 text-xs font-semibold uppercase tracking-wider">Pengeluaran</div>
            <div class="text-2xl font-bold text-amber-700">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
            @if($totalExpensesPrev > 0)
                <div class="text-xs {{ $totalExpenses <= $totalExpensesPrev ? 'text-green-600' : 'text-red-600' }}">
                    <i class="fas fa-{{ $totalExpenses <= $totalExpensesPrev ? 'arrow-down' : 'arrow-up' }}"></i>
                    {{ number_format(abs($totalExpenses - $totalExpensesPrev), 0, ',', '.') }} dr bln lalu
                </div>
            @endif
        </div>
        <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
            <div class="text-emerald-600 text-xs font-semibold uppercase tracking-wider">Pendapatan Bersih</div>
            <div class="text-2xl font-bold text-emerald-700">Rp {{ number_format($netRevenue, 0, ',', '.') }}</div>
            @if($netRevenuePrev > 0)
                <div class="text-xs {{ $netRevenue >= $netRevenuePrev ? 'text-green-600' : 'text-red-600' }}">
                    <i class="fas fa-{{ $netRevenue >= $netRevenuePrev ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ number_format(abs($netRevenue - $netRevenuePrev), 0, ',', '.') }} dr bln lalu
                </div>
            @endif
        </div>
    </div>

    {{-- Progress Bar Perbandingan --}}
    <div class="bg-gray-50 p-4 rounded-lg border">
        <div class="flex justify-between text-sm mb-1">
            <span class="font-semibold">Revenue Growth</span>
            <span class="{{ $revenueGrowth >= 0 ? 'text-green-600' : 'text-red-600' }} font-bold">
                {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="h-2.5 rounded-full {{ $revenueGrowth >= 0 ? 'bg-green-500' : 'bg-red-500' }}"
                 style="width: {{ min(abs($revenueGrowth), 100) }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-1">
            <span>Bulan lalu: Rp {{ number_format($grandRevenuePrev, 0, ',', '.') }}</span>
            <span>Bulan ini: Rp {{ number_format($grandRevenue, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- B. OKUPANSI & STATISTIK KAMAR --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded shadow p-5">
        <h3 class="text-lg font-bold border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-bed text-indigo-600"></i> B. Okupansi & Statistik Kamar
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Total Kamar</span>
                <span class="font-bold">{{ $totalRooms }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Rata-rata Okupansi</span>
                <span class="font-bold text-indigo-600">{{ $avgOccupancy }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full {{ $avgOccupancy >= 60 ? 'bg-green-500' : ($avgOccupancy >= 30 ? 'bg-yellow-500' : 'bg-red-500') }}"
                     style="width: {{ $avgOccupancy }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>0%</span>
                <span>50%</span>
                <span>100%</span>
            </div>
        </div>

        {{-- Occupancy Chart --}}
        <canvas id="occupancyChart" class="mt-4" height="120"></canvas>
    </div>

    {{-- B.1 Data Reservasi --}}
    <div class="bg-white rounded shadow p-5">
        <h3 class="text-lg font-bold border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-calendar-check text-teal-600"></i> B.1 Data Reservasi
        </h3>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 p-3 rounded text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $totalReservations }}</div>
                <div class="text-xs text-gray-500">Total Reservasi</div>
            </div>
            <div class="bg-gray-50 p-3 rounded text-center">
                <div class="text-2xl font-bold text-green-600">{{ $checkins }}</div>
                <div class="text-xs text-gray-500">Check-in</div>
            </div>
            <div class="bg-gray-50 p-3 rounded text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $checkouts }}</div>
                <div class="text-xs text-gray-500">Check-out</div>
            </div>
            <div class="bg-gray-50 p-3 rounded text-center">
                <div class="text-2xl font-bold text-red-600">{{ $cancelled }}</div>
                <div class="text-xs text-gray-500">Dibatalkan</div>
            </div>
        </div>

        {{-- OTA Summary --}}
        @if($otaBookings > 0)
        <div class="mt-4 bg-cyan-50 p-3 rounded border border-cyan-200">
            <div class="flex justify-between items-center mb-2">
                <div>
                    <span class="text-sm font-semibold text-cyan-700">Booking OTA</span>
                    <span class="text-cyan-600 font-bold ml-2">{{ $otaBookings }} reservasi</span>
                </div>
                <div class="text-right">
                    <span class="text-xs text-cyan-600">Total Pendapatan OTA</span>
                    <div class="font-bold text-cyan-700">Rp {{ number_format($otaRevenue, 0, ',', '.') }}</div>
                </div>
            </div>
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-cyan-300">
                        <th class="py-1 text-left font-semibold text-cyan-700">Sumber OTA</th>
                        <th class="py-1 text-right font-semibold text-cyan-700">Jumlah</th>
                        <th class="py-1 text-right font-semibold text-cyan-700">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($otaBySource as $ota)
                    <tr class="border-b border-cyan-100">
                        <td class="py-1 capitalize">{{ $ota->ota_source }}</td>
                        <td class="py-1 text-right font-medium">{{ $ota->total_bookings }}</td>
                        <td class="py-1 text-right font-medium">Rp {{ number_format($ota->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Guest Compliance --}}
        <div class="mt-4 bg-gray-50 p-3 rounded border">
            <div class="flex justify-between text-sm">
                <span class="font-semibold">Kepatuhan Data Tamu (ID Card)</span>
                <span class="font-bold {{ $guestCompliancePct >= 90 ? 'text-green-600' : ($guestCompliancePct >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $guestCompliancePct }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                <div class="h-2 rounded-full {{ $guestCompliancePct >= 90 ? 'bg-green-500' : ($guestCompliancePct >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}"
                     style="width: {{ $guestCompliancePct }}%"></div>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                {{ $guestsWithId }} dari {{ $totalGuests }} tamu memiliki ID card
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- C. PENDAPATAN CASH vs TRANSFER --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-amber-50 p-5 rounded-lg shadow border border-amber-200">
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-money-bill-wave text-amber-600"></i>
            <span class="font-bold text-amber-800">CASH</span>
        </div>
        <div class="text-3xl font-bold text-amber-700">Rp {{ number_format($grandCash, 0, ',', '.') }}</div>
        <div class="text-xs text-amber-600 mt-1">
            Kamar: Rp {{ number_format($cashRevenue, 0, ',', '.') }} |
            Resto: Rp {{ number_format($cashResto, 0, ',', '.') }}
        </div>
    </div>
    <div class="bg-blue-50 p-5 rounded-lg shadow border border-blue-200">
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-university text-blue-600"></i>
            <span class="font-bold text-blue-800">TRANSFER BCA</span>
        </div>
        <div class="text-3xl font-bold text-blue-700">Rp {{ number_format($grandTransfer, 0, ',', '.') }}</div>
        <div class="text-xs text-blue-600 mt-1">
            Kamar: Rp {{ number_format($transferRevenue, 0, ',', '.') }} |
            Resto: Rp {{ number_format($transferResto, 0, ',', '.') }}
        </div>
    </div>
    <div class="bg-gray-50 p-5 rounded-lg shadow border border-gray-200">
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-credit-card text-gray-600"></i>
            <span class="font-bold text-gray-800">LAINNYA</span>
        </div>
        <div class="text-3xl font-bold text-gray-700">Rp {{ number_format($grandOther, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">
            Kamar: Rp {{ number_format($otherRevenue, 0, ',', '.') }} |
            Resto: Rp {{ number_format($otherResto, 0, ',', '.') }}
        </div>
    </div>
</div>

{{-- C.1 Pendapatan per Metode Pembayaran (Detail) --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded shadow p-5">
        <h3 class="text-lg font-bold border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-credit-card text-emerald-600"></i> C.1 Pendapatan Kamar per Metode
        </h3>
        @if($revenueByMethod->count() > 0)
            <div class="space-y-2">
                @foreach($revenueByMethod as $method => $amount)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $method) }}</span>
                        <span class="font-bold">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-emerald-500"
                             style="width: {{ $roomRevenue > 0 ? ($amount / $roomRevenue) * 100 : 0 }}%"></div>
                    </div>
                @endforeach
                <hr class="my-2">
                <div class="flex justify-between font-bold text-lg">
                    <span>Total Kamar</span>
                    <span class="text-emerald-700">Rp {{ number_format($roomRevenue, 0, ',', '.') }}</span>
                </div>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">Belum ada data transaksi</p>
        @endif
    </div>

    <div class="bg-white rounded shadow p-5">
        <h3 class="text-lg font-bold border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-utensils text-orange-600"></i> C.2 Pendapatan Resto per Metode
        </h3>
        @if($restoByMethod->count() > 0)
            <div class="space-y-2">
                @foreach($restoByMethod as $method => $amount)
                    <div class="flex justify-between">
                        <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $method) }}</span>
                        <span class="font-bold text-orange-600">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <hr class="my-2">
                <div class="flex justify-between font-bold text-lg">
                    <span>Total Resto</span>
                    <span class="text-orange-700">Rp {{ number_format($restoRevenue, 0, ',', '.') }}</span>
                </div>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">Belum ada transaksi resto</p>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- D. PENGELUARAN --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded shadow p-5 mb-6">
    <h3 class="text-lg font-bold border-b pb-2 mb-4 flex items-center gap-2">
        <i class="fas fa-money-bill-wave text-red-600"></i> D. Pengeluaran
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
            <div class="text-red-600 text-xs font-semibold uppercase">Total Pengeluaran</div>
            <div class="text-2xl font-bold text-red-700">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
        </div>
        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
            <div class="text-red-600 text-xs font-semibold uppercase">Bulan Lalu</div>
            <div class="text-2xl font-bold">Rp {{ number_format($totalExpensesPrev, 0, ',', '.') }}</div>
        </div>
        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
            <div class="text-red-600 text-xs font-semibold uppercase">Growth</div>
            <div class="text-2xl font-bold {{ $expenseGrowth <= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $expenseGrowth >= 0 ? '+' : '' }}{{ $expenseGrowth }}%
            </div>
        </div>
    </div>

    @if($expensesByDesc->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Kategori</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Jumlah</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">% dari Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByDesc as $exp)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $exp->description }}</td>
                    <td class="px-4 py-2 text-right font-bold text-red-600">Rp {{ number_format($exp->total, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right text-gray-600">
                        {{ $totalExpenses > 0 ? round(($exp->total / $totalExpenses) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 font-bold">
                <tr>
                    <td class="px-4 py-2">TOTAL</td>
                    <td class="px-4 py-2 text-right text-red-700">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">100%</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
        <p class="text-gray-500 text-center py-4">Belum ada pengeluaran pada bulan ini</p>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- F. ESTIMASI PAJAK --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded shadow p-5">
        <h3 class="text-lg font-bold border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-calculator text-gray-700"></i> F. Estimasi Pajak (PPN 11%)
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">PPN Kamar</span>
                <span class="font-bold">Rp {{ number_format($ppnRoom, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">PPN Resto</span>
                <span class="font-bold">Rp {{ number_format($ppnResto, 0, ',', '.') }}</span>
            </div>
            <hr>
            <div class="flex justify-between text-lg">
                <span class="font-bold">Total Estimasi PPN</span>
                <span class="font-bold text-red-600">Rp {{ number_format($ppnEstimate, 0, ',', '.') }}</span>
            </div>
            <div class="text-xs text-gray-500 mt-2">
                *Estimasi PPN = (Pendapatan ÷ 1.11) × 11%
            </div>
        </div>
    </div>

    {{-- F.1 Revenue Chart --}}
    <div class="bg-white rounded shadow p-5 md:col-span-2">
        <h3 class="text-lg font-bold border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-line text-blue-600"></i> F.1 Pendapatan Harian
        </h3>
        <canvas id="dailyRevenueChart" height="100"></canvas>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- G. OTHER REVENUE --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded shadow p-5 mb-6">
    <h3 class="text-lg font-bold border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-hand-holding-usd text-purple-600"></i> G. Other Revenue
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="text-purple-600 text-xs font-semibold uppercase">Other Revenue Bulan Ini</div>
            <div class="text-2xl font-bold text-purple-700">Rp {{ number_format($scRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="text-purple-600 text-xs font-semibold uppercase">Bulan Lalu</div>
            <div class="text-2xl font-bold">Rp {{ number_format($scRevenuePrev, 0, ',', '.') }}</div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="text-purple-600 text-xs font-semibold uppercase">% dari Total Revenue</div>
            <div class="text-2xl font-bold text-purple-700">
                {{ $grandRevenue > 0 ? round(($scRevenue / $grandRevenue) * 100, 1) : 0 }}%
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- H. RINGKASAN EKSEKUTIF --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded shadow p-5 mb-6 print:break-inside-avoid">
    <h3 class="text-lg font-bold border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-clipboard-check text-gray-700"></i> H. Ringkasan Eksekutif
    </h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Indikator</th>
                    <th class="px-4 py-2 text-right">Bulan Ini</th>
                    <th class="px-4 py-2 text-right">Bulan Lalu</th>
                    <th class="px-4 py-2 text-right">Perubahan</th>
                    <th class="px-4 py-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b">
                    <td class="px-4 py-2 font-semibold">Total Pendapatan</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($grandRevenue, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($grandRevenuePrev, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right {{ $revenueGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%
                    </td>
                    <td class="px-4 py-2 text-center">
                        @if($revenueGrowth >= 10)
                            <span class="text-green-600"><i class="fas fa-arrow-up"></i></span>
                        @elseif($revenueGrowth >= 0)
                            <span class="text-yellow-600"><i class="fas fa-minus-circle"></i></span>
                        @else
                            <span class="text-red-600"><i class="fas fa-arrow-down"></i></span>
                        @endif
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="px-4 py-2 font-semibold">Pendapatan Bersih</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($netRevenue, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($netRevenuePrev, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right"></td>
                    <td class="px-4 py-2 text-center">
                        @if($netRevenue >= 0)
                            <span class="text-green-600"><i class="fas fa-check-circle"></i> Laba</span>
                        @else
                            <span class="text-red-600"><i class="fas fa-exclamation-circle"></i> Rugi</span>
                        @endif
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="px-4 py-2 font-semibold">Rata-rata Okupansi</td>
                    <td class="px-4 py-2 text-right">{{ $avgOccupancy }}%</td>
                    <td class="px-4 py-2 text-right">-</td>
                    <td class="px-4 py-2 text-right"></td>
                    <td class="px-4 py-2 text-center">
                        @if($avgOccupancy >= 70)
                            <span class="text-green-600">Baik</span>
                        @elseif($avgOccupancy >= 50)
                            <span class="text-yellow-600">Cukup</span>
                        @else
                            <span class="text-red-600">Kurang</span>
                        @endif
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="px-4 py-2 font-semibold">Total Pengeluaran</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($totalExpensesPrev, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right {{ $expenseGrowth <= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $expenseGrowth >= 0 ? '+' : '' }}{{ $expenseGrowth }}%
                    </td>
                    <td class="px-4 py-2 text-center">
                        @if($expenseGrowth <= -5)
                            <span class="text-green-600"><i class="fas fa-check-circle"></i> Efisien</span>
                        @elseif($expenseGrowth <= 5)
                            <span class="text-yellow-600">Stabil</span>
                        @else
                            <span class="text-red-600"><i class="fas fa-exclamation-triangle"></i> Meningkat</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="px-4 py-2 font-semibold">Estimasi PPN 11%</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($ppnEstimate, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">-</td>
                    <td class="px-4 py-2 text-right"></td>
                    <td class="px-4 py-2 text-center text-gray-500">Estimasi</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Occupancy Chart ──────────────────────────────────────────────
    var occCtx = document.getElementById('occupancyChart');
    if (occCtx) {
        new Chart(occCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($occupancyDays as $date => $pct)
                        '{{ Carbon\Carbon::parse($date)->format('d') }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Okupansi %',
                    data: [
                        @foreach($occupancyDays as $date => $pct)
                            {{ $pct }},
                        @endforeach
                    ],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: function(v) { return v + '%'; } }
                    }
                }
            }
        });
    }

    // ── Daily Revenue Chart ──────────────────────────────────────────
    var revCtx = document.getElementById('dailyRevenueChart');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($dailyRevenue as $dr)
                        '{{ $dr['date'] }}',
                    @endforeach
                ],
                datasets: [
                    {
                        label: 'Kamar',
                        data: [
                            @foreach($dailyRevenue as $dr)
                                {{ $dr['room'] }},
                            @endforeach
                        ],
                        backgroundColor: '#3b82f6',
                        borderRadius: 2,
                    },
                    {
                        label: 'Resto',
                        data: [
                            @foreach($dailyRevenue as $dr)
                                {{ $dr['resto'] }},
                            @endforeach
                        ],
                        backgroundColor: '#10b981',
                        borderRadius: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, padding: 8 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: function(v) { return 'Rp' + v.toLocaleString('id-ID'); } }
                    }
                }
            }
        });
    }
});
</script>
@endsection