<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Default tanggal: hari ini
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        // Filter status kamar (all / available / occupied / due_out / cleaning / maintenance)
        $statusFilter = $request->input('status_filter', 'all');
        // Filter tipe kamar
        $roomTypeFilter = $request->input('room_type', 'all');

        // Untuk menghitung kamar tersedia, kita perlu memeriksa ketersediaan kamar
        // menggunakan logika yang sama dengan BookingController
        $availableRoomsCount = 0;
        $checkinsToday = 0;
        $checkoutsToday = 0;

        // Check-in/check-out jam 12:00 siang
        $checkinsToday = Reservation::whereDate('check_in', '>=', $dateFrom)
            ->whereDate('check_in', '<=', $dateTo)
            ->where('status', 'pending')
            ->count();
        $checkoutsToday = Reservation::whereDate('check_out', '>=', $dateFrom)
            ->whereDate('check_out', '<=', $dateTo)
            ->where('status', 'checked_in')
            ->count();

        // Due Out: kamar yang tamu-nya check-out HARI INI
        $dueOutRoomIds = Reservation::whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')
            ->pluck('room_id')
            ->toArray();
        $upcomingBookings = Reservation::whereDate('check_in', '>', Carbon::today())
            ->where('status', 'pending')
            ->orderBy('check_in', 'asc')
            ->limit(5)
            ->get();

        // Daftar tipe kamar untuk dropdown filter
        $roomTypes = Room::select('room_type_name')
            ->distinct()
            ->orderBy('room_type_name')
            ->pluck('room_type_name')
            ->filter()
            ->values();

        // Query kamar dengan filter
        $roomsQuery = Room::with(['roomType', 'reservations' => function ($q) use ($dateFrom, $dateTo) {
            $q->where(function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'checked_in')
                    ->orWhere(function ($sub) use ($dateFrom, $dateTo) {
                        $sub->where('status', 'pending')
                            ->whereDate('check_in', '>=', $dateFrom)
                            ->whereDate('check_in', '<=', $dateTo);
                    });
            });
        }, 'reservations.guest']);

        // Filter by room type
        if ($roomTypeFilter !== 'all') {
            $roomsQuery->where('room_type_name', $roomTypeFilter);
        }

        // Filter by status (due_out = occupied tapi check-out hari ini)
        if ($statusFilter !== 'all') {
            if ($statusFilter === 'due_out') {
                $roomsQuery->where('status', 'occupied')
                    ->whereIn('id', $dueOutRoomIds);
            } else {
                $roomsQuery->where('status', $statusFilter);
            }
        }

        $rooms = $roomsQuery->orderBy('room_number')->get();

        // Hitung kamar tersedia - optimasi: gunakan query batch bukan loop per kamar
        if ($statusFilter === 'available' || $statusFilter === 'all') {
            $bookedRoomIds = Reservation::whereIn('status', ['pending', 'checked_in'])
                ->where(function ($q) use ($dateFrom, $dateTo) {
                    $q->where('check_in', '<', $dateTo)
                        ->where('check_out', '>', $dateFrom);
                })
                ->pluck('room_id')
                ->unique()
                ->toArray();

            // Room IDs yang tidak tersedia karena status (out_of_order, maintenance)
            $unavailableStatusIds = Room::whereIn('status', ['out_of_order', 'maintenance'])
                ->pluck('id')
                ->toArray();

            $excludeIds = array_unique(array_merge($bookedRoomIds, $unavailableStatusIds));
            $availableRoomsCount = $rooms->whereNotIn('id', $excludeIds)->count();
        } else {
            $availableRoomsCount = $rooms->where('status', 'available')->count();
        }

        return view('rooms.dashboard', compact(
            'availableRoomsCount', 'checkinsToday', 'checkoutsToday',
            'dueOutRoomIds', 'upcomingBookings', 'rooms', 'dateFrom', 'dateTo',
            'statusFilter', 'roomTypeFilter', 'roomTypes'
        ));
    }

    public function apiRoomsStatus(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));
        $statusFilter = $request->input('status_filter', 'all');
        $roomTypeFilter = $request->input('room_type', 'all');

        // Due Out: kamar yang tamu-nya check-out HARI INI
        $dueOutRoomIds = Reservation::whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')
            ->pluck('room_id')
            ->toArray();

        $roomsQuery = Room::with(['roomType', 'reservations' => function ($q) use ($dateFrom, $dateTo) {
            $q->where('status', 'checked_in')
                ->orWhere(function ($sub) use ($dateFrom, $dateTo) {
                    $sub->where('status', 'pending')
                        ->whereDate('check_in', '>=', $dateFrom)
                        ->whereDate('check_in', '<=', $dateTo);
                });
        }, 'reservations.guest']);

        if ($roomTypeFilter !== 'all') {
            $roomsQuery->where('room_type_name', $roomTypeFilter);
        }

        if ($statusFilter === 'due_out') {
            $roomsQuery->where('status', 'occupied')
                ->whereIn('id', $dueOutRoomIds);
        } elseif ($statusFilter !== 'all') {
            $roomsQuery->where('status', $statusFilter);
        }

        $rooms = $roomsQuery->orderBy('room_number')->get();

        // Hitung kamar tersedia — optimasi batch query
        $availableCount = 0;
        if ($statusFilter === 'available' || $statusFilter === 'all') {
            $bookedRoomIds = Reservation::whereIn('status', ['pending', 'checked_in'])
                ->where(function ($q) use ($dateFrom, $dateTo) {
                    $q->where('check_in', '<', $dateTo)->where('check_out', '>', $dateFrom);
                })
                ->pluck('room_id')->unique()->toArray();

            // Room IDs yang tidak tersedia karena status (out_of_order, maintenance)
            $unavailableStatusIds = Room::whereIn('status', ['out_of_order', 'maintenance'])
                ->pluck('id')
                ->toArray();

            $excludeIds = array_unique(array_merge($bookedRoomIds, $unavailableStatusIds));
            $availableCount = $rooms->whereNotIn('id', $excludeIds)->count();
        } else {
            $availableCount = $rooms->where('status', 'available')->count();
        }

        return response()->json([
            'rooms' => $rooms,
            'available_count' => $availableCount,
            'checkins_today' => Reservation::whereDate('check_in', '>=', $dateFrom)->whereDate('check_in', '<=', $dateTo)->where('status', 'pending')->count(),
            'checkouts_today' => Reservation::whereDate('check_out', '>=', $dateFrom)->whereDate('check_out', '<=', $dateTo)->where('status', 'checked_in')->count(),
            'due_out_room_ids' => $dueOutRoomIds,
        ]);
    }

    /**
     * Update single room status (for quick action from dashboard)
     */
    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,maintenance,cleaning,out_of_order',
        ]);

        $room->update(['status' => $validated['status']]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status kamar {$room->room_number} diubah ke {$validated['status']}",
            ]);
        }

        return back()->with('success', "Status kamar {$room->room_number} diubah ke {$validated['status']}");
    }

    /**
     * Bulk update room status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'exists:rooms,id',
            'status' => 'required|in:available,occupied,maintenance,cleaning,out_of_order',
        ]);

        Room::whereIn('id', $validated['room_ids'])->update(['status' => $validated['status']]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($validated['room_ids'])." kamar diubah ke status {$validated['status']}",
            ]);
        }

        return back()->with('success', count($validated['room_ids'])." kamar diubah ke status {$validated['status']}");
    }
}
