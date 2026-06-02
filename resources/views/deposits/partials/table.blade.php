<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-800 text-white">
                <th class="text-left p-3 font-semibold">No. Tanda Terima</th>
                <th class="text-left p-3 font-semibold">Tanggal</th>
                <th class="text-left p-3 font-semibold">Tamu</th>
                <th class="text-center p-3 font-semibold">Jumlah Kartu</th>
                <th class="text-right p-3 font-semibold">Total</th>
                <th class="text-center p-3 font-semibold">Metode</th>
                <th class="text-center p-3 font-semibold">Status</th>
                <th class="text-center p-3 font-semibold w-32">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deposits as $deposit)
            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                <td class="p-3 font-mono font-bold text-blue-600">{{ $deposit->receipt_number }}</td>
                <td class="p-3 text-gray-600">{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                <td class="p-3">
                    <div class="font-medium">{{ $deposit->guest->guest_name ?? '-' }}</div>
                    @if($deposit->reservation)
                        <div class="text-xs text-gray-400">{{ $deposit->reservation->reservation_number }}</div>
                    @endif
                </td>
                <td class="p-3 text-center">{{ $deposit->number_of_cards }}</td>
                <td class="p-3 text-right font-bold">Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}</td>
                <td class="p-3 text-center">
                    <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-medium">
                        {{ ucwords(str_replace('_', ' ', $deposit->payment_method)) }}
                    </span>
                </td>
                <td class="p-3 text-center">
                    @if($deposit->status === 'returned')
                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium">
                            <i class="fas fa-check-circle mr-1"></i> Dikembalikan
                        </span>
                    @else
                        <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-medium">
                            <i class="fas fa-clock mr-1"></i> Aktif
                        </span>
                    @endif
                </td>
                <td class="p-3 text-center">
                    <div class="flex items-center justify-center gap-3">
                        <a href="javascript:void(0)" onclick="Deposit.openShowModal({{ $deposit->id }})"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                           title="Lihat / Print">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($deposit->status === 'active')
                            <button type="button" onclick="Deposit.returnDeposit({{ $deposit->id }})"
                                    class="text-green-600 hover:text-green-800 text-sm font-medium" title="Sudah Dikembalikan">
                                <i class="fas fa-undo"></i>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="p-8 text-center text-gray-400">
                    <i class="fas fa-inbox text-3xl mb-2 block"></i>
                    Belum ada data deposit.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($deposits->hasPages())
<div class="mt-4">
    {{ $deposits->links() }}
</div>
@endif
