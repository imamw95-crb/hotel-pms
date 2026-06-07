<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Single dashboard view that adapts to user role
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isOwner()) {
            return $this->ownerData();
        }

        if ($user->isAdmin()) {
            return $this->adminData();
        }

        if ($user->isUserManager()) {
            return $this->ownerData();
        }

        return $this->frontOfficeData();
    }

    /**
     * Owner dashboard data
     */
    private function ownerData()
    {
        $totalRooms = Room::count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;

        $todayRevenue = Transaction::whereDate('created_at', Carbon::today())->sum('amount');
        $monthRevenue = Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        // Check-in/check-out jam 12:00 siang
        $checkinsToday = Reservation::whereDate('check_in', Carbon::today())
            ->where('status', 'pending')->count();
        $checkoutsToday = Reservation::whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')->count();

        // Due Out: kamar yang tamu-nya check-out HARI INI (masih occupied, akan kosong siang)
        $dueOutRooms = Reservation::with(['room', 'guest'])
            ->whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')
            ->orderBy('room_id')
            ->get();

        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $last7Days['labels'][] = $date->format('D');

            // Occupancy: tamu yang check-out hari ini masih terhitung sampai jam 12:00 siang
            $occupied = Reservation::whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>=', $date)
                ->whereIn('status', ['checked_in', 'checked_out'])
                ->count();
            $last7Days['occupancy'][] = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100) : 0;

            $last7Days['revenue'][] = Transaction::whereDate('created_at', $date)->sum('amount');
        }

        $recentReservations = Reservation::with(['room', 'guest'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'totalRooms', 'occupiedRooms', 'occupancyRate',
            'todayRevenue', 'monthRevenue', 'checkinsToday', 'checkoutsToday',
            'dueOutRooms', 'last7Days', 'recentReservations'
        ));
    }

    /**
     * Admin dashboard data
     */
    private function adminData()
    {
        $totalUsers = User::count();
        $totalRooms = Room::count();
        $totalReservations = Reservation::count();
        $totalRevenue = Transaction::sum('amount');

        return view('dashboard.index', compact(
            'totalUsers', 'totalRooms', 'totalReservations', 'totalRevenue'
        ));
    }

    /**
     * Front Office dashboard data
     */
    private function frontOfficeData()
    {
        $availableRooms = Room::where('status', 'available')->count();
        // Check-in/check-out jam 12:00 siang
        $todayCheckins = Reservation::whereDate('check_in', Carbon::today())
            ->where('status', 'pending')->count();
        $todayCheckouts = Reservation::whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')->count();

        // Due Out: kamar yang tamu-nya check-out HARI INI
        // (masih occupied, akan kosong siang ini — siap untuk back-to-back check-in)
        $dueOutRooms = Reservation::with(['room', 'guest'])
            ->whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')
            ->orderBy('room_id')
            ->get();

        // Occupied (non-due-out): kamar yang masih benar-benar terisi
        $trulyOccupiedRooms = Room::where('status', 'occupied')
            ->whereDoesntHave('reservations', function ($q) {
                $q->whereDate('check_out', Carbon::today())
                    ->where('status', 'checked_in');
            })
            ->count();

        return view('dashboard.index', compact(
            'availableRooms', 'todayCheckins', 'todayCheckouts',
            'dueOutRooms', 'trulyOccupiedRooms'
        ));
    }

    /**
     * Auto-cancel pending bookings from website/web (via AJAX).
     */
    public function autoCancelPending()
    {
        $hours = 3;
        $threshold = Carbon::now()->subHours($hours);

        $pendingBookings = Reservation::whereIn('status', ['menunggu_pembayaran', 'pending'])
            ->where(function ($q) {
                $q->where('ota_source', 'website')
                    ->orWhere('ota_source', 'api');
            })
            ->where('created_at', '<', $threshold)
            ->orderBy('created_at', 'asc')
            ->get();

        $total = $pendingBookings->count();
        $cancelled = 0;

        foreach ($pendingBookings as $reservation) {
            try {
                $reservation->update([
                    'status' => 'cancelled',
                    'notes' => ($reservation->notes ? $reservation->notes."\n" : '')
                        .'[Auto-cancel] Dibatalkan manual dari PMS (pending >'.$hours.' jam pada '.now()->format('d/m/Y H:i').')',
                ]);

                if ($reservation->room) {
                    $reservation->room->update(['status' => 'available']);
                }

                $cancelled++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Auto-cancel gagal untuk reservasi {$reservation->id}: {$e->getMessage()}");
            }
        }

        if ($total === 0) {
            return response()->json([
                'success' => true,
                'message' => '✅ Tidak ada booking pending yang perlu dibatalkan.',
                'total' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "✅ {$cancelled} booking pending dibatalkan (dari {$total}).",
            'total' => $cancelled,
        ]);
    }
}
