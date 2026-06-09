@php
    $hotel = \App\Models\HotelSetting::first();
    $statusLabels = [
        'pending' => 'Pending',
        'menunggu_pembayaran' => 'Menunggu Transfer',
        'checked_in' => 'Check In',
        'checked_out' => 'Check Out',
        'cancelled' => 'Batal',
        'no_show' => 'No Show',
    ];
@endphp

<!-- ===== PRINTABLE AREA ===== -->
<div id="printArea" class="bg-white rounded-lg shadow p-6">

    <!-- Header Hotel -->
    <div class="text-center mb-6">
        <div class="flex flex-col items-center mb-2">
            @if($hotel->logo_path)
                <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" class="h-12 mb-2">
            @endif
            <h2 class="text-lg font-bold uppercase tracking-wider text-gray-700">{{ $hotel->hotel_name ?? 'Dynamic PMS V.2' }}</h2>
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

            {{-- Room Revenue Breakdown: Cash | OTA | Web --}}
            <div class="mt-3 pt-3 border-t border-green-200">
                <div class="flex justify-between items-center text-sm mb-1">
                    <span class="font-bold text-gray-700">PENDAPATAN KAMAR</span>
                    <span class="font-bold text-green-700">Rp {{ number_format($revenueToday ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="ml-4 space-y-1">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600"><i class="fas fa-money-bill-wave text-green-500 mr-1"></i>Cash</span>
                        <span class="font-semibold text-green-700">Rp {{ number_format($cashRevenueToday ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600"><i class="fas fa-globe text-purple-500 mr-1"></i>OTA</span>
                        <span class="font-semibold text-purple-700">Rp {{ number_format($otaRevenueToday ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600"><i class="fas fa-laptop text-blue-500 mr-1"></i>Web / Direct</span>
                        <span class="font-semibold text-blue-700">Rp {{ number_format($webRevenueToday ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center mt-2 text-sm">
                <span class="text-gray-600">Pendapatan Resto / F&amp;B:</span>
                <span class="font-semibold text-orange-600">Rp {{ number_format($restoRevenueToday ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Other Revenue:</span>
                <span class="font-semibold text-blue-600">Rp {{ number_format($serviceChargeRevenueToday ?? 0, 0, ',', '.') }}</span>
            </div>
            @if(($depositRevenueToday ?? 0) > 0)
            <div class="flex justify-between items-center mt-2 pt-2 border-t border-green-200 text-sm">
                <span class="text-gray-600"><i class="fas fa-id-card text-teal-500 mr-1"></i>Deposit Key Card:</span>
                <span class="font-semibold text-teal-600">Rp {{ number_format($depositRevenueToday ?? 0, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        @if(!empty($revenueByMethod) && count($revenueByMethod) > 0)
        <h3 class="text-sm font-bold text-gray-600 mb-2 uppercase">Detail per Metode Pembayaran</h3>
        @php
            $typeLabels = [
                'dp' => 'DP',
                'pelunasan' => 'Pelunasan',
                'checkin_payment' => 'Check-in Payment',
                'additional' => 'Additional',
                'checkout_payment' => 'Check-out Payment',
                'refund' => 'Refund',
                'tambahan' => 'Tambahan',
                'ota_payment' => 'OTA Payment',
            ];
        @endphp
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
                        <th class="text-left p-1 font-bold">Tipe / Item</th>
                        <th class="text-center p-1 font-bold">Sumber</th>
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
                        <td class="p-1">
                            <span class="px-1 py-0.5 rounded text-xs font-bold
                                @if(($txn['type'] ?? '') === 'dp') bg-amber-100 text-amber-800
                                @elseif(($txn['type'] ?? '') === 'pelunasan') bg-emerald-100 text-emerald-800
                                @elseif(($txn['type'] ?? '') === 'checkin_payment') bg-sky-100 text-sky-800
                                @elseif(($txn['type'] ?? '') === 'checkout_payment') bg-indigo-100 text-indigo-800
                                @elseif(($txn['type'] ?? '') === 'additional' || ($txn['type'] ?? '') === 'tambahan') bg-orange-100 text-orange-800
                                @elseif(($txn['type'] ?? '') === 'refund') bg-rose-100 text-rose-800
                                @elseif(($txn['type'] ?? '') === 'ota_payment') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $typeLabels[$txn['type'] ?? ''] ?? ucwords(str_replace('_', ' ', $txn['type'] ?? '-')) }}
                            </span>
                            @if(!empty($txn['notes']))
                                <br><small class="text-gray-400 italic">{{ $txn['notes'] }}</small>
                            @endif
                        </td>
                        <td class="p-1 text-center">
                            @php
                                $srcColors = [
                                    'Cash' => 'bg-green-100 text-green-800',
                                    'OTA' => 'bg-purple-100 text-purple-800',
                                    'Web' => 'bg-blue-100 text-blue-800',
                                ];
                                $srcColor = $srcColors[$txn['source'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-1 py-0.5 rounded text-xs font-bold {{ $srcColor }}">
                                {{ $txn['source'] ?? '-' }}
                                @if(!empty($txn['ota_source']))<br><small class="font-normal">{{ $txn['ota_source'] }}</small>@endif
                            </span>
                        </td>
                        <td class="p-1 text-center">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-indigo-100 text-indigo-800',
                                    'menunggu_pembayaran' => 'bg-yellow-100 text-yellow-800',
                                    'checked_in' => 'bg-green-100 text-green-800',
                                    'checked_out' => 'bg-blue-100 text-blue-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                                $sColor = $statusColors[$txn['status'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-1 py-0.5 rounded text-xs font-bold {{ $sColor }}">
                                {{ $statusLabels[$txn['status'] ?? ''] ?? strtoupper(str_replace('_', ' ', $txn['status'] ?? '-')) }}
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

    {{-- Pengeluaran (Expenses) --}}
    @if(!empty($expensesList) && count($expensesList) > 0)
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-red-800 pb-1 text-red-700">
            <i class="fas fa-money-bill-wave text-red-500 mr-2"></i>Pengeluaran (Expenses)
        </h2>

        <div class="bg-red-50 border-2 border-red-400 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-center">
                <span class="text-lg font-bold text-red-800">TOTAL PENGELUARAN HARI INI</span>
                <span class="text-3xl font-bold text-red-700">Rp {{ number_format($expensesToday ?? 0, 0, ',', '.') }}</span>
            </div>
            <p class="text-xs text-gray-500 mt-1 italic">* Pengeluaran tidak mengurangi total pendapatan (ditampilkan sebagai informasi terpisah)</p>
        </div>

        @if(!empty($expensesByMethod) && count($expensesByMethod) > 0)
        <h3 class="text-sm font-bold text-gray-600 mb-2 uppercase">Detail per Metode Pembayaran</h3>
        <div class="grid grid-cols-4 gap-3 mb-4">
            @foreach($expensesByMethod as $method => $total)
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 uppercase font-bold">{{ ucwords(str_replace('_', ' ', $method)) }}</div>
                <div class="text-lg font-bold text-red-700">Rp {{ number_format($total, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
        @endif

        <table class="w-full text-xs mb-2">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-1 font-bold">No. Expense</th>
                    <th class="text-left p-1 font-bold">Deskripsi</th>
                    <th class="text-left p-1 font-bold">Keterangan</th>
                    <th class="text-center p-1 font-bold">Metode</th>
                    <th class="text-right p-1 font-bold">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesList as $e)
                <tr class="border-b border-gray-100">
                    <td class="p-1 font-medium">{{ $e['expense_number'] ?? '-' }}</td>
                    <td class="p-1">{{ $e['description'] ?? '-' }}</td>
                    <td class="p-1 text-gray-400 italic">{{ $e['notes'] ?? '-' }}</td>
                    <td class="p-1 text-center capitalize">{{ str_replace('_', ' ', $e['payment_method'] ?? '-') }}</td>
                    <td class="p-1 text-right font-bold text-red-600">Rp {{ number_format($e['amount'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-red-50 border-t-2 border-red-300">
                    <td colspan="4" class="p-2 text-right font-bold text-red-800">TOTAL PENGELUARAN</td>
                    <td class="p-2 text-right font-bold text-red-700">Rp {{ number_format($expensesToday ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

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

    {{-- Other Revenue --}}
    @if(!empty($serviceCharges) && count($serviceCharges) > 0)
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-gray-800 pb-1"><i class="fas fa-receipt text-blue-500 mr-2"></i>Other Revenue</h2>

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
                    <td colspan="6" class="p-2 text-right font-bold text-blue-800">TOTAL OTHER REVENUE</td>
                    <td class="p-2 text-right font-bold text-blue-700">Rp {{ number_format($serviceChargeRevenueToday ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- Deposit Key Card --}}
    @if(($depositRevenueToday ?? 0) > 0 || (isset($depositList) && count($depositList) > 0))
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-teal-700 pb-1 text-teal-700">
            <i class="fas fa-id-card text-teal-500 mr-2"></i>Deposit Key Card
        </h2>

        <div class="bg-teal-50 border-2 border-teal-400 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-center">
                <span class="text-lg font-bold text-teal-800">TOTAL DEPOSIT KEY CARD HARI INI</span>
                <span class="text-3xl font-bold text-teal-700">Rp {{ number_format($depositRevenueToday ?? 0, 0, ',', '.') }}</span>
            </div>
            <p class="text-xs text-gray-500 mt-1 italic">* Deposit key card (jaminan kartu akses kamar)</p>
        </div>

        @if(!empty($depositByMethod) && count($depositByMethod) > 0)
        <h3 class="text-sm font-bold text-gray-600 mb-2 uppercase">Detail per Metode Pembayaran</h3>
        <div class="grid grid-cols-4 gap-3 mb-4">
            @foreach($depositByMethod as $method => $total)
            <div class="bg-teal-50 border border-teal-200 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 uppercase font-bold">{{ ucwords(str_replace('_', ' ', $method)) }}</div>
                <div class="text-lg font-bold text-teal-700">Rp {{ number_format($total, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
        @endif

        <table class="w-full text-xs mb-2">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-1 font-bold">No. Receipt</th>
                    <th class="text-left p-1 font-bold">Waktu</th>
                    <th class="text-left p-1 font-bold">Tamu</th>
                    <th class="text-center p-1 font-bold">Kamar</th>
                    <th class="text-center p-1 font-bold">Kartu</th>
                    <th class="text-right p-1 font-bold">Per Kartu</th>
                    <th class="text-center p-1 font-bold">Metode</th>
                    <th class="text-right p-1 font-bold">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($depositList ?? [] as $d)
                <tr class="border-b border-gray-100">
                    <td class="p-1 font-medium">{{ $d['receipt_number'] ?? '-' }}</td>
                    <td class="p-1">{{ $d['created_at'] ?? '-' }}</td>
                    <td class="p-1">{{ $d['guest_name'] ?? '-' }}</td>
                    <td class="p-1 text-center">{{ $d['room_number'] ?? '-' }}</td>
                    <td class="p-1 text-center">{{ $d['number_of_cards'] ?? 0 }}</td>
                    <td class="p-1 text-right">Rp {{ number_format($d['nominal_per_card'] ?? 0, 0, ',', '.') }}</td>
                    <td class="p-1 text-center capitalize">{{ str_replace('_', ' ', $d['payment_method'] ?? '-') }}</td>
                    <td class="p-1 text-right font-bold text-teal-600">Rp {{ number_format($d['total_amount'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-teal-50 border-t-2 border-teal-300">
                    <td colspan="7" class="p-2 text-right font-bold text-teal-800">TOTAL DEPOSIT KEY CARD</td>
                    <td class="p-2 text-right font-bold text-teal-700">Rp {{ number_format($depositRevenueToday ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- Ringkasan Kas (Cash Flow) --}}
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-yellow-700 pb-1 text-yellow-700">
            <i class="fas fa-calculator text-yellow-600 mr-2"></i>Ringkasan Kas (Cash Flow)
        </h2>

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-green-50 border-2 border-green-400 rounded-lg p-4 text-center">
                <div class="text-xs uppercase text-gray-500 font-bold">Total Pemasukan Tunai</div>
                <div class="text-2xl font-bold text-green-700">Rp {{ number_format(($cashRevenue ?? 0) + ($cashDeposits ?? 0), 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">Pembayaran tunai (Reservasi, Resto, SC, Deposit)</div>
            </div>
            <div class="bg-red-50 border-2 border-red-400 rounded-lg p-4 text-center">
                <div class="text-xs uppercase text-gray-500 font-bold">Total Pengeluaran Tunai</div>
                <div class="text-2xl font-bold text-red-700">Rp {{ number_format($cashExpenses ?? 0, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">Pengeluaran tunai (Operasional)</div>
            </div>
            <div class="bg-blue-50 border-2 border-blue-400 rounded-lg p-4 text-center">
                <div class="text-xs uppercase text-gray-500 font-bold">Sisa Kas (Cash Balance)</div>
                <div class="text-2xl font-bold text-blue-700">Rp {{ number_format($cashFlowBalance ?? 0, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">Pemasukan - Pengeluaran Tunai</div>
            </div>
        </div>
    </div>

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
                                <span class="px-1 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800"><i class="fas fa-coffee"></i><span class="print-only">Ya</span></span>
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
                                <span class="px-1 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800"><i class="fas fa-coffee"></i><span class="print-only">Ya</span></span>
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
                            <span class="px-1 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800"><i class="fas fa-coffee"></i><span class="print-only">Ya</span></span>
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

    <!-- OTA Bookings -->
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-purple-600 pb-1 text-purple-700">
            <i class="fas fa-globe text-purple-500 mr-2"></i>OTA Bookings ({{ count($otaBookings ?? []) }})
        </h2>
        @if(count($otaBookings ?? []) > 0)
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-purple-50 border-b border-purple-200">
                    <th class="text-left p-2 font-bold text-xs">NO. RES</th>
                    <th class="text-left p-2 font-bold text-xs">NAMA TAMU</th>
                    <th class="text-center p-2 font-bold text-xs">KAMAR</th>
                    <th class="text-center p-2 font-bold text-xs">CHECK-IN</th>
                    <th class="text-center p-2 font-bold text-xs">CHECK-OUT</th>
                    <th class="text-right p-2 font-bold text-xs">NOMINAL (Rp)</th>
                    <th class="text-center p-2 font-bold text-xs">OTA</th>
                    <th class="text-center p-2 font-bold text-xs">STATUS</th>
                    <th class="text-center p-2 font-bold text-xs">SARAPAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($otaBookings ?? [] as $res)
                <tr class="border-b border-gray-100">
                    <td class="p-2 font-medium text-xs">{{ $res['reservation_number'] ?? '-' }}</td>
                    <td class="p-2 text-xs">{{ $res['guest_name'] ?? '-' }}</td>
                    <td class="p-2 text-center font-bold text-xs">{{ $res['room_number'] ?? '-' }}</td>
                    <td class="p-2 text-center text-xs">{{ $res['check_in'] ?? '-' }}</td>
                    <td class="p-2 text-center text-xs">{{ $res['check_out'] ?? '-' }}</td>
                    <td class="p-2 text-right font-bold text-xs">Rp {{ number_format($res['total_amount'] ?? 0, 0, ',', '.') }}</td>
                    <td class="p-2 text-center text-xs">
                        <span class="px-1 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-800">{{ $res['ota_source'] }}</span>
                    </td>
                    <td class="p-2 text-center">
                        <span class="px-2 py-1 rounded text-xs font-bold
                            @if(($res['status'] ?? '') === 'pending') bg-indigo-100 text-indigo-800
                            @elseif(($res['status'] ?? '') === 'menunggu_pembayaran') bg-yellow-100 text-yellow-800
                            @elseif(($res['status'] ?? '') === 'checked_in') bg-green-100 text-green-800
                            @elseif(($res['status'] ?? '') === 'checked_out') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $statusLabels[$res['status'] ?? ''] ?? strtoupper(str_replace('_', ' ', $res['status'] ?? '-')) }}
                        </span>
                    </td>
                    <td class="p-2 text-center text-xs">
                        @if(!empty($res['include_breakfast']))
                            <span class="px-1 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800"><i class="fas fa-coffee"></i><span class="print-only">Ya</span></span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-purple-50 border-t-2 border-purple-300">
                    <td colspan="5" class="p-2 text-right font-bold text-xs text-purple-800">TOTAL OTA BOOKING</td>
                    <td class="p-2 text-right font-bold text-xs text-purple-700">Rp {{ number_format(collect($otaBookings)->sum('total_amount'), 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        @else
        <p class="text-gray-400 text-center py-4 text-sm italic">Tidak ada booking OTA</p>
        @endif
    </div>

    <!-- Web Bookings -->
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-blue-600 pb-1 text-blue-700">
            <i class="fas fa-laptop text-blue-500 mr-2"></i>Web Bookings ({{ count($webBookings ?? []) }})
        </h2>
        @if(count($webBookings ?? []) > 0)
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-blue-50 border-b border-blue-200">
                    <th class="text-left p-2 font-bold text-xs">NO. RES</th>
                    <th class="text-left p-2 font-bold text-xs">NAMA TAMU</th>
                    <th class="text-center p-2 font-bold text-xs">KAMAR</th>
                    <th class="text-center p-2 font-bold text-xs">CHECK-IN</th>
                    <th class="text-center p-2 font-bold text-xs">CHECK-OUT</th>
                    <th class="text-right p-2 font-bold text-xs">NOMINAL (Rp)</th>
                    <th class="text-center p-2 font-bold text-xs">PEMBAYARAN</th>
                    <th class="text-center p-2 font-bold text-xs">STATUS</th>
                    <th class="text-center p-2 font-bold text-xs">SARAPAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($webBookings ?? [] as $res)
                <tr class="border-b border-gray-100">
                    <td class="p-2 font-medium text-xs">{{ $res['reservation_number'] ?? '-' }}</td>
                    <td class="p-2 text-xs">{{ $res['guest_name'] ?? '-' }}</td>
                    <td class="p-2 text-center font-bold text-xs">{{ $res['room_number'] ?? '-' }}</td>
                    <td class="p-2 text-center text-xs">{{ $res['check_in'] ?? '-' }}</td>
                    <td class="p-2 text-center text-xs">{{ $res['check_out'] ?? '-' }}</td>
                    <td class="p-2 text-right font-bold text-xs">Rp {{ number_format($res['total_amount'] ?? 0, 0, ',', '.') }}</td>
                    <td class="p-2 text-center text-xs">
                        <span class="px-1 py-0.5 rounded text-xs font-bold bg-cyan-100 text-cyan-800">{{ ucwords(str_replace('_', ' ', $res['payment_method'] ?? '-')) }}</span>
                    </td>
                    <td class="p-2 text-center">
                        <span class="px-2 py-1 rounded text-xs font-bold
                            @if(($res['status'] ?? '') === 'pending') bg-indigo-100 text-indigo-800
                            @elseif(($res['status'] ?? '') === 'menunggu_pembayaran') bg-yellow-100 text-yellow-800
                            @elseif(($res['status'] ?? '') === 'checked_in') bg-green-100 text-green-800
                            @elseif(($res['status'] ?? '') === 'checked_out') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $statusLabels[$res['status'] ?? ''] ?? strtoupper(str_replace('_', ' ', $res['status'] ?? '-')) }}
                        </span>
                    </td>
                    <td class="p-2 text-center text-xs">
                        @if(!empty($res['include_breakfast']))
                            <span class="px-1 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800"><i class="fas fa-coffee"></i><span class="print-only">Ya</span></span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-blue-50 border-t-2 border-blue-300">
                    <td colspan="5" class="p-2 text-right font-bold text-xs text-blue-800">TOTAL WEB BOOKING</td>
                    <td class="p-2 text-right font-bold text-xs text-blue-700">Rp {{ number_format(collect($webBookings)->sum('total_amount'), 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        @else
        <p class="text-gray-400 text-center py-4 text-sm italic">Tidak ada web booking</p>
        @endif
    </div>

    <!-- Direct Bookings (Cash / Langsung) -->
    <div class="mb-6">
        <h2 class="text-lg font-bold uppercase mb-3 border-b-2 border-green-600 pb-1 text-green-700">
            <i class="fas fa-building text-green-500 mr-2"></i>Direct Bookings ({{ count($directBookings ?? []) }})
        </h2>
        @if(count($directBookings ?? []) > 0)
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-green-50 border-b border-green-200">
                    <th class="text-left p-2 font-bold text-xs">NO. RES</th>
                    <th class="text-left p-2 font-bold text-xs">NAMA TAMU</th>
                    <th class="text-center p-2 font-bold text-xs">KAMAR</th>
                    <th class="text-center p-2 font-bold text-xs">CHECK-IN</th>
                    <th class="text-center p-2 font-bold text-xs">CHECK-OUT</th>
                    <th class="text-right p-2 font-bold text-xs">NOMINAL (Rp)</th>
                    <th class="text-center p-2 font-bold text-xs">STATUS</th>
                    <th class="text-center p-2 font-bold text-xs">SARAPAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($directBookings ?? [] as $res)
                <tr class="border-b border-gray-100">
                    <td class="p-2 font-medium text-xs">{{ $res['reservation_number'] ?? '-' }}</td>
                    <td class="p-2 text-xs">{{ $res['guest_name'] ?? '-' }}</td>
                    <td class="p-2 text-center font-bold text-xs">{{ $res['room_number'] ?? '-' }}</td>
                    <td class="p-2 text-center text-xs">{{ $res['check_in'] ?? '-' }}</td>
                    <td class="p-2 text-center text-xs">{{ $res['check_out'] ?? '-' }}</td>
                    <td class="p-2 text-right font-bold text-xs">Rp {{ number_format($res['total_amount'] ?? 0, 0, ',', '.') }}</td>
                    <td class="p-2 text-center">
                        <span class="px-2 py-1 rounded text-xs font-bold
                            @if(($res['status'] ?? '') === 'pending') bg-indigo-100 text-indigo-800
                            @elseif(($res['status'] ?? '') === 'menunggu_pembayaran') bg-yellow-100 text-yellow-800
                            @elseif(($res['status'] ?? '') === 'checked_in') bg-green-100 text-green-800
                            @elseif(($res['status'] ?? '') === 'checked_out') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $statusLabels[$res['status'] ?? ''] ?? strtoupper(str_replace('_', ' ', $res['status'] ?? '-')) }}
                        </span>
                    </td>
                    <td class="p-2 text-center text-xs">
                        @if(!empty($res['include_breakfast']))
                            <span class="px-1 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800"><i class="fas fa-coffee"></i><span class="print-only">Ya</span></span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-green-50 border-t-2 border-green-300">
                    <td colspan="5" class="p-2 text-right font-bold text-xs text-green-800">TOTAL DIRECT BOOKING</td>
                    <td class="p-2 text-right font-bold text-xs text-green-700">Rp {{ number_format(collect($directBookings)->sum('total_amount'), 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        @else
        <p class="text-gray-400 text-center py-4 text-sm italic">Tidak ada direct booking</p>
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
    /* ── Force B&W / grayscale ── */
    * {
        background: transparent !important;
        background-color: transparent !important;
        color: #000 !important;
        border-color: #999 !important;
        text-shadow: none !important;
        box-shadow: none !important;
        -webkit-print-color-adjust: economy;
        print-color-adjust: economy;
    }

    /* ── Hide non-print elements ── */
    .no-print, aside, nav, header, .sidebar-item, .bg-blue-800,
    form, button, .no-print\:block, i.fas, i.far, i.fab { display: none !important; }

    /* ── Page reset ── */
    @page { margin: 15mm 10mm; }
    body {
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 10pt !important;
        line-height: 1.4 !important;
        color: #000 !important;
    }

    /* ── Layout reset ── */
    .flex.h-screen, .flex-1, .overflow-y-auto, .container.mx-auto {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
    }

    /* ── Print container ── */
    #printArea {
        padding: 0 !important;
        margin: 0 !important;
        background: white !important;
    }

    /* ── Grid & card overrides ── */
    .grid { display: flex !important; flex-wrap: wrap !important; gap: 8px !important; }
    .grid-cols-4 > * { flex: 1 1 22% !important; min-width: 100px !important; }
    .grid-cols-3 > * { flex: 1 1 30% !important; min-width: 120px !important; }
    .grid-cols-2 { display: flex !important; flex-wrap: wrap !important; gap: 12px !important; }
    .grid-cols-2 > * { flex: 1 1 45% !important; min-width: 200px !important; }
    .gap-4 { gap: 8px !important; }
    .gap-6 { gap: 12px !important; }
    .gap-3 { gap: 6px !important; }
    .mb-6 { margin-bottom: 12px !important; }
    .mb-4 { margin-bottom: 8px !important; }
    .mb-3 { margin-bottom: 6px !important; }
    .p-4 { padding: 8px !important; }
    .p-6 { padding: 10px !important; }
    .p-3 { padding: 6px !important; }
    .p-2 { padding: 4px 6px !important; }

    /* ── Cards → B&W borders with light gray bg hint ── */
    .rounded-lg { border-radius: 3px !important; border: 1px solid #bbb !important; }
    .border-2 { border-width: 1px !important; }

    /* ── Summary cards (room status, cash flow) ── */
    .border-2.border-blue-400,
    .border-2.border-red-400,
    .border-2.border-green-400,
    .border-2.border-orange-400,
    .border.border-red-200,
    .border.border-orange-200,
    .border.border-blue-200 { border-color: #999 !important; }

    /* ── Section heading underlines ── */
    .border-b-2 { border-bottom-width: 1px !important; }
    .border-b-2.border-green-600,
    .border-b-2.border-blue-600,
    .border-b-2.border-purple-600,
    .border-b-2.border-red-800,
    .border-b-2.border-gray-800,
    .border-b-2.border-yellow-700 { border-color: #666 !important; }

    /* ── Text colors → black ── */
    .text-blue-700, .text-red-700, .text-green-700, .text-orange-700,
    .text-purple-700, .text-yellow-700, .text-green-800, .text-red-800,
    .text-blue-800, .text-orange-600, .text-blue-600, .text-green-600,
    .text-red-600, .text-gray-500, .text-gray-600, .text-gray-700,
    .text-gray-400, .text-indigo-800 { color: #000 !important; }

    /* ── Tables ── */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 8.5pt !important;
        margin-bottom: 6px !important;
    }
    th, td {
        padding: 3px 5px !important;
        border: 1px solid #ccc !important;
        text-align: left !important;
        vertical-align: middle !important;
    }
    th { background: #f5f5f5 !important; font-weight: 700 !important; }
    tfoot th, tfoot td { background: #f0f0f0 !important; }

    /* ── Typography ── */
    h1 { font-size: 16pt !important; margin: 4px 0 !important; }
    h2 { font-size: 11pt !important; margin: 4px 0 !important; }
    h3 { font-size: 10pt !important; margin: 3px 0 !important; }
    .text-4xl { font-size: 18pt !important; }
    .text-3xl { font-size: 14pt !important; }
    .text-2xl { font-size: 16pt !important; }
    .text-xl { font-size: 12pt !important; }
    .text-lg { font-size: 10pt !important; }
    .text-sm { font-size: 8.5pt !important; }
    .text-xs { font-size: 7.5pt !important; }
    .uppercase { text-transform: uppercase !important; }
    .font-bold { font-weight: 700 !important; }
    .italic { font-style: italic !important; }

    /* ── Separators ── */
    hr { border: none !important; border-top: 1px solid #666 !important; margin: 10px 0 !important; }
    .border-t-2 { border-top-width: 1px !important; }

    /* ── Status badges (B&W) ── */
    .px-2\.py-1.rounded.text-xs.font-bold,
    .px-1\.py-0\.5.rounded.text-xs.font-bold {
        display: inline-block !important;
        padding: 1px 4px !important;
        border-radius: 2px !important;
        border: 1px solid #999 !important;
        background: #f5f5f5 !important;
    }

    /* ── Occupancy bar ── */
    .bg-gray-200 { background: #eee !important; }
    .bg-blue-600 { background: #666 !important; }

    /* ── Page break rules ── */
    .mb-6 { page-break-inside: avoid; }
    h2 { page-break-after: avoid; }
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }
    thead { display: table-header-group; }

    /* ── Sign-off signature lines ── */
    .border-b.border-gray-400 {
        border-bottom: 1px solid #666 !important;
        margin-bottom: 60px !important;
        min-height: 40px !important;
    }

    /* ── Print-only element ── */
    .print-only { display: inline !important; }
}

/* Hide print-only on screen */
.print-only { display: none; }
</style>
