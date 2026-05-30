@php
    $hotel = \App\Models\HotelSetting::get();
@endphp

<!-- ===== PRINTABLE AREA ===== -->
<div id="printArea" class="bg-white rounded-lg shadow p-6">

    <!-- Header Hotel -->
    <div class="text-center mb-6">
        <div class="flex flex-col items-center mb-2">
            @if($hotel->logo_path)
                <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" class="h-12 mb-2">
            @endif
            <h2 class="text-lg font-bold uppercase tracking-wider text-gray-700">{{ $hotel->hotel_name ?? 'Hotel PMS' }}</h2>
            @if($hotel->address)<p class="text-xs text-gray-500">{{ $hotel->address }}</p>@endif
            @if($hotel->phone)<p class="text-xs text-gray-500">Telp: {{ $hotel->phone }}</p>@endif
        </div>
        <h1 class="text-2xl font-bold uppercase tracking-wider">Night Audit Report</h1>
        <p class="text-gray-600">{{ \Carbon\Carbon::parse($date ?? now())->format('l, d F Y') }}</p>
        <p class="text-xs text-gray-400">Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        <hr class="mt-4 border-t-2 border-gray-800">
    </div>

    <!-- Room Status Summary -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="border-2 border-blue-400 rounded-lg p-4 text-center bg-blue-50">
            <div class="text-xs uppercase text-gray-500 font-bold">Total Room</div>
            <div class="text-4xl font-bold text-blue-700">{{ $totalRooms ?? 0 }}</div>
        </div>
        <div class="border-2 border-red-400 rounded-lg p-4 text-center bg-red-50">
            <div class="text-xs uppercase text-gray-500 font-bold">Occupied</div>
            <div class="text-4xl font-bold text-red-700">{{ $occupiedRooms ?? 0 }}</div>
        </div>
        <div class="border-2 border-green-400 rounded-lg p-4 text-center bg-green-50">
            <div class="text-xs uppercase text-gray-500 font-bold">Available</div>
            <div class="text-4xl font-bold text-green-700">{{ $availableRooms ?? 0 }}</div>
        </div>
        <div class="border-2 border-orange-400 rounded-lg p-4 text-center bg-orange-50">
            <div class="text-xs uppercase text-gray-500 font-bold">Out of Order</div>
            <div class="text-4xl font-bold text-orange-700">{{ $maintenanceRooms ?? 0 }}</div>
        </div>
    </div>

    <!-- Occupancy Rate -->
    <div class="mb-6">
        @php $occRate = $occupancyRate ?? ($totalRooms > 0 ? round((($occupiedRooms ?? 0) / $totalRooms) * 100, 2) : 0); @endphp
        <div class="flex items-center justify-between mb-1">
            <span class="text-sm font-bold text-gray-700">Occupancy Rate</span>
            <span class="text-sm font-bold text-gray-700">{{ $occRate }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $occRate }}%"></div>
        </div>
    </div>

    <hr class="mb-6 border-t border-gray-300">

    <!-- Revenue Section -->
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-gray-800 pb-1">Revenue Summary</h2>

        <div class="bg-green-50 border-2 border-green-400 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-center">
                <span class="text-lg font-bold text-green-800">TOTAL PENDAPATAN HARI INI</span>
                <span class="text-3xl font-bold text-green-700">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center mt-2 text-sm">
                <span class="text-gray-600">Pendapatan Kamar:</span>
                <span class="font-semibold text-green-700">Rp {{ number_format($revenueToday ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Pendapatan Resto / F&amp;B:</span>
                <span class="font-semibold text-orange-600">Rp {{ number_format($restoRevenueToday ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Service Charge:</span>
                <span class="font-semibold text-blue-600">Rp {{ number_format($serviceChargeRevenueToday ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        @if(!empty($revenueByMethod) && count($revenueByMethod) > 0)
        <h3 class="text-sm font-bold text-gray-600 mb-2 uppercase">Detail per Metode Pembayaran</h3>
        @foreach($transactionsByMethod ?? [] as $method => $transactions)
        <div class="mb-4">
            <div class="bg-gray-100 border border-gray-300 rounded p-2 mb-1 flex justify-between items-center">
                <span class="font-bold text-sm">{{ ucwords(str_replace('_', ' ', $method)) }}</span>
                <span class="font-bold text-sm text-green-700">Rp {{ number_format(($revenueByMethod[$method] ?? 0), 0, ',', '.') }}</span>
            </div>
            <table class="w-full text-xs mb-2">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left p-1 font-bold">No. Transaksi</th>
                        <th class="text-left p-1 font-bold">Nama Tamu</th>
                        <th class="text-center p-1 font-bold">Kamar</th>
                        <th class="text-center p-1 font-bold">Status</th>
                        <th class="text-right p-1 font-bold">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $txn)
                    <tr class="border-b border-gray-100">
                        <td class="p-1 font-medium">{{ $txn['transaction_number'] ?? '-' }}</td>
                        <td class="p-1">{{ $txn['guest_name'] ?? '-' }}</td>
                        <td class="p-1 text-center">{{ $txn['room_number'] ?? '-' }}</td>
                        <td class="p-1 text-center">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-indigo-100 text-indigo-800',
                                    'checked_in' => 'bg-green-100 text-green-800',
                                    'checked_out' => 'bg-blue-100 text-blue-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                                $sColor = $statusColors[$txn['status'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-1 py-0.5 rounded text-xs font-bold {{ $sColor }}">
                                {{ strtoupper($txn['status'] ?? '-') }}
                            </span>
                        </td>
                        <td class="p-1 text-right font-bold">Rp {{ number_format($txn['amount'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
        @endif
    </div>

    {{-- Pendapatan Resto/F&B --}}
    @if(!empty($restoTransactions) && count($restoTransactions) > 0)
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-gray-800 pb-1"><i class="fas fa-utensils text-orange-500 mr-2"></i>Pendapatan Resto / F&amp;B</h2>

        @if(!empty($restoRevenueByMethod) && count($restoRevenueByMethod) > 0)
        <div class="grid grid-cols-4 gap-3 mb-4">
            @foreach($restoRevenueByMethod as $method => $total)
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 uppercase font-bold">{{ ucwords(str_replace('_', ' ', $method)) }}</div>
                <div class="text-lg font-bold text-orange-700">Rp {{ number_format($total, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
        @endif

        <table class="w-full text-xs mb-2">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-1 font-bold">No. Transaksi</th>
                    <th class="text-left p-1 font-bold">Waktu</th>
                    <th class="text-left p-1 font-bold">Tamu</th>
                    <th class="text-left p-1 font-bold">Meja</th>
                    <th class="text-left p-1 font-bold">Item</th>
                    <th class="text-center p-1 font-bold">Metode</th>
                    <th class="text-right p-1 font-bold">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($restoTransactions as $txn)
                <tr class="border-b border-gray-100">
                    <td class="p-1 font-medium">{{ $txn['transaction_number'] ?? '-' }}</td>
                    <td class="p-1">{{ $txn['created_at'] ?? '-' }}</td>
                    <td class="p-1">{{ $txn['guest_name'] ?? 'Walk-in' }}</td>
                    <td class="p-1">{{ $txn['table_number'] ?? '-' }}</td>
                    <td class="p-1">
                        @foreach(array_slice($txn['items'] ?? [], 0, 2) as $item)
                            <span class="inline-block bg-gray-100 rounded px-1 mr-1">{{ $item['name'] ?? '' }} ×{{ $item['qty'] ?? 0 }}</span>
                        @endforeach
                        @if(count($txn['items'] ?? []) > 2)
                            <span class="text-gray-400">+{{ count($txn['items']) - 2 }}</span>
                        @endif
                    </td>
                    <td class="p-1 text-center">{{ ucwords(str_replace('_', ' ', $txn['payment_method'] ?? '-')) }}</td>
                    <td class="p-1 text-right font-bold">Rp {{ number_format($txn['total_amount'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-orange-50 border-t-2 border-orange-300">
                    <td colspan="6" class="p-2 text-right font-bold text-orange-800">TOTAL PENDAPATAN RESTO</td>
                    <td class="p-2 text-right font-bold text-orange-700">Rp {{ number_format($restoRevenueToday ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- Service Charge --}}
    @if(!empty($serviceCharges) && count($serviceCharges) > 0)
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-gray-800 pb-1"><i class="fas fa-receipt text-blue-500 mr-2"></i>Service Charge</h2>

        @if(!empty($serviceChargeByMethod) && count($serviceChargeByMethod) > 0)
        <div class="grid grid-cols-4 gap-3 mb-4">
            @foreach($serviceChargeByMethod as $method => $total)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 uppercase font-bold">{{ ucwords(str_replace('_', ' ', $method)) }}</div>
                <div class="text-lg font-bold text-blue-700">Rp {{ number_format($total, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
        @endif

        <table class="w-full text-xs mb-2">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-1 font-bold">No. Charge</th>
                    <th class="text-left p-1 font-bold">Tamu</th>
                    <th class="text-left p-1 font-bold">Kamar</th>
                    <th class="text-left p-1 font-bold">Layanan</th>
                    <th class="text-center p-1 font-bold">Qty</th>
                    <th class="text-left p-1 font-bold">Metode</th>
                    <th class="text-right p-1 font-bold">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceCharges as $sc)
                <tr class="border-b border-gray-100">
                    <td class="p-1 font-medium">{{ $sc['charge_number'] ?? '-' }}</td>
                    <td class="p-1">{{ $sc['guest_name'] ?? '-' }}</td>
                    <td class="p-1">{{ $sc['room_number'] ?? '-' }}</td>
                    <td class="p-1">{{ $sc['service_name'] ?? '-' }}</td>
                    <td class="p-1 text-center">{{ $sc['quantity'] ?? 0 }}</td>
                    <td class="p-1">{{ $sc['payment_method'] ? ucwords(str_replace('_', ' ', $sc['payment_method'])) : '-' }}</td>
                    <td class="p-1 text-right font-bold">Rp {{ number_format($sc['total_amount'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-blue-50 border-t-2 border-blue-300">
                    <td colspan="6" class="p-2 text-right font-bold text-blue-800">TOTAL SERVICE CHARGE</td>
                    <td class="p-2 text-right font-bold text-blue-700">Rp {{ number_format($serviceChargeRevenueToday ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <hr class="mb-6 border-t border-gray-300">

    <!-- Check-in / Check-out Summary -->
    <div class="grid grid-cols-2 gap-6 mb-6">
        <!-- Check-in -->
        <div>
            <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-green-600 pb-1 text-green-700">
                Check-in ({{ count($checkinsToday ?? []) }})
            </h2>
            @if(count($checkinsToday ?? []) > 0)
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-green-50 border-b border-green-200">
                        <th class="text-left p-2 font-bold text-xs">NO. RES</th>
                        <th class="text-left p-2 font-bold text-xs">TAMU</th>
                        <th class="text-center p-2 font-bold text-xs">KAMAR</th>
                        <th class="text-center p-2 font-bold text-xs">SARAPAN</th>
                        <th class="text-right p-2 font-bold text-xs">CHECK-OUT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checkinsToday ?? [] as $res)
                    <tr class="border-b border-gray-100">
                        <td class="p-2 font-medium text-xs">{{ $res['reservation_number'] ?? '-' }}</td>
                        <td class="p-2 text-xs">{{ $res['guest_name'] ?? '-' }}</td>
                        <td class="p-2 text-center font-bold text-xs">{{ $res['room_number'] ?? '-' }}</td>
                        <td class="p-2 text-center text-xs">
                            @if(!empty($res['include_breakfast']))
                                <span class="px-1 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800"><i class="fas fa-coffee"></i></span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="p-2 text-right text-xs">{{ $res['check_out'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-gray-400 text-center py-6 text-sm italic">Tidak ada check-in</p>
            @endif
        </div>

        <!-- Check-out -->
        <div>
            <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-blue-600 pb-1 text-blue-700">
                Check-out ({{ count($checkoutsToday ?? []) }})
            </h2>
            @if(count($checkoutsToday ?? []) > 0)
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-blue-50 border-b border-blue-200">
                        <th class="text-left p-2 font-bold text-xs">NO. RES</th>
                        <th class="text-left p-2 font-bold text-xs">TAMU</th>
                        <th class="text-center p-2 font-bold text-xs">KAMAR</th>
                        <th class="text-center p-2 font-bold text-xs">SARAPAN</th>
                        <th class="text-right p-2 font-bold text-xs">CHECK-IN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checkoutsToday ?? [] as $res)
                    <tr class="border-b border-gray-100">
                        <td class="p-2 font-medium text-xs">{{ $res['reservation_number'] ?? '-' }}</td>
                        <td class="p-2 text-xs">{{ $res['guest_name'] ?? '-' }}</td>
                        <td class="p-2 text-center font-bold text-xs">{{ $res['room_number'] ?? '-' }}</td>
                        <td class="p-2 text-center text-xs">
                            @if(!empty($res['include_breakfast']))
                                <span class="px-1 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800"><i class="fas fa-coffee"></i></span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="p-2 text-right text-xs">{{ $res['check_in'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-gray-400 text-center py-6 text-sm italic">Tidak ada check-out</p>
            @endif
        </div>
    </div>

    <hr class="mb-6 border-t border-gray-300">

    <!-- In-House Guests -->
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-purple-600 pb-1 text-purple-700">
            In-House Guests ({{ count($inHouseGuests ?? []) }})
        </h2>
        @if(count($inHouseGuests ?? []) > 0)
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-purple-50 border-b border-purple-200">
                    <th class="text-left p-2 font-bold text-xs">NO. RES</th>
                    <th class="text-left p-2 font-bold text-xs">NAMA TAMU</th>
                    <th class="text-center p-2 font-bold text-xs">KAMAR</th>
                    <th class="text-center p-2 font-bold text-xs">CHECK-IN</th>
                    <th class="text-center p-2 font-bold text-xs">CHECK-OUT</th>
                    <th class="text-center p-2 font-bold text-xs">LAMA INAP</th>
                    <th class="text-center p-2 font-bold text-xs">SARAPAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inHouseGuests ?? [] as $res)
                <tr class="border-b border-gray-100">
                    <td class="p-2 font-medium text-xs">{{ $res['reservation_number'] ?? '-' }}</td>
                    <td class="p-2 text-xs">{{ $res['guest_name'] ?? '-' }}</td>
                    <td class="p-2 text-center font-bold text-xs">{{ $res['room_number'] ?? '-' }}</td>
                    <td class="p-2 text-center text-xs">{{ $res['check_in'] ?? '-' }}</td>
                    <td class="p-2 text-center text-xs">{{ $res['check_out'] ?? '-' }}</td>
                    <td class="p-2 text-center">
                        <span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-800">
                            {{ $res['total_nights'] ?? 0 }} malam
                        </span>
                    </td>
                    <td class="p-2 text-center text-xs">
                        @if(!empty($res['include_breakfast']))
                            <span class="px-1 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800"><i class="fas fa-coffee"></i></span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-gray-400 text-center py-6 text-sm italic">Tidak ada in-house guest</p>
        @endif
    </div>

    <hr class="mb-6 border-t border-gray-300">

    <!-- New Bookings -->
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-blue-600 pb-1 text-blue-700">
            New Bookings ({{ count($newBookings ?? []) }})
        </h2>
        @if(count($newBookings ?? []) > 0)
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-blue-50 border-b border-blue-200">
                    <th class="text-left p-2 font-bold text-xs">NO. RES</th>
                    <th class="text-left p-2 font-bold text-xs">NAMA TAMU</th>
                    <th class="text-center p-2 font-bold text-xs">KAMAR</th>
                    <th class="text-center p-2 font-bold text-xs">CHECK-IN</th>
                    <th class="text-center p-2 font-bold text-xs">CHECK-OUT</th>
                    <th class="text-center p-2 font-bold text-xs">STATUS</th>
                    <th class="text-center p-2 font-bold text-xs">SARAPAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($newBookings ?? [] as $res)
                <tr class="border-b border-gray-100">
                    <td class="p-2 font-medium text-xs">{{ $res['reservation_number'] ?? '-' }}</td>
                    <td class="p-2 text-xs">{{ $res['guest_name'] ?? '-' }}</td>
                    <td class="p-2 text-center font-bold text-xs">{{ $res['room_number'] ?? '-' }}</td>
                    <td class="p-2 text-center text-xs">{{ $res['check_in'] ?? '-' }}</td>
                    <td class="p-2 text-center text-xs">{{ $res['check_out'] ?? '-' }}</td>
                    <td class="p-2 text-center">
                        <span class="px-2 py-1 rounded text-xs font-bold
                            @if(($res['status'] ?? '') === 'pending') bg-indigo-100 text-indigo-800
                            @elseif(($res['status'] ?? '') === 'checked_in') bg-green-100 text-green-800
                            @elseif(($res['status'] ?? '') === 'checked_out') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ strtoupper($res['status'] ?? '-') }}
                        </span>
                    </td>
                    <td class="p-2 text-center text-xs">
                        @if(!empty($res['include_breakfast']))
                            <span class="px-1 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800"><i class="fas fa-coffee"></i></span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-gray-400 text-center py-6 text-sm italic">Tidak ada booking baru</p>
        @endif
    </div>

    <!-- Sign-off -->
    <div class="mt-8 pt-4 border-t-2 border-gray-300">
        <div class="grid grid-cols-3 gap-4 text-center text-sm">
            <div>
                <div class="border-b border-gray-400 mb-8"></div>
                <p class="font-bold">Prepared By</p>
                <p class="text-xs text-gray-500">Night Auditor</p>
            </div>
            <div>
                <div class="border-b border-gray-400 mb-8"></div>
                <p class="font-bold">Checked By</p>
                <p class="text-xs text-gray-500">Supervisor</p>
            </div>
            <div>
                <div class="border-b border-gray-400 mb-8"></div>
                <p class="font-bold">Approved By</p>
                <p class="text-xs text-gray-500">Manager</p>
            </div>
        </div>
    </div>

</div><!-- /printArea -->

<style>
@media print {
    .no-print, aside, nav, header, .sidebar-item, .bg-blue-800, .bg-white.shadow-sm,
    form, button, .no-print\:block { display: none !important; }
    body { background: white !important; margin: 0 !important; padding: 10px !important; font-size: 11px !important; }
    .flex.h-screen, .flex-1, .overflow-y-auto, .container.mx-auto {
        display: block !important; width: 100% !important; max-width: 100% !important;
        margin: 0 !important; padding: 0 !important; overflow: visible !important;
    }
    .shadow { box-shadow: none !important; }
    .rounded-lg { border: 1px solid #ccc !important; border-radius: 0 !important; }
    table { width: 100% !important; border-collapse: collapse !important; font-size: 10px !important; }
    th, td { padding: 3px 5px !important; border: 1px solid #999 !important; }
    th { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    h1 { font-size: 16px !important; }
    h2 { font-size: 12px !important; }
    .text-4xl { font-size: 22px !important; }
    .text-3xl { font-size: 16px !important; }
    .text-lg { font-size: 11px !important; }
    .text-sm { font-size: 9px !important; }
    .text-xs { font-size: 8px !important; }
    hr { border-color: #333 !important; }
    .border-2 { border-width: 1px !important; }
    #printArea { padding: 0 !important; margin: 0 !important; }
}
</style>
