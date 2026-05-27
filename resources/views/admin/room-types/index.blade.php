@extends('layouts.app')

@section('title', 'Tipe Kamar')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Tipe Kamar</h2>
        <a href="{{ route('room-types.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded">
            <i class="fas fa-plus"></i> Tambah Tipe
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Kode</th>
                    <th class="text-left p-2">Nama Tipe</th>
                    <th class="text-left p-2">Urutan</th>
                    <th class="text-left p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomTypes as $type)
                <tr class="border-b">
                    <td class="p-2">{{ $type->code }}</td>
                    <td class="p-2">{{ $type->name }}</td>
                    <td class="p-2">{{ $type->sequence }}</td>
                    <td class="p-2">
                        <a href="{{ route('room-types.edit', $type) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('room-types.destroy', $type) }}" method="POST" class="inline" data-ajax="true">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline ml-2">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection