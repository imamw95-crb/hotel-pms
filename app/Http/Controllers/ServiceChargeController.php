<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\ServiceCharge;
use Illuminate\Http\Request;

class ServiceChargeController extends Controller
{
    /**
     * List other revenues.
     */
    public function index(Request $request)
    {
        $query = ServiceCharge::with(['guest', 'reservation.room', 'createdBy']);

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        if ($dateFrom) {
            $query->whereDate('charge_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('charge_date', '<=', $dateTo);
        }

        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('charge_number', 'like', "%{$search}%")
                    ->orWhere('service_name', 'like', "%{$search}%")
                    ->orWhereHas('guest', function ($q) use ($search) {
                        $q->where('guest_name', 'like', "%{$search}%");
                    });
            });
        }

        $charges = $query->orderBy('created_at', 'desc')->paginate(15);

        // Summary
        $totalToday = ServiceCharge::whereDate('charge_date', today())->sum('total_amount');
        $totalPeriod = $query->sum('total_amount');

        return view('service-charge.index', compact('charges', 'dateFrom', 'dateTo', 'search', 'totalToday', 'totalPeriod'));
    }

    /**
     * Form tambah other revenue.
     */
    public function create(Request $request)
    {
        $guests = Guest::orderBy('guest_name')->get();
        $reservations = Reservation::with(['guest', 'room'])
            ->whereIn('status', ['checked_in', 'pending'])
            ->orderBy('check_in', 'desc')
            ->get();

        // AJAX via modal: return JSON with rendered modal view (no layout)
        if ($request->expectsJson()) {
            $view = view('service-charge.modal-create', compact('guests', 'reservations'))->render();

            return response()->json([
                'success' => true,
                'view' => $view,
            ]);
        }

        return view('service-charge.create', compact('guests', 'reservations'));
    }

    /**
     * Simpan other revenue.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'nullable|exists:guests,id',
            'service_name' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'charge_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $totalAmount = $validated['amount'] * $validated['quantity'];

        $charge = ServiceCharge::create([
            'reservation_id' => $validated['reservation_id'] ?? null,
            'guest_id' => $validated['guest_id'] ?? null,
            'service_name' => $validated['service_name'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'quantity' => $validated['quantity'],
            'total_amount' => $totalAmount,
            'charge_date' => $validated['charge_date'],
            'payment_method' => $validated['payment_method'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Other revenue berhasil disimpan.',
                'redirect_url' => route('service-charge.show', $charge),
                'charge' => $charge,
            ]);
        }

        return redirect()->route('service-charge.show', $charge)
            ->with('success', 'Other revenue berhasil disimpan.');
    }

    /**
     * Detail & print other revenue.
     */
    public function show(ServiceCharge $serviceCharge)
    {
        $serviceCharge->load(['guest', 'reservation.room', 'createdBy']);

        return view('service-charge.show', ['charge' => $serviceCharge]);
    }
}
