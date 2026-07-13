<?php

namespace App\Http\Controllers;

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
        $recentLogs = MHSLog::with(['reservation.room', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('frontoffice.issue-card', compact('rooms', 'reservations', 'recentLogs'));
    }

    /**
     * Issue card via MHS — HANYA untuk reservasi yang sudah ada, TIDAK membuat booking baru.
     */
    public function issue(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'number_of_cards' => 'required|integer|min:1|max:2',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Ambil reservasi yang sudah ada — tidak membuat reservasi baru
        $reservation = Reservation::findOrFail($validated['reservation_id']);
        $reservationId = $reservation->id;
        $guest = $reservation->guest;

        // Update jumlah kartu
        $reservation->update([
            'number_of_cards' => $validated['number_of_cards'],
        ]);

        // Format tanggal untuk MHS: YmdHi (contoh: 202605241400)
        $checkIn = Carbon::parse($validated['check_in'])->format('YmdHi');
        $checkOut = Carbon::parse($validated['check_out'])->format('YmdHi');

        // Kirim perintah checkin ke MHS via API
        $mhsResult = $this->mhs->checkin(
            $room->room_number,
            $validated['guest_name'],
            $checkIn,
            $checkOut,
            $reservationId
        );

        if (! ($mhsResult['success'] ?? false)) {
            $errorMsg = 'Gagal issue card: '.($mhsResult['response_message'] ?? 'Unknown error');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'response_code' => $mhsResult['response_code'] ?? null,
                ], 422);
            }

            return back()->with('error', $errorMsg)->withInput();
        }

        // Update status kamar
        $room->update(['status' => 'occupied']);

        $message = "Issue card berhasil! Kamar {$room->room_number} - {$validated['guest_name']}";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => route('issue-card.index'),
            ]);
        }

        return redirect()->route('issue-card.index')
            ->with('success', $message);
    }

    /**
     * Re-issue card untuk reservasi yang sudah ada
     */
    public function reissue(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'number_of_cards' => 'required|integer|min:1|max:2',
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
            $checkOut,
            $reservation->id
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
                'message' => 'Gagal terhubung ke server MHS: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Erase card / cancel kartu fisik via encoder
     */
    public function eraseCard(Request $request, Reservation $reservation)
    {
        $room = $reservation->room;

        if (! $room) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data kamar tidak ditemukan.']);
            }

            return back()->with('error', 'Data kamar tidak ditemukan.');
        }

        $mhsResult = $this->mhs->eraseCard($room->room_number, $reservation->id);

        if (! ($mhsResult['success'] ?? false)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal erase card: '.($mhsResult['response_message'] ?? 'Unknown error')]);
            }

            return back()->with('error', 'Gagal erase card: '.($mhsResult['response_message'] ?? 'Unknown error'));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Erase card berhasil untuk kamar {$room->room_number}! Silakan tap kartu di encoder."]);
        }

        return back()->with('success', "Erase card berhasil untuk kamar {$room->room_number}! Silakan tap kartu di encoder.");
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
                'message' => 'Gagal membaca kartu: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Daftarkan encoder ke MHS
     */
    public function registerEncoder(Request $request)
    {
        $encoderIp = $request->input('ip', '192.168.88.2');
        $encoderId = $request->input('encoder_id', '01');

        $result = $this->mhs->registerEncoder($encoderIp, $encoderId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success'] ?? false) {
            return back()->with('success', 'Encoder berhasil didaftarkan!');
        }

        return back()->with('error', 'Gagal mendaftarkan encoder: '.($result['message'] ?? 'Unknown error'));
    }

    /**
     * Ambil daftar kamar dari database (lengkap dengan status & tamu aktif)
     */
    public function getMhsRooms()
    {
        try {
            $rooms = Room::with(['reservations' => function ($q) {
                $q->whereIn('status', ['checked_in', 'pending'])
                    ->with('guest')
                    ->orderBy('created_at', 'desc');
            }])->orderBy('room_number')->get();

            $data = $rooms->map(function ($room) {
                $activeReservation = $room->reservations->first();

                return [
                    'room_number' => $room->room_number,
                    'room_type' => $room->room_type_name ?? 'Standard',
                    'status' => $room->status,
                    'guest_name' => $activeReservation?->guest?->guest_name ?? null,
                    'check_in' => $activeReservation?->check_in?->format('Y-m-d H:i'),
                    'check_out' => $activeReservation?->check_out?->format('Y-m-d H:i'),
                    'reservation_status' => $activeReservation?->status ?? null,
                ];
            });

            $byFloor = $data->groupBy(function ($room) {
                return substr($room['room_number'], 0, 2);
            });

            return response()->json([
                'success' => true,
                'total_rooms' => $data->count(),
                'rooms' => $data,
                'by_floor' => $byFloor,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Gagal mengambil daftar kamar: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * AJAX search reservasi
     */
    public function searchReservations(Request $request)
    {
        $q = $request->input('q', '');

        $reservations = Reservation::with(['guest', 'room'])
            ->where(function ($query) use ($q) {
                $query->where('reservation_number', 'like', "%{$q}%")
                    ->orWhereHas('guest', function ($q2) use ($q) {
                        $q2->where('guest_name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('room', function ($q3) use ($q) {
                        $q3->where('room_number', 'like', "%{$q}%");
                    });
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $results = $reservations->map(function ($res) {
            return [
                'id' => $res->id,
                'reservation_number' => $res->reservation_number,
                'guest_name' => $res->guest->guest_name ?? '-',
                'room_number' => $res->room->room_number ?? '-',
                'room_type' => $res->room->roomType->name ?? $res->room->room_type_name ?? 'Standard',
                'room_id' => $res->room_id,
                'id_number' => $res->guest->id_number ?? '',
                'phone' => $res->guest->phone ?? '',
                'email' => $res->guest->email ?? '',
                'check_in' => $res->check_in->format('Y-m-d\\TH:i'),
                'check_out' => $res->check_out->format('Y-m-d\\TH:i'),
                'status' => $res->status,
                'number_of_cards' => $res->number_of_cards ?? 1,
            ];
        });

        return response()->json([
            'success' => true,
            'results' => $results,
            'total' => $results->count(),
        ]);
    }
}
