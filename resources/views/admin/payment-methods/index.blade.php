@extends('layouts.app')

@section('title', 'Metode Pembayaran')
@section('header', 'Master Metode Pembayaran')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-gray-500">Kelola metode pembayaran yang tersedia.</p>
        </div>
        <a href="{{ route('admin.payment-methods.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus"></i> Tambah Metode
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="text-left p-3 font-semibold">No</th>
                    <th class="text-left p-3 font-semibold">Nama Metode</th>
                    <th class="text-left p-3 font-semibold">Slug</th>
                    <th class="text-center p-3 font-semibold">Status</th>
                    <th class="text-center p-3 font-semibold w-32">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentMethods as $method)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="p-3">{{ $loop->iteration }}</td>
                    <td class="p-3 font-medium">{{ $method->name }}</td>
                    <td class="p-3 text-gray-500 font-mono text-xs">{{ $method->slug }}</td>
                    <td class="p-3 text-center">
                        @if($method->is_active)
                            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium">Aktif</span>
                        @else
                            <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs font-medium">Nonaktif</span>
                        @endif
                    </td>
                    <td class="p-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.payment-methods.edit', $method) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}" class="inline" data-ajax="true">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-400">
                        <i class="fas fa-inbox text-3xl mb-2 block"></i>
                        Belum ada metode pembayaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
