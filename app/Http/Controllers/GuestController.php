<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = Guest::query();

        // Default tanggal: hari ini
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        // Filter berdasarkan tanggal created_at (tanggal tamu ditambahkan/daftar)
        $query->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        if ($request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', '%'.$search.'%')
                    ->orWhere('id_number', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $guests = $query->orderBy('guest_name')->paginate(25);

        return view('guests.index', compact('guests', 'dateFrom', 'dateTo'));
    }

    public function create()
    {
        return view('guests.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'guest_name' => 'required|string|max:100',
            'id_number' => 'nullable|string|max:50|unique:guests',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $guest = Guest::create($request->all());

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tamu berhasil ditambahkan',
                'redirect_url' => route('guests.index'),
                'guest' => $guest,
            ]);
        }

        return redirect()->route('guests.index')->with('success', 'Tamu berhasil ditambahkan');
    }

    public function edit(Guest $guest)
    {
        return view('guests.edit', compact('guest'));
    }

    public function update(Request $request, Guest $guest)
    {
        $request->validate([
            'guest_name' => 'required|string|max:100',
            'id_number' => 'nullable|string|max:50|unique:guests,id_number,'.$guest->id,
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $guest->update($request->all());

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tamu berhasil diperbarui',
                'redirect_url' => route('guests.index'),
                'guest' => $guest,
            ]);
        }

        return redirect()->route('guests.index')->with('success', 'Tamu berhasil diperbarui');
    }

    public function destroy(Guest $guest)
    {
        $guest->delete();

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tamu berhasil dihapus',
                'redirect_url' => route('guests.index'),
            ]);
        }

        return redirect()->route('guests.index')->with('success', 'Tamu berhasil dihapus');
    }

    public function export(Request $request)
    {
        $query = Guest::query();

        if ($request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', '%'.$search.'%')
                    ->orWhere('id_number', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $guests = $query->orderBy('guest_name')->get();

        // Generate CSV
        $filename = 'master-tamu-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($guests) {
            $handle = fopen('php://output', 'w');

            // Set UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($handle, ['Nama Tamu', 'No. Identitas', 'No. Telepon', 'Email', 'Alamat', 'Catatan'], ';');

            // Data rows
            foreach ($guests as $guest) {
                fputcsv($handle, [
                    $guest->guest_name,
                    $guest->id_number ?? '',
                    $guest->phone ?? '',
                    $guest->email ?? '',
                    $guest->address ?? '',
                    $guest->notes ?? '',
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }
}
