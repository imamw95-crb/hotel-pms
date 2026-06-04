<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Transaction;
use App\Services\MHSBridgeService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    protected $mhs;

    public function __construct(MHSBridgeService $mhs)
    {
        $this->mhs = $mhs;
    }

    public function index(Request $request)
    {
        $rooms = Room::orderBy('room_number')->get();

        // Default tanggal: kemarin sampai hari ini
        $dateFrom = $request->input('date_from', Carbon::yesterday()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        // Fetch pending and upcoming reservations for check-in
        $pendingReservations = Reservation::with(['guest', 'room'])
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
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'pending')
                    ->whereDate('check_in', '>=', $dateFrom)
                    ->whereDate('check_in', '<=', $dateTo);
            })
            ->orderBy('check_in')
            ->get();

        return view('frontoffice.checkin', compact('rooms', 'pendingReservations', 'dateFrom', 'dateTo'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'number_of_cards' => 'integer|min:1|max:5',
            'payment_amount' => 'nullable|numeric|min:0',
            'include_breakfast' => 'nullable|boolean',
        ]);

        $room = Room::findOrFail($request->room_id);

        if (! $room->isAvailable($request->check_in, $request->check_out)) {
            return back()->with('error', 'Kamar sudah dipesan untuk tanggal tersebut.');
        }

        // Standard hotel time: check-in jam 12:00 siang, check-out jam 12:00 siang
        $checkInDate = Carbon::parse($request->check_in)->setTime(12, 0, 0);
        $checkOutDate = Carbon::parse($request->check_out)->setTime(12, 0, 0);

        $checkIn = $checkInDate->format('YmdHi');
        $checkOut = $checkOutDate->format('YmdHi');

        $mhsResult = $this->mhs->checkin(
            $room->room_number,
            $request->guest_name,
            $checkIn,
            $checkOut
        );

        if (! $mhsResult['success']) {
            return back()->with('error', 'Gagal issue card: '.($mhsResult['response_message'] ?? 'Unknown error'));
        }

        $guest = Guest::updateOrCreate(
            ['id_number' => $request->id_number ?? null],
            [
                'guest_name' => $request->guest_name,
                'phone' => $request->phone ?? null,
                'email' => $request->email ?? null,
            ]
        );

        $days = $checkInDate->diffInDays($checkOutDate);
        $totalAmount = $room->calculateTotalForRange($checkInDate, $checkOutDate);

        $reservation = Reservation::create([
            'reservation_number' => 'RES-'.strtoupper(uniqid()),
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => $checkInDate,
            'check_out' => $checkOutDate,
            'number_of_cards' => $request->number_of_cards ?? 1,
            'include_breakfast' => $request->boolean('include_breakfast'),
            'status' => 'checked_in',
            'total_amount' => $totalAmount,
            'paid_amount' => $request->payment_amount ?? 0,
            'created_by' => auth()->id(),
        ]);

        if ($request->payment_amount > 0) {
            Transaction::create([
                'transaction_number' => 'TRX-'.strtoupper(uniqid()),
                'reservation_id' => $reservation->id,
                'type' => 'checkin_payment',
                'amount' => $request->payment_amount,
                'payment_method' => $request->payment_method ?? 'cash',
                'created_by' => auth()->id(),
            ]);
        }

        $room->update(['status' => 'occupied']);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil! Kartu sudah di-issue.',
                'redirect_url' => route('checkin.success', $reservation->id),
                'reservation' => $reservation,
            ]);
        }

        return redirect()->route('checkin.success', $reservation->id)
            ->with('success', 'Check-in berhasil! Kartu sudah di-issue.');
    }

    public function success($id)
    {
        $reservation = Reservation::with(['room', 'guest'])->findOrFail($id);

        return view('frontoffice.checkin-success', compact('reservation'));
    }
}
