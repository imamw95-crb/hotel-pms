@extends('layouts.app')

@section('title', 'Detail Reservasi')
@section('header', 'Detail Reservasi')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-blue-600">{{ $reservation->reservation_number }}</h2>
                <p class="text-gray-500 mt-1">Dibuat: {{ $reservation->created_at->format('d/m/Y H:i') }} oleh {{ $reservation->createdBy->name ?? '-' }}</p>
            </div>
            <div>
                @if($reservation->status === 'pending')
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded font-bold">PENDING</span>
                @elseif($reservation->status === 'checked_in')
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded font-bold">CHECKED IN</span>
                @elseif($reservation->status === 'checked_out')
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded font-bold">CHECKED OUT</span>
                @elseif($reservation->status === 'cancelled')
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded font-bold">CANCELLED</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Info Tamu -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-lg mb-4 border-b pb-2"><i class="fas fa-user text-blue-500 mr-2"></i>Info Tamu</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-gray-500 text-sm">Nama</span>
                    <p class="font-medium">{{ $reservation->guest->guest_name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">No. Identitas</span>
                    <p class="font-medium">{{ $reservation->guest->id_number ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">Telepon</span>
                    <p class="font-medium">{{ $reservation->guest->phone ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">Email</span>
                    <p class="font-medium">{{ $reservation->guest->email ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Info Kamar -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-lg mb-4 border-b pb-2"><i class="fas fa-bed text-green-500 mr-2"></i>Info Kamar</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-gray-500 text-sm">No. Kamar</span>
                    <p class="font-medium text-xl">{{ $reservation->room->room_number ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">Tipe Kamar</span>
                    <p class="font-medium">{{ $reservation->room->room_type_name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">Check-in</span>
                    <p class="font-medium">{{ $reservation->check_in->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">Check-out</span>
                    <p class="font-medium">{{ $reservation->check_out->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Pembayaran -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="font-bold text-lg mb-4 border-b pb-2"><i class="fas fa-money-bill text-yellow-500 mr-2"></i>Info Pembayaran</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Total Tagihan</span>
                <p class="text-xl font-bold">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Sudah Dibayar</span>
                <p class="text-xl font-bold text-green-600">Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-red-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Sisa Bayar</span>
                <p class="text-xl font-bold text-red-600">Rp {{ number_format($reservation->total_amount - $reservation->paid_amount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-blue-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Metode Bayar</span>
                <p class="text-xl font-bold capitalize">{{ str_replace('_', ' ', $reservation->payment_method ?? '-') }}</p>
            </div>
        </div>
    </div>

    <!-- Catatan -->
    @if($reservation->notes)
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="font-bold text-lg mb-2"><i class="fas fa-sticky-note text-purple-500 mr-2"></i>Catatan</h3>
        <p class="text-gray-700">{{ $reservation->notes }}</p>
    </div>
    @endif

    <!-- Tombol Aksi -->
    <div class="flex justify-between items-center mt-6">
        <a href="{{ route('reservations.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <div class="flex space-x-2">
            @if($reservation->status === 'pending')
                <form action="{{ route('reservations.checkin', $reservation) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-sign-in-alt mr-1"></i> Check-in
                    </button>
                </form>
                <form action="{{ route('reservations.cancel', $reservation) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" onclick="return confirm('Batalkan reservasi ini?')">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                </form>
            @endif
            @if($reservation->status === 'checked_in')
                <form action="{{ route('reservations.checkout', $reservation) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                        <i class="fas fa-sign-out-alt mr-1"></i> Check-out
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
