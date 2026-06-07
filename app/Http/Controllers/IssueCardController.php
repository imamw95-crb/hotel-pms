<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\MHSLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\MHSBridgeService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IssueCardController extends Controller
{
    protected $mhs;

    public function __construct(MHSBridgeService $mhs)
    {
        $this->mhs = $mhs;
    }

    /**
     * Tampilkan halaman issue card
     */
    public function index()
    {
        $rooms = Room::where('status', 'available')->orderBy('room_number')->get();
        $reservations = Reservation::with(['guest', 'room'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        $recentLogs = MHSLog::with('reservation.room')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('frontoffice.issue-card', compact('rooms', 'reservations', 'recentLogs'));
    }

    /**
     * Issue card baru via API HMS
     */
    public function issue(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'number_of_cards' => 'required|integer|min:1|max:5',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Format tanggal untuk MHS: YmdHi (contoh: 202605241400)
        $checkIn = Carbon::parse($validated['check_in'])->format('YmdHi');
        $checkOut = Carbon::parse($validated['check_out'])->format('YmdHi');

        // Kirim perintah checkin ke MHS via API
        $mhsResult = $this->mhs->checkin(
            $room->room_number,
            $validated['guest_name'],
            $checkIn,
            $checkOut
        );

        if (! ($mhsResult['success'] ?? false)) {
            return back()->with('error', 'Gagal issue card: '.($mhsResult['response_message'] ?? 'Unknown error'))
                ->withInput();
        }

        // Simpan data guest
        $guest = Guest::updateOrCreate(
            ['id_number' => $validated['id_number'] ?? null],
            [
                'guest_name' => $validated['guest_name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
            ]
        );

        // Standard hotel time: check-in jam 12:00 siang, check-out jam 12:00 siang
        $checkInDate = Carbon::parse($validated['check_in'])->setTime(12, 0, 0);
        $checkOutDate = Carbon::parse($validated['check_out'])->setTime(12, 0, 0);

        // Hitung total
        $days = $checkInDate->diffInDays($checkOutDate);
        $totalAmount = $room->price_per_night * $days;

        // Buat reservasi
        $reservation = Reservation::create([
            'reservation_number' => 'RES-'.strtoupper(uniqid()),
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => $checkInDate,
            'check_out' => $checkOutDate,
            'number_of_cards' => $validated['number_of_cards'],
            'status' => 'checked_in',
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'notes' => 'Issue Card via MHS',
            'created_by' => auth()->id(),
        ]);

        // Update status kamar
        $room->update(['status' => 'occupied']);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Issue card berhasil! Kamar {$room->room_number} - {$validated['guest_name']}",
                'redirect_url' => route('checkin.success', $reservation->id),
                'reservation' => $reservation,
            ]);
        }

        return redirect()->route('checkin.success', $reservation->id)
            ->with('success', "Issue card berhasil! Kamar {$room->room_number} - {$validated['guest_name']}");
    }

    /**
     * Re-issue card untuk reservasi yang sudah ada
     */
    public function reissue(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'number_of_cards' => 'required|integer|min:1|max:5',
        ]);

        $room = $reservation->room;
        $guest = $reservation->guest;

        if (! $room || ! $guest) {
            return back()->with('error', 'Data kamar atau tamu tidak ditemukan.');
        }

        $checkIn = Carbon::parse($reservation->check_in)->format('YmdHi');
        $checkOut = Carbon::parse($reservation->check_out)->format('YmdHi');

        $mhsResult = $this->mhs->checkin(
            $room->room_number,
            $guest->guest_name,
            $checkIn,
            $checkOut
        );

        if (! ($mhsResult['success'] ?? false)) {
            return back()->with('error', 'Gagal re-issue card: '.($mhsResult['response_message'] ?? 'Unknown error'));
        }

        $reservation->update([
            'number_of_cards' => $validated['number_of_cards'],
        ]);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Re-issue card berhasil untuk kamar {$room->room_number}!",
                'reservation' => $reservation,
            ]);
        }

        return back()->with('success', "Re-issue card berhasil untuk kamar {$room->room_number}!");
    }

    /**
     * Checkout / hapus kartu via API HMS
     */
    public function checkout(Request $request, Reservation $reservation)
    {
        $room = $reservation->room;

        if (! $room) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data kamar tidak ditemukan.']);
            }

            return back()->with('error', 'Data kamar tidak ditemukan.');
        }

        $mhsResult = $this->mhs->checkout($room->room_number, $reservation->id);

        if (! ($mhsResult['success'] ?? false)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal checkout: '.($mhsResult['response_message'] ?? 'Unknown error')]);
            }

            return back()->with('error', 'Gagal checkout: '.($mhsResult['response_message'] ?? 'Unknown error'));
        }

        // Set check-out ke jam 12:00 siang hari ini (standard hotel time)
        $checkoutTime = Carbon::today()->setTime(12, 0, 0);

        $reservation->update([
            'status' => 'checked_out',
            'check_out' => $checkoutTime,
        ]);
        $room->update(['status' => 'available']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Checkout berhasil untuk kamar {$room->room_number}!"]);
        }

        return back()->with('success', "Checkout berhasil untuk kamar {$room->room_number}!");
    }

    /**
     * Test koneksi ke MHS
     */
    public function testConnection()
    {
        try {
            $result = $this->mhs->testConnection();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'error' => $e->getMessage(),
                'message' => 'Gagal terhubung ke server MHS: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Baca data kartu via API HMS
     */
    public function readCard()
    {
        try {
            $result = $this->mhs->readCard();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Gagal membaca kartu: ' . $e->getMessage(),
            ]);
        }
    }
}
