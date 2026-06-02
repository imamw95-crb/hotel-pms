{{-- Deposit Show Modal Content — no layout, pure HTML for AJAX modal --}}
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold"><i class="fas fa-credit-card text-blue-500 mr-2"></i>Detail Deposit</h2>
        <div class="flex gap-2">
            <a href="{{ route('deposits.show', $deposit) }}" target="_blank"
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition flex items-center gap-2 text-sm">
                <i class="fas fa-print"></i> Print Tanda Terima
            </a>
            <button onclick="Modal.close()" class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>

    {{-- Info Deposit --}}
    <div class="bg-white border rounded-lg overflow-hidden">
        {{-- Header --}}
        <div class="bg-gray-800 text-white px-4 py-3 flex justify-between items-center">
            <div>
                <span class="font-bold text-lg">{{ $deposit->receipt_number }}</span>
            </div>
            <div>
                @if($deposit->status === 'returned')
                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                        <i class="fas fa-check-circle mr-1"></i> Dikembalikan
                    </span>
                @else
                    <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                        <i class="fas fa-clock mr-1"></i> Aktif
                    </span>
                @endif
            </div>
        </div>

        {{-- Detail Grid --}}
        <div class="p-4">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Informasi Tamu</h4>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="text-gray-500 py-1 w-28">Nama</td>
                            <td class="font-semibold">: {{ $deposit->guest->guest_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1">No. Identitas</td>
                            <td class="font-semibold">: {{ $deposit->guest->id_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1">Telepon</td>
                            <td class="font-semibold">: {{ $deposit->guest->phone ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Informasi Deposit</h4>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="text-gray-500 py-1 w-28">Tanggal</td>
                            <td class="font-semibold">: {{ $deposit->created_at->format('d F Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1">Metode Bayar</td>
                            <td class="font-semibold">: {{ ucwords(str_replace('_', ' ', $deposit->payment_method)) }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1">Petugas</td>
                            <td class="font-semibold">: {{ $deposit->createdBy->name ?? '-' }}</td>
                        </tr>
                        @if($deposit->reservation)
                        <tr>
                            <td class="text-gray-500 py-1">Reservasi</td>
                            <td class="font-semibold">: {{ $deposit->reservation->reservation_number }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Details Table --}}
            <div class="mt-6 border-t pt-4">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="text-left p-2 font-semibold">Keterangan</th>
                            <th class="text-center p-2 font-semibold w-20">Jumlah</th>
                            <th class="text-right p-2 font-semibold w-32">Nominal</th>
                            <th class="text-right p-2 font-semibold w-32">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b">
                            <td class="p-2">Deposit Kartu (Rp {{ number_format($deposit->nominal_per_card, 0, ',', '.') }} × {{ $deposit->number_of_cards }} kartu)</td>
                            <td class="p-2 text-center">{{ $deposit->number_of_cards }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($deposit->nominal_per_card, 0, ',', '.') }}</td>
                            <td class="p-2 text-right font-bold">Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="p-2 text-right font-bold text-base">TOTAL</td>
                            <td class="p-2 text-right font-bold text-base text-blue-700">Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Notes --}}
            @if($deposit->notes)
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                <span class="font-semibold text-sm text-yellow-800">Catatan:</span>
                <p class="text-sm text-yellow-700 mt-1">{{ $deposit->notes }}</p>
            </div>
            @endif

            {{-- Return Info --}}
            @if($deposit->status === 'returned')
            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded">
                <span class="font-semibold text-sm text-green-800"><i class="fas fa-check-circle mr-1"></i> Deposit sudah dikembalikan</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-4 flex justify-between">
        <button onclick="Modal.close()" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2 transition">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </button>
        <div class="flex gap-2">
            <a href="{{ route('deposits.show', $deposit) }}" target="_blank"
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg transition flex items-center gap-2 text-sm">
                <i class="fas fa-print"></i> Cetak Tanda Terima
            </a>
            @if($deposit->status === 'active')
                <button type="button" onclick="Deposit.returnDepositFromDetail({{ $deposit->id }})"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded-lg transition flex items-center gap-2 text-sm">
                    <i class="fas fa-undo"></i> Tandai Dikembalikan
                </button>
            @endif
        </div>
    </div>
</div>
