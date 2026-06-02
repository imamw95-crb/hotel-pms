@extends('layouts.app')

@section('title', 'OTA Autopilot Test')

@section('header', '🧪 OTA Email Autopilot — Test Panel')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Test Email Input -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">📧 Test Email Parser</h2>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">OTA Source</label>
                <select id="otaSource" class="w-full border rounded px-3 py-2 text-sm">
                    <option value="tiket.com">tiket.com</option>
                    <option value="traveloka.com">traveloka.com</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Subject</label>
                <input type="text" id="emailSubject" value="Booking Confirmation - Tiket.com Reservation #TK-987654"
                    class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Body</label>
                <textarea id="emailBody" rows="12" class="w-full border rounded px-3 py-2 text-sm font-mono"
                    placeholder="Paste OTA email content here...">Dear Partner,

We are pleased to confirm the following reservation at your property.

═══════════════════════════════════════
BOOKING DETAILS
═══════════════════════════════════════

Reservation ID: TK-987654
Guest Name: Budi Santoso
Check-in Date: 2026-06-15
Check-out Date: 2026-06-17
Room Type: Deluxe Room
Number of Guests: 2
Booking Status: Confirmed
Source: Tiket.com

═══════════════════════════════════════
PAYMENT INFORMATION
═══════════════════════════════════════

Total Amount: Rp 1.500.000
Payment Method: Bank Transfer
Payment Status: Paid

Please ensure the room is ready for the guest's arrival.
Check-in time: 14:00
Check-out time: 12:00

Best regards,
Tiket.com Partner Team</textarea>
            </div>

            <div class="flex gap-2">
                <button onclick="testParse()" class="bg-blue-500 text-white px-6 py-2 rounded text-sm hover:bg-blue-600 transition">
                    🤖 Test AI Parse
                </button>
                <button onclick="loadSample('booking')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300">
                    📋 Sample: Booking
                </button>
                <button onclick="loadSample('cancel')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300">
                    ❌ Sample: Cancel
                </button>
                <button onclick="loadSample('modify')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300">
                    ✏️ Sample: Modify
                </button>
            </div>
        </div>
    </div>

    <!-- AI Result -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">🤖 AI Parsing Result</h2>
        <div id="resultArea">
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-robot text-4xl mb-3"></i>
                <p>Click "Test AI Parse" to see result</p>
            </div>
        </div>
    </div>

    <!-- OTA Notifications -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">🔔 OTA Notifications (Front Office)</h2>
            <button onclick="loadNotifications()" class="text-blue-500 text-sm hover:underline">Refresh</button>
        </div>
        <div id="notificationsArea">
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-bell text-4xl mb-3"></i>
                <p>No notifications yet</p>
            </div>
        </div>
    </div>

    <!-- Processed Emails -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">📊 Processed Emails Log</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">UID</th>
                        <th class="px-3 py-2 text-left">Sender</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">OTA</th>
                        <th class="px-3 py-2 text-left">Processed</th>
                    </tr>
                </thead>
                <tbody id="processedTable">
                    <tr><td colspan="6" class="px-3 py-4 text-center text-gray-400">No records</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
