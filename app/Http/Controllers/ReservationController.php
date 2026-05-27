<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\MHSLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['guest', 'room', 'createdBy']);

        // Pencarian
        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reservation_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($q) use ($search) {
                      $q->where('guest_name', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('room', function ($q) use ($search) {
                      $q->where('room_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter status
        $status = $request->get('status');
        if ($status) {
            $query->where('status', $status);
        }

        // Filter tanggal
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        if ($dateFrom) {
            $query->whereDate('check_in', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('check_out', '<=', $dateTo);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistik ringkasan
        $stats = [
            'pending'     => Reservation::where('status', 'pending')->count(),
            'checked_in'  => Reservation::where('status', 'checked_in')->count(),
            'checked_out' => Reservation::where('status', 'checked_out')->count(),
            'cancelled'   => Reservation::where('status', 'cancelled')->count(),
        ];

        return view('reservations.index', compact('reservations', 'search', 'status', 'dateFrom', 'dateTo', 'stats'));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'createdBy']);
        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('reservations.show', compact('reservation', 'transactions'));
    }

    /**
     * Tambah pembayaran (DP / Pelunasan / Multi Payment)
     */
    public function addPayment(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'cancelled' || $reservation->status === 'checked_out') {
            return back()->with('error', 'Tidak bisa menambah pembayaran untuk reservasi ini.');
        }

        $validated = $request->validate([
            'payment_type' => 'required|in:dp,pelunasan,tambahan',
            'payment_method' => 'required|in:' . PaymentMethod::where('is_active', true)->pluck('slug')->implode(','),
            'amount' => 'required|numeric|min:0',
        ]);

        $sisaBayar = $reservation->total_amount - $reservation->paid_amount;

        if ($validated['amount'] > $sisaBayar) {
            return back()->with('error', 'Nominal pembayaran melebihi sisa bayar (Rp ' . number_format($sisaBayar, 0, ',', '.') . ')');
        }

        // Buat transaksi
        Transaction::create([
            'transaction_number' => 'TRX-' . strtoupper(uniqid()),
            'reservation_id' => $reservation->id,
            'type' => $validated['payment_type'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'created_by' => auth()->id(),
        ]);

        // Update paid_amount di reservasi
        $reservation->paid_amount += $validated['amount'];
        $reservation->save();

        $typeLabel = $validated['payment_type'] === 'dp' ? 'DP' : ($validated['payment_type'] === 'pelunasan' ? 'Pelunasan' : 'Pembayaran tambahan');

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$typeLabel} sebesar Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil ditambahkan.",
                'redirect_url' => route('reservations.show', $reservation),
                'reservation' => $reservation
            ]);
        }

        return redirect()->route('reservations.show', $reservation)
            ->with('success', "{$typeLabel} sebesar Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil ditambahkan.");
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->status === 'checked_in') {
            return back()->with('error', 'Reservasi yang sudah check-in tidak bisa dibatalkan.');
        }

        $reservation->update(['status' => 'cancelled']);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Reservasi {$reservation->reservation_number} berhasil dibatalkan.",
                'redirect_url' => route('reservations.index'),
                'reservation' => $reservation
            ]);
        }

        return redirect()->route('reservations.index')->with('success', "Reservasi {$reservation->reservation_number} berhasil dibatalkan.");
    }

    public function checkin(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Hanya reservasi dengan status pending yang bisa di-check-in.');
        }

        $reservation->update(['status' => 'checked_in']);
        $reservation->room->update(['status' => 'occupied']);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Check-in berhasil untuk kamar {$reservation->room->room_number}.",
                'redirect_url' => route('reservations.show', $reservation),
                'reservation' => $reservation
            ]);
        }

        return redirect()->route('reservations.show', $reservation)->with('success', "Check-in berhasil untuk kamar {$reservation->room->room_number}.");
    }

    public function checkout(Reservation $reservation)
    {
        if ($reservation->status !== 'checked_in') {
            return back()->with('error', 'Hanya reservasi yang sudah check-in yang bisa di-check-out.');
        }

        // Set check-out ke jam 12:00 siang hari ini (standard hotel time)
        $checkoutTime = Carbon::today()->setTime(12, 0, 0);

        $reservation->update([
            'status' => 'checked_out',
            'check_out' => $checkoutTime,
        ]);
        $reservation->room->update(['status' => 'available']);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Check-out berhasil untuk kamar {$reservation->room->room_number}. Status kamar: Available.",
                'redirect_url' => route('checkout.index'),
                'reservation' => $reservation
            ]);
        }

        return redirect()->route('checkout.index')->with('success', "Check-out berhasil untuk kamar {$reservation->room->room_number}. Status kamar: Available.");
    }

    /**
     * Halaman daftar kamar untuk pindah kamar
     */
    public function roomChangeList()
    {
        $reservations = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->orderBy('check_out', 'asc')
            ->get();

        $availableRooms = Room::where('status', 'available')
            ->orderBy('room_number')
            ->get();

        return view('reservations.room-change-list', compact('reservations', 'availableRooms'));
    }

    /**
     * Halaman daftar kamar untuk checkout
     */
    public function checkoutList()
    {
        $reservations = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->orderBy('check_out', 'asc')
            ->get();

        return view('reservations.checkout-list', compact('reservations'));
    }

    /**
     * Checkout by room ID (from Room Rack)
     */
    public function checkoutByRoom(Room $room)
    {
        $reservation = Reservation::where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->first();

        if (!$reservation) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada reservasi aktif untuk kamar ini.'], 404);
            }
            return back()->with('error', 'Tidak ada reservasi aktif untuk kamar ini.');
        }

        return $this->checkout($reservation);
    }

    /**
     * Tampilkan form pindah kamar
     */
    public function showRoomChange(Reservation $reservation)
    {
        if ($reservation->status !== 'checked_in') {
            return back()->with('error', 'Pindah kamar hanya bisa dilakukan untuk reservasi yang sudah check-in.');
        }

        $reservation->load(['guest', 'room']);

        // Ambil kamar yang available untuk tanggal reservasi ini
        $checkIn = $reservation->check_in->format('Y-m-d H:i:s');
        $checkOut = $reservation->check_out->format('Y-m-d H:i:s');

        // Back-to-Booking: check-out di hari yang sama dengan check-in baru
        // TIDAK dianggap bentrok (check-out 12:00, check-in 14:00)
        $availableRooms = Room::where('status', 'available')
            ->where('id', '!=', $reservation->room_id)
            ->where(function ($query) use ($checkIn, $checkOut, $reservation) {
                $query->whereDoesntHave('reservations', function ($q) use ($checkIn, $checkOut, $reservation) {
                    $q->where(function ($sq) use ($checkIn, $checkOut) {
                        $sq->where('check_in', '<', $checkOut)
                           ->where('check_out', '>', $checkIn);
                    })
                    ->whereIn('status', ['pending', 'checked_in'])
                    ->where('id', '!=', $reservation->id);
                });
            })
            ->orderBy('room_number')
            ->get();

        return view('reservations.room-change', compact('reservation', 'availableRooms'));
    }

    /**
     * Proses pindah kamar
     */
    public function changeRoom(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== 'checked_in') {
            return back()->with('error', 'Pindah kamar hanya bisa dilakukan untuk reservasi yang sudah check-in.');
        }

        $validated = $request->validate([
            'new_room_id' => 'required|exists:rooms,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $newRoom = Room::findOrFail($validated['new_room_id']);

        // Pastikan kamar baru tersedia untuk tanggal reservasi ini
        $checkIn = $reservation->check_in->format('Y-m-d H:i:s');
        $checkOut = $reservation->check_out->format('Y-m-d H:i:s');

        $isAvailable = $newRoom->isAvailable($checkIn, $checkOut, $reservation->id);
        if (!$isAvailable) {
            return back()->with('error', "Kamar {$newRoom->room_number} tidak tersedia untuk periode tanggal tersebut.");
        }

        if ($newRoom->status !== 'available') {
            return back()->with('error', "Kamar {$newRoom->room_number} tidak dalam status available.");
        }

        $oldRoom = $reservation->room;
        $oldRoomNumber = $oldRoom->room_number;
        $newRoomNumber = $newRoom->room_number;

        // Simpan room_type_name baru jika berbeda tipe
        $newRoomTypeName = $newRoom->room_type_name;

        // Hitung ulang total_amount menggunakan harga weekday/weekend dinamis
        $newTotalAmount = $newRoom->calculateTotalForRange($reservation->check_in, $reservation->check_out);

        // Update reservasi
        $reservation->room_id = $newRoom->id;
        $reservation->room_type_name = $newRoomTypeName;
        $reservation->total_amount = $newTotalAmount;
        if ($validated['reason']) {
            $reservation->notes = ($reservation->notes ? $reservation->notes . "\n" : '') . '[' . now()->format('d/m/Y H:i') . '] Pindah kamar dari ' . $oldRoomNumber . ' ke ' . $newRoomNumber . ': ' . $validated['reason'];
        }
        $reservation->save();

        // Update status kamar lama menjadi available
        $oldRoom->update(['status' => 'cleaning']);

        // Update status kamar baru menjadi occupied
        $newRoom->update(['status' => 'occupied']);

        // Log aktivitas
        MHSLog::create([
            'command' => 'room_change',
            'reservation_id' => $reservation->id,
            'request_data' => [
                'old_room_id' => $oldRoom->id,
                'old_room_number' => $oldRoomNumber,
                'new_room_id' => $newRoom->id,
                'new_room_number' => $newRoomNumber,
                'reason' => $validated['reason'] ?? null,
                'old_total_amount' => $oldPricePerNight * $nights,
                'new_total_amount' => $newTotalAmount,
            ],
            'response_data' => [
                'success' => true,
                'message' => "Pindah kamar dari {$oldRoomNumber} ke {$newRoomNumber} berhasil.",
            ],
            'success' => true,
        ]);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pindah kamar dari {$oldRoomNumber} ke {$newRoomNumber} berhasil.",
                'redirect_url' => route('reservations.show', $reservation),
                'reservation' => $reservation
            ]);
        }

        return redirect()->route('reservations.show', $reservation)
            ->with('success', "Pindah kamar dari {$oldRoomNumber} ke {$newRoomNumber} berhasil.");
    }

    /**
     * Print Kwitansi
     */
    public function printKwitansi(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'createdBy']);
        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('reservations.print-kwitansi', compact('reservation', 'transactions'));
    }

    /**
     * Print Invoice
     */
    public function printInvoice(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'createdBy']);
        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('reservations.print-invoice', compact('reservation', 'transactions'));
    }
}
