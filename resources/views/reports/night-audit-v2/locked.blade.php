@extends('layouts.app')

@section('title', 'Night Audit Locked - ' . $date)
@section('header', 'Night Audit Locked')

@section('content')
<div class="mb-6 no-print">
    <div class="bg-white rounded-lg shadow p-4 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
            <i class="fas fa-lock text-green-600 text-xl"></i>
            <span class="font-bold text-green-700">LOCKED REPORT</span>
            <span class="text-gray-500 mx-2">|</span>
            <span class="text-gray-700">{{ \Carbon\Carbon::parse($auditLog->audit_date)->format('d F Y') }}</span>
        </div>
        <div class="ml-auto flex gap-2">
            <button type="button" onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-print mr-1"></i> Print
            </button>
            <a href="{{ route('reports.night-audit-v2.export', $auditLog->id) }}" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">
                <i class="fas fa-file-csv mr-1"></i> Export CSV
            </a>
            <a href="{{ route('reports.night-audit-v2.index', ['date' => $date]) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

@include('reports.night-audit-v2.partials.report-content', $data)
@endsection
