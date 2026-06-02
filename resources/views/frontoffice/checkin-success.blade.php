@extends('layouts.app')

@section('title', 'Check-in Success')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto text-center">
    <div class="text-green-500 mb-4">
        <i class="fas fa-check-circle text-6xl"></i>
    </div>
    <h2 class="text-2xl font-bold mb-4">Check-in Berhasil!</h2>
    <p class="text-gray-600 mb-6">Kartu akses telah di-issue.</p>
    
    <div class="bg-gray-100 p-4 rounded-lg text-left mb-6">
        <p><strong>No. Reservasi:</strong> {{ $reservation->reservation_number }}</p>
        <p><strong>Guest:</strong> {{ $reservation->guest->guest_name }}</p>
        <p><strong>Kamar:</strong> {{ $reservation->room->room_number }}</p>
        <p><strong>Check-in:</strong> {{ $reservation->check_in->format('d/m/Y H:i') }}</p>
        <p><strong>Check-out:</strong> {{ $reservation->check_out->format('d/m/Y H:i') }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($reservation->total_amount,0,',','.') }}</p>
    </div>
    
    <a href="{{ route('checkin.index') }}" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
        <i class="fas fa-plus"></i> Check-in Lagi
    </a>
    <a href="{{ route('frontoffice.dashboard') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 ml-2">
        <i class="fas fa-home"></i> Dashboard
    </a>
</div>
@endsection