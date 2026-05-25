@extends('layouts.app')

@section('title', 'Database Backup')
@section('header', 'Database Backup')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Database Backup</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola backup database hotel_pms</p>
        </div>
        <form action="{{ route('admin.backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-database mr-2"></i> Buat Backup
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nama File</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Ukuran</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Tanggal</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($backups as $backup)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                            <i class="fas fa-file-archive text-blue-500 mr-2"></i>
                            {{ $backup['name'] }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $backup['size'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $backup['date'] }}</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.backups.download', $backup['name']) }}"
                                   class="text-blue-600 hover:text-blue-800" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('admin.backups.restore', $backup['name']) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Yakin restore database? Data saat ini akan ditimpa!')">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800" title="Restore">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.backups.destroy', $backup['name']) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Yakin hapus backup ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Belum ada backup. Klik "Buat Backup" untuk memulai.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
