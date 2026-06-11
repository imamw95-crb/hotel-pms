{{-- Statistik Ringkasan --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Total</p>
            <p class="text-xl font-bold text-gray-800 mt-0.5" id="stat-total">{{ $reservations->total() }}</p>
        </div>
        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-calendar-alt text-blue-500"></i>
        </div>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Pending</p>
            <p class="text-xl font-bold text-yellow-600 mt-0.5" id="stat-pending">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-clock text-yellow-500"></i>
        </div>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Website</p>
            <p class="text-xl font-bold text-sky-600 mt-0.5" id="stat-website">{{ $stats['website'] ?? 0 }}</p>
        </div>
        <div class="w-10 h-10 bg-sky-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-globe text-sky-500"></i>
        </div>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">OTA</p>
            <p class="text-xl font-bold text-purple-600 mt-0.5" id="stat-ota">{{ $stats['ota'] ?? 0 }}</p>
        </div>
        <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-link text-purple-500"></i>
        </div>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Checked In</p>
            <p class="text-xl font-bold text-green-600 mt-0.5" id="stat-checked_in">{{ $stats['checked_in'] ?? 0 }}</p>
        </div>
        <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-door-open text-green-500"></i>
        </div>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Checked Out</p>
            <p class="text-xl font-bold text-blue-600 mt-0.5" id="stat-checked_out">{{ $stats['checked_out'] ?? 0 }}</p>
        </div>
        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-check-circle text-blue-500"></i>
        </div>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Cancelled</p>
            <p class="text-xl font-bold text-red-500 mt-0.5" id="stat-cancelled">{{ $stats['cancelled'] ?? 0 }}</p>
        </div>
        <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-ban text-red-400"></i>
        </div>
    </div>
</div>
