<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\HousekeepingTask;
use App\Models\OutOfOrder;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Transaction;
use App\Services\OpenRouterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Filter sumber (website / ota / local)
        $sumber = $request->get('sumber');
        if ($sumber === 'website') {
            $query->where('ota_source', 'website');
        } elseif ($sumber === 'ota') {
            $query->whereNotNull('ota_source')
                ->where('ota_source', '!=', '')
                ->where('ota_source', '!=', 'website');
        } elseif ($sumber === 'local') {
            $query->where(function ($q) {
                $q->whereNull('ota_source')->orWhere('ota_source', '');
            });
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
            'pending' => Reservation::where('status', 'pending')->count(),
            'checked_in' => Reservation::where('status', 'checked_in')->count(),
            'checked_out' => Reservation::where('status', 'checked_out')->count(),
            'cancelled' => Reservation::where('status', 'cancelled')->count(),
            'website' => Reservation::where('ota_source', 'website')->count(),
            'ota' => Reservation::whereNotNull('ota_source')
                ->where('ota_source', '!=', '')
                ->where('ota_source', '!=', 'website')->count(),
        ];

        return view('reservations.index', compact('reservations', 'search', 'status', 'sumber', 'dateFrom', 'dateTo', 'stats'));
    }

    /**
     * Cek apakah ada reservasi baru sejak timestamp tertentu (auto-refresh)
     */
    public function checkNew(Request $request)
    {
        $since = $request->get('since');

        if (! $since) {
            return response()->json(['has_new' => false, 'count' => 0]);
        }

        try {
            $sinceTime = Carbon::parse($since);
        } catch (\Exception $e) {
            return response()->json(['has_new' => false, 'count' => 0]);
        }

        $newCount = Reservation::where('created_at', '>', $sinceTime)->count();

        return response()->json([
            'has_new' => $newCount > 0,
            'count' => $newCount,
        ]);
    }

    public function show(Reservation $reservation)
    {
        $reservation->load([
            'guest', 'room', 'createdBy',
            'serviceCharges', 'serviceCharges.createdBy',
            'restoTransactions', 'restoTransactions.createdBy',
        ]);
        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reservations.show', compact('reservation', 'transactions'));
    }

    /**
     * Input Pembayaran — 1 Form Universal
     * Handle: OTA paid, OTA partial, cash, DP, pelunasan — semua dari 1 input
     */
    public function addPayment(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'cancelled' || $reservation->status === 'checked_out') {
            return back()->with('error', 'Tidak bisa menambah pembayaran untuk reservasi ini.');
        }

        $validated = $request->validate([
            'payment_type' => 'required|in:dp,pelunasan,tambahan',
            'payment_method' => 'required|in:'.PaymentMethod::where('is_active', true)->pluck('slug')->implode(','),
            'amount' => 'required|numeric|min:0',
            'ota_payment_status' => 'nullable|in:paid_ota,partial_ota,unpaid_ota',
            'ota_paid_amount' => 'nullable|numeric|min:0',
        ]);

        $hotelAmount = $validated['amount'];
        $otaPaymentStatus = $validated['ota_payment_status'] ?? null;
        $otaPaidAmount = $validated['ota_paid_amount'] ?? 0;

        // Hitung sisa bayar (total - sudah dibayar sebelumnya)
        $sisaBayar = $reservation->total_amount - $reservation->paid_amount;

        // Validasi: total hotel payment + OTA payment tidak boleh melebihi sisa bayar
        $totalInput = $hotelAmount + $otaPaidAmount;
        if ($totalInput > $reservation->total_amount) {
            return back()->with('error', 'Total pembayaran (OTA + Hotel) melebihi total tagihan (Rp '.number_format($reservation->total_amount, 0, ',', '.').')');
        }

        DB::beginTransaction();
        try {
            // 1. Update OTA payment status di reservation
            if ($otaPaymentStatus) {
                $reservation->ota_payment_status = $otaPaymentStatus;
                $reservation->ota_paid_amount = $otaPaidAmount;
            }

            // 2. Buat transaction untuk OTA payment (jika ada)
            if ($otaPaidAmount > 0) {
                $otaTxnType = ($otaPaidAmount >= $reservation->total_amount) ? 'pelunasan' : 'dp';
                Transaction::create([
                    'transaction_number' => 'TRX-'.strtoupper(uniqid()),
                    'reservation_id' => $reservation->id,
                    'type' => $otaTxnType,
                    'amount' => $otaPaidAmount,
                    'payment_method' => $validated['payment_method'],
                    'notes' => 'OTA '.$validated['payment_method'].' — '.str_replace('_', ' ', $otaPaymentStatus),
                    'created_by' => auth()->id(),
                ]);
            }

            // 3. Buat transaction untuk hotel payment (jika ada)
            if ($hotelAmount > 0) {
                Transaction::create([
                    'transaction_number' => 'TRX-'.strtoupper(uniqid()),
                    'reservation_id' => $reservation->id,
                    'type' => $validated['payment_type'],
                    'amount' => $hotelAmount,
                    'payment_method' => $validated['payment_method'],
                    'created_by' => auth()->id(),
                ]);
            }

            // 4. Update paid_amount di reservasi (OTA + Hotel)
            $reservation->paid_amount += ($otaPaidAmount + $hotelAmount);

            // 5. Jika sudah lunas, update paid_date
            if ($reservation->paid_amount >= $reservation->total_amount) {
                $reservation->paid_date = now();
            }

            // 6. Update payment method
            $reservation->payment_method = $validated['payment_method'];

            $reservation->save();
            DB::commit();

            $typeLabel = $validated['payment_type'] === 'dp' ? 'DP' : ($validated['payment_type'] === 'pelunasan' ? 'Pelunasan' : 'Pembayaran tambahan');

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$typeLabel} sebesar Rp ".number_format($validated['amount'], 0, ',', '.').' berhasil ditambahkan.',
                    'redirect_url' => route('reservations.show', $reservation),
                    'reservation' => $reservation,
                ]);
            }

            return redirect()->route('reservations.show', $reservation)
                ->with('success', "{$typeLabel} sebesar Rp ".number_format($validated['amount'], 0, ',', '.').' berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan pembayaran: '.$e->getMessage());
        }
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
                'reservation' => $reservation,
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
                'reservation' => $reservation,
            ]);
        }

        return redirect()->route('reservations.show', $reservation)->with('success', "Check-in berhasil untuk kamar {$reservation->room->room_number}.");
    }

    /**
     * Toggle include_breakfast (AJAX)
     */
    public function toggleBreakfast(Reservation $reservation)
    {
        $reservation->update(['include_breakfast' => ! $reservation->include_breakfast]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'include_breakfast' => $reservation->include_breakfast,
                'message' => $reservation->include_breakfast
                    ? 'Sarapan telah diaktifkan'
                    : 'Sarapan telah dinonaktifkan',
            ]);
        }

        return back()->with('success', 'Status sarapan berhasil diubah.');
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

        // Auto-create housekeeping cleaning task
        try {
            $existing = HousekeepingTask::where('room_id', $reservation->room_id)
                ->where('task_type', 'cleaning')
                ->whereIn('status', ['pending', 'in_progress'])
                ->exists();

            if (! $existing) {
                HousekeepingTask::create([
                    'room_id' => $reservation->room_id,
                    'task_type' => 'cleaning',
                    'priority' => 'normal',
                    'description' => 'Auto-generated from check-out: '.($reservation->guest->guest_name ?? '').' ('.$reservation->reservation_number.')',
                    'status' => 'pending',
                    'created_by' => Auth::id(),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to auto-create housekeeping task on checkout: '.$e->getMessage());
        }

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Check-out berhasil untuk kamar {$reservation->room->room_number}. Tugas pembersihan telah dibuat.",
                'redirect_url' => route('checkout.index'),
                'reservation' => $reservation,
            ]);
        }

        return redirect()->route('checkout.index')->with('success', "Check-out berhasil untuk kamar {$reservation->room->room_number}. Tugas pembersihan telah dibuat.");
    }

    /**
     * Halaman daftar kamar untuk pindah kamar
     */
    public function roomChangeList(Request $request)
    {
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Reservation::with(['guest', 'room'])
            ->whereIn('status', ['pending', 'checked_in']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reservation_number', 'like', "%{$search}%")
                    ->orWhereHas('guest', function ($g) use ($search) {
                        $g->where('guest_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('room', function ($r) use ($search) {
                        $r->where('room_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($dateFrom) {
            $query->whereDate('check_in', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('check_in', '<=', $dateTo);
        }

        $reservations = $query->orderBy('check_out', 'asc')->get();

        $availableRooms = Room::where('status', 'available')
            ->orderBy('room_number')
            ->get();

        return view('reservations.room-change-list', compact('reservations', 'availableRooms', 'search', 'dateFrom', 'dateTo'));
    }

    /**
     * Halaman daftar kamar untuk checkout
     */
    public function checkoutList(Request $request)
    {
        $rooms = Room::orderBy('room_number')->get();

        $dateFrom = $request->input('date_from', Carbon::yesterday()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        $reservations = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('reservation_number', 'like', '%'.$search.'%')
                        ->orWhereHas('guest', function ($guestQuery) use ($search) {
                            $guestQuery->where('guest_name', 'like', '%'.$search.'%')
                                ->orWhere('id_number', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('room', function ($roomQuery) use ($search) {
                            $roomQuery->where('room_number', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($request->input('room_id'), function ($query, $roomId) {
                $query->where('room_id', $roomId);
            })
            ->whereDate('check_out', '>=', $dateFrom)
            ->whereDate('check_in', '<=', $dateTo)
            ->orderBy('check_out', 'asc')
            ->get();

        return view('reservations.checkout-list', compact('reservations', 'rooms', 'dateFrom', 'dateTo'));
    }

    /**
     * Checkout by room ID (from Room Rack)
     */
    public function checkoutByRoom(Room $room)
    {
        $reservation = Reservation::where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->first();

        if (! $reservation) {
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
        if (! in_array($reservation->status, ['pending', 'checked_in'])) {
            return back()->with('error', 'Pindah kamar hanya bisa dilakukan untuk reservasi dengan status pending atau check-in.');
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
            ->whereDoesntHave('outOfOrders', function ($q) use ($checkIn, $checkOut) {
                $q->where('status', OutOfOrder::STATUS_ACTIVE)
                    ->where('start_date', '<=', $checkOut)
                    ->where(function ($sq) use ($checkIn) {
                        $sq->whereNull('end_date')
                            ->orWhere('end_date', '>=', $checkIn);
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
        if (! in_array($reservation->status, ['pending', 'checked_in'])) {
            return back()->with('error', 'Pindah kamar hanya bisa dilakukan untuk reservasi dengan status pending atau check-in.');
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
        if (! $isAvailable) {
            return back()->with('error', "Kamar {$newRoom->room_number} tidak tersedia untuk periode tanggal tersebut.");
        }

        if ($newRoom->status !== 'available') {
            return back()->with('error', "Kamar {$newRoom->room_number} tidak dalam status available.");
        }

        $oldRoom = $reservation->room;
        $oldRoomNumber = $oldRoom->room_number;
        $newRoomNumber = $newRoom->room_number;

        // Simpan total lama sebelum dihitung ulang
        $oldTotalAmount = $reservation->total_amount;

        // Hitung ulang total_amount menggunakan harga weekday/weekend dinamis
        $newTotalAmount = $newRoom->calculateTotalForRange($reservation->check_in, $reservation->check_out);

        // Update reservasi
        $reservation->room_id = $newRoom->id;
        $reservation->total_amount = $newTotalAmount;
        if ($validated['reason']) {
            $reservation->notes = ($reservation->notes ? $reservation->notes."\n" : '').'['.now()->format('d/m/Y H:i').'] Pindah kamar dari '.$oldRoomNumber.' ke '.$newRoomNumber.': '.$validated['reason'];
        }
        $reservation->save();

        // Update status kamar berdasarkan status reservasi
        if ($reservation->status === 'checked_in') {
            // Jika sudah check-in: kamar lama perlu dibersihkan, kamar baru diisi
            $oldRoom->update(['status' => 'cleaning']);
            $newRoom->update(['status' => 'occupied']);
        } else {
            // Jika masih pending: kamar belum digunakan, cukup update reservasi saja
            $oldRoom->update(['status' => 'available']);
            // Kamar baru tetap available karena tamu belum check-in
        }

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pindah kamar dari {$oldRoomNumber} ke {$newRoomNumber} berhasil.",
                'redirect_url' => route('room-change.index'),
                'reservation' => $reservation,
            ]);
        }

        return redirect()->route('room-change.index')
            ->with('success', "Pindah kamar dari {$oldRoomNumber} ke {$newRoomNumber} berhasil.");
    }

    /**
     * AI Auto-Reservation — Create reservation from natural language input.
     * AI parses the input, finds available room, and creates the reservation.
     *
     * Flow: INPUT → AI PARSE → VALIDATE → CHECK ROOM → CREATE RESERVATION → DONE
     */
    public function aiCreate(Request $request, OpenRouterService $openRouter)
    {
        $validated = $request->validate([
            'input' => 'required|string|min:5|max:1000',
        ]);

        $input = $validated['input'];

        // Step 1: AI Parsing
        $aiData = $openRouter->parseNaturalLanguage($input);

        if (! $aiData) {
            return response()->json([
                'success' => false,
                'message' => 'AI gagal memproses input. Coba lagi.',
            ], 422);
        }

        // Step 2: Validate required fields
        $errors = [];
        if (empty($aiData['guest_name'])) {
            $errors[] = 'Nama tamu tidak terdeteksi';
        }
        if (empty($aiData['checkin_date'])) {
            $errors[] = 'Tanggal check-in tidak terdeteksi';
        }
        if (empty($aiData['checkout_date'])) {
            $errors[] = 'Tanggal check-out tidak terdeteksi';
        }

        if (! empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak lengkap: '.implode(', ', $errors),
                'ai_data' => $aiData,
            ], 422);
        }

        // Step 3: Validate dates
        try {
            $checkIn = Carbon::parse($aiData['checkin_date'])->setTime(14, 0);
            $checkOut = Carbon::parse($aiData['checkout_date'])->setTime(12, 0);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal tidak valid',
                'ai_data' => $aiData,
            ], 422);
        }

        if ($checkIn->gte($checkOut)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal check-out harus setelah check-in',
                'ai_data' => $aiData,
            ], 422);
        }

        // Step 4: Find available room
        $roomId = null;
        $roomTypeName = $aiData['room_type'] ?? null;

        // Exclude rooms with active Out of Order for the requested period
        $oooRoomIds = OutOfOrder::where('status', OutOfOrder::STATUS_ACTIVE)
            ->where('start_date', '<=', $checkOut->format('Y-m-d'))
            ->where(function ($q) use ($checkIn) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $checkIn->format('Y-m-d'));
            })
            ->pluck('room_id')
            ->unique();

        if ($roomTypeName) {
            // Try to find by specific room type
            $availableRooms = Room::where('room_type_name', $roomTypeName)
                ->where('status', '!=', 'maintenance')
                ->whereNotIn('id', $oooRoomIds)
                ->whereNotIn('id', function ($q) use ($checkIn, $checkOut) {
                    $q->select('room_id')
                        ->from('reservations')
                        ->whereIn('status', ['pending', 'checked_in'])
                        ->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                })
                ->orderBy('room_number')
                ->get();

            if ($availableRooms->isNotEmpty()) {
                $roomId = $availableRooms->first()->id;
            }
        }

        // Fallback: any available room
        if (! $roomId) {
            $anyAvailable = Room::where('status', '!=', 'maintenance')
                ->whereNotIn('id', $oooRoomIds)
                ->whereNotIn('id', function ($q) use ($checkIn, $checkOut) {
                    $q->select('room_id')
                        ->from('reservations')
                        ->whereIn('status', ['pending', 'checked_in'])
                        ->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                })
                ->orderBy('room_number')
                ->first();

            if ($anyAvailable) {
                $roomId = $anyAvailable->id;
                $roomTypeName = $anyAvailable->room_type_name;
            }
        }

        // Step 5: Create reservation
        try {
            $reservation = DB::transaction(function () use ($aiData, $roomId, $checkIn, $checkOut) {
                // Find or create guest
                $guest = Guest::firstOrCreate(
                    ['guest_name' => $aiData['guest_name']],
                    [
                        'phone' => null,
                        'email' => null,
                        'address' => null,
                    ]
                );

                // Calculate total if not provided
                $totalAmount = $aiData['total_price'] ?? 0;
                if ($totalAmount <= 0 && $roomId) {
                    $room = Room::find($roomId);
                    if ($room) {
                        $totalAmount = $room->calculateTotalForRange($checkIn, $checkOut);
                    }
                }

                $reservation = Reservation::create([
                    'guest_id' => $guest->id,
                    'room_id' => $roomId,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'number_of_cards' => $aiData['guest_count'] ?? 1,
                    'total_amount' => $totalAmount,
                    'payment_method' => $aiData['payment_method'] ?: 'cash',
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'notes' => ($aiData['notes'] ? $aiData['notes'].' ' : '').'(AI Auto-Reservation)',
                    'ota_source' => 'ai_auto',
                    'created_by' => auth()->id() ?? 1,
                ]);

                return $reservation;
            });

            $reservation->load(['guest', 'room']);

            $roomInfo = $reservation->room
                ? "Kamar {$reservation->room->room_number} ({$reservation->room->room_type_name})"
                : 'Kamar belum ditentukan';

            return response()->json([
                'success' => true,
                'message' => "✅ Reservasi berhasil dibuat: {$reservation->reservation_number} untuk {$aiData['guest_name']} — {$roomInfo}",
                'reservation' => $reservation,
                'ai_data' => $aiData,
            ]);
        } catch (\Exception $e) {
            Log::error('AI reservation failed: '.$e->getMessage(), [
                'input' => $input,
                'ai_data' => $aiData,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat reservasi: '.$e->getMessage(),
                'ai_data' => $aiData,
            ], 500);
        }
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
     * Print Invoice — gabung kamar + service charge + resto dalam 1 invoice
     */
    public function printInvoice(Reservation $reservation)
    {
        $reservation->load([
            'guest', 'room', 'createdBy',
            'serviceCharges', 'serviceCharges.createdBy',
            'restoTransactions', 'restoTransactions.createdBy',
        ]);
        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalServiceCharge = $reservation->serviceCharges->sum('total_amount');
        $totalResto = $reservation->restoTransactions->sum('total_amount');
        $grandTotal = $reservation->total_amount + $totalServiceCharge + $totalResto;

        return view('reservations.print-invoice', compact(
            'reservation', 'transactions',
            'totalServiceCharge', 'totalResto', 'grandTotal'
        ));
    }

    /**
     * Print Registration Card
     */
    public function printRegistrationCard(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'createdBy']);

        return view('reservations.print-registration-card', compact('reservation'));
    }

    /**
     * Update total amount reservasi
     */
    public function updateTotal(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0',
        ]);

        $oldAmount = $reservation->total_amount;
        $reservation->total_amount = $validated['total_amount'];
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Total reservasi berhasil diperbarui dari Rp '.number_format($oldAmount, 0, ',', '.').' menjadi Rp '.number_format($validated['total_amount'], 0, ',', '.'),
            'reservation' => $reservation,
        ]);
    }

    public function updateRoomRate(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'checked_out' || $reservation->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mengubah harga kamar untuk reservasi yang sudah checkout / dibatalkan.',
            ], 422);
        }

        $validated = $request->validate([
            'custom_room_rate' => 'nullable|numeric|min:0',
        ]);

        $nights = $reservation->nights;

        if ($validated['custom_room_rate'] === null) {
            $reservation->custom_room_rate = null;
            $newTotal = ($reservation->room->price_per_night ?? 0) * $nights;
            $message = 'Harga kamar dikembalikan ke default. Total: Rp '.number_format($newTotal, 0, ',', '.');
        } else {
            $reservation->custom_room_rate = $validated['custom_room_rate'];
            $newTotal = $validated['custom_room_rate'] * $nights;
            $message = 'Harga kamar diperbarui menjadi Rp '.number_format($validated['custom_room_rate'], 0, ',', '.').'/malam. Total: Rp '.number_format($newTotal, 0, ',', '.');
        }

        $reservation->total_amount = $newTotal;
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => $message,
            'reservation' => $reservation,
        ]);
    }

    /**
     * Simpan / perbarui catatan reservasi
     */
    public function updateNotes(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:5000',
        ]);

        $reservation->notes = $validated['notes'];
        $reservation->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil disimpan.',
                'notes' => $reservation->notes,
            ]);
        }

        return back()->with('success', 'Catatan berhasil disimpan.');
    }

    /**
     * Refresh partial tabel + statistik via AJAX (tanpa reload halaman)
     */
    public function refreshTable(Request $request)
    {
        $query = Reservation::with(['guest', 'room', 'createdBy']);

        // Terapkan filter yang sama seperti index()
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

        $status = $request->get('status');
        if ($status) {
            $query->where('status', $status);
        }

        $sumber = $request->get('sumber');
        if ($sumber === 'website') {
            $query->where('ota_source', 'website');
        } elseif ($sumber === 'ota') {
            $query->whereNotNull('ota_source')
                ->where('ota_source', '!=', '')
                ->where('ota_source', '!=', 'website');
        } elseif ($sumber === 'local') {
            $query->where(function ($q) {
                $q->whereNull('ota_source')->orWhere('ota_source', '');
            });
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        if ($dateFrom) {
            $query->whereDate('check_in', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('check_out', '<=', $dateTo);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistik
        $stats = [
            'pending' => Reservation::where('status', 'pending')->count(),
            'checked_in' => Reservation::where('status', 'checked_in')->count(),
            'checked_out' => Reservation::where('status', 'checked_out')->count(),
            'cancelled' => Reservation::where('status', 'cancelled')->count(),
            'website' => Reservation::where('ota_source', 'website')->count(),
            'ota' => Reservation::whereNotNull('ota_source')
                ->where('ota_source', '!=', '')
                ->where('ota_source', '!=', 'website')->count(),
        ];

        $tableHtml = view('reservations.partials._table', compact('reservations'))->render();
        $statsHtml = view('reservations.partials._stats', compact('reservations', 'stats'))->render();
        $paginationHtml = view('reservations.partials._pagination', compact('reservations'))->render();

        return response()->json([
            'success' => true,
            'table_html' => $tableHtml,
            'stats_html' => $statsHtml,
            'pagination_html' => $paginationHtml,
            'total' => $reservations->total(),
        ]);
    }
}
