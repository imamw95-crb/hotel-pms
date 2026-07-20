<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailableRoomsController extends Controller
{
    public function index(Request $request)
    {
        $checkIn = $request->input('check_in', Carbon::today()->format('Y-m-d'));
        $checkOut = $request->input('check_out', Carbon::today()->addDay()->format('Y-m-d'));

        $checkInCarbon = Carbon::parse($checkIn);
        $checkOutCarbon = Carbon::parse($checkOut);

        $selectedRoomType = $request->input('room_type', '');
        $selectedStatus = $request->input('status', '');

        // Get all room types ordered by sequence
        $roomTypes = RoomType::orderBy('sequence')->orderBy('name')->get();

        // Get all occupied room IDs for the date range (overlapping active reservations)
        $occupiedByRoom = Reservation::whereIn('status', Reservation::ACTIVE_STATUSES)
            ->where(function ($q) use ($checkInCarbon, $checkOutCarbon) {
                $q->where('check_in', '<', $checkOutCarbon)
                    ->where('check_out', '>', $checkInCarbon);
            })
            ->get(['room_id'])
            ->pluck('room_id')
            ->countBy()
            ->toArray();

        // Get all room IDs that have active Out of Order for this date range
        $oooRoomIds = Room::whereHas('outOfOrders', function ($q) use ($checkInCarbon, $checkOutCarbon) {
            $q->where('status', 'active')
                ->where('start_date', '<=', $checkOutCarbon->format('Y-m-d'))
                ->where(function ($sub) use ($checkInCarbon) {
                    $sub->whereNull('end_date')
                        ->orWhere('end_date', '>=', $checkInCarbon->format('Y-m-d'));
                });
        })->pluck('id')->toArray();

        // Build availability data per room type
        $allRooms = Room::with('roomType')->get()->groupBy('room_type_id');
        $availability = [];

        foreach ($roomTypes as $roomType) {
            $roomsOfType = $allRooms->get($roomType->id, collect());
            $totalAllRooms = $roomsOfType->count();

            if ($totalAllRooms === 0) {
                continue;
            }

            $roomIdsOfType = $roomsOfType->pluck('id')->toArray();

            // Count occupied rooms of this type (with active reservations overlapping the date range)
            $occupiedCount = 0;
            foreach ($roomIdsOfType as $rid) {
                if (isset($occupiedByRoom[$rid])) {
                    $occupiedCount++;
                }
            }

            // Count maintenance or out-of-order rooms
            $unavailableIds = [];
            foreach ($roomIdsOfType as $rid) {
                $room = $roomsOfType->firstWhere('id', $rid);
                if ($room && ($room->status === 'maintenance' || in_array($rid, $oooRoomIds))) {
                    $unavailableIds[] = $rid;
                }
            }
            $maintenanceOrOooCount = count(array_unique($unavailableIds));

            // Available = Total - Occupied - Maintenance/OOO
            $availableCount = $totalAllRooms - $occupiedCount - $maintenanceOrOooCount;
            if ($availableCount < 0) {
                $availableCount = 0;
            }

            // Visual status indicator
            if ($availableCount >= 3) {
                $visualStatus = 'available';
                $visualLabel = 'AVAILABLE';
                $visualClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
            } elseif ($availableCount >= 1) {
                $visualStatus = 'limited';
                $visualLabel = 'LIMITED';
                $visualClass = 'bg-amber-100 text-amber-800 border-amber-300';
            } else {
                $visualStatus = 'sold_out';
                $visualLabel = 'SOLD OUT';
                $visualClass = 'bg-red-100 text-red-800 border-red-300';
            }

            $availability[] = [
                'room_type' => $roomType,
                'total' => $totalAllRooms,
                'occupied' => $occupiedCount,
                'maintenance_or_ooo' => $maintenanceOrOooCount,
                'available' => $availableCount,
                'visual_status' => $visualStatus,
                'visual_label' => $visualLabel,
                'visual_class' => $visualClass,
            ];
        }

        // Apply room type filter
        if ($selectedRoomType) {
            $availability = array_filter($availability, function ($item) use ($selectedRoomType) {
                return $item['room_type']->id == $selectedRoomType || $item['room_type']->name === $selectedRoomType;
            });
        }

        // Apply status filter
        if ($selectedStatus) {
            $availability = array_filter($availability, function ($item) use ($selectedStatus) {
                return $item['visual_status'] === $selectedStatus;
            });
        }

        return view('available-rooms.index', compact(
            'availability',
            'roomTypes',
            'checkIn',
            'checkOut',
            'selectedRoomType',
            'selectedStatus'
        ));
    }
}