const SAMPLES = {
    booking: {
        subject: 'Booking Confirmation - Tiket.com Reservation #TK-987654',
        body: `Dear Partner,

We are pleased to confirm the following reservation at your property.

═══════════════════════════════════════
BOOKING DETAILS
═══════════════════════════════════════

Reservation ID: TK-987654
Guest Name: Budi Santoso
Check-in Date: 2026-06-15
Check-out Date: 2026-06-17
Room Type: Deluxe Room
Number of Guests: 2
Booking Status: Confirmed
Source: Tiket.com

═══════════════════════════════════════
PAYMENT INFORMATION
═══════════════════════════════════════

Total Amount: Rp 1.500.000
Payment Method: Bank Transfer
Payment Status: Paid

Please ensure the room is ready for the guest's arrival.
Check-in time: 14:00
Check-out time: 12:00

Best regards,
Tiket.com Partner Team`
    },
    cancel: {
        subject: 'Cancellation Notice - Traveloka Booking #TV-555123',
        body: `Dear Partner,

We regret to inform you that the following booking has been cancelled.

═══════════════════════════════════════
CANCELLATION DETAILS
═══════════════════════════════════════

Reservation ID: TV-555123
Guest Name: Siti Rahmawati
Check-in Date: 2026-06-20
Check-out Date: 2026-06-22
Room Type: Superior Room
Number of Guests: 1
Booking Status: Cancelled
Source: Traveloka

Cancellation Reason: Guest requested cancellation
Cancellation Date: 2026-05-28

The room is now available for new bookings.

Best regards,
Traveloka Hotel Team`
    },
    modify: {
        subject: 'Booking Modification - Updated Reservation #TK-111222',
        body: `Dear Partner,

Please note that the following reservation has been modified.

═══════════════════════════════════════
MODIFIED BOOKING DETAILS
═══════════════════════════════════════

Reservation ID: TK-111222
Guest Name: Ahmad Hidayat
Check-in Date: 2026-07-01
Check-out Date: 2026-07-05
Room Type: Suite Room
Number of Guests: 3
Booking Status: Modified
Source: Tiket.com

Changes:
- Check-in changed from 2026-06-28 to 2026-07-01
- Check-out changed from 2026-06-30 to 2026-07-05
- Room type changed from Deluxe to Suite

Please update your records accordingly.

Best regards,
Tiket.com Partner Team`
    }
};

function loadSample(type) {
    const s = SAMPLES[type];
    if (s) {
        document.getElementById('emailSubject').value = s.subject;
        document.getElementById('emailBody').value = s.body;
        const otaSelect = document.getElementById('otaSource');
        if (type === 'cancel') otaSelect.value = 'traveloka.com';
        else otaSelect.value = 'tiket.com';
    }
}

async function testParse() {
    const resultArea = document.getElementById('resultArea');
    resultArea.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i><p class="mt-2 text-gray-500">Parsing with AI...</p></div>';

    try {
        const res = await fetch('{{ url("dev/ota-test/parse") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                email_subject: document.getElementById('emailSubject').value,
                email_body: document.getElementById('emailBody').value,
                ota_source: document.getElementById('otaSource').value,
            }),
        });

        const data = await res.json();

        if (data.success && data.data) {
            const rows = Object.entries(data.data).map(([k, v]) =>
                `<tr><td class="px-3 py-2 font-medium text-gray-600">${k}</td><td class="px-3 py-2 font-mono text-sm">${v}</td></tr>`
            ).join('');

            resultArea.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded p-3 mb-4">
                    <p class="text-green-700 font-medium">✅ AI Parsing Successful</p>
                </div>
                <table class="min-w-full text-sm border rounded">
                    <tbody>${rows}</tbody>
                </table>`;
        } else {
            resultArea.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded p-3">
                    <p class="text-red-700 font-medium">❌ AI Parsing Failed</p>
                    <p class="text-red-600 text-sm mt-1">Check OpenRouter API key and internet connection</p>
                </div>`;
        }
    } catch (e) {
        resultArea.innerHTML = `<div class="bg-red-50 border border-red-200 rounded p-3"><p class="text-red-700">❌ Error: ${e.message}</p></div>`;
    }
}

async function loadNotifications() {
    const area = document.getElementById('notificationsArea');
    try {
        const res = await fetch('{{ url("dev/ota-test/notifications") }}');
        const data = await res.json();

        if (data.notifications && data.notifications.length > 0) {
            const items = data.notifications.map(n => `
                <div class="border rounded p-3 mb-2 ${n.action === 'cancelled' ? 'bg-red-50 border-red-200' : n.action === 'updated' ? 'bg-yellow-50 border-yellow-200' : 'bg-green-50 border-green-200'}">
                    <p class="font-medium text-sm">${n.message}</p>
                    <p class="text-xs text-gray-500 mt-1">${n.created_at}</p>
                </div>
            `).join('');
            area.innerHTML = items;
        } else {
            area.innerHTML = '<div class="text-center py-8 text-gray-400"><i class="fas fa-bell text-4xl mb-3"></i><p>No notifications yet</p></div>';
        }
    } catch (e) {
        area.innerHTML = `<p class="text-red-500">Error: ${e.message}</p>`;
    }
}
</script>
@endsection
