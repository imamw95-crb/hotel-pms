<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = Guest::query();

        if ($request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', '%' . $search . '%')
                    ->orWhere('id_number', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $guests = $query->orderBy('guest_name')->paginate(25);
        return view('guests.index', compact('guests'));
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

        Guest::create($request->all());

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
            'id_number' => 'nullable|string|max:50|unique:guests,id_number,' . $guest->id,
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $guest->update($request->all());

        return redirect()->route('guests.index')->with('success', 'Tamu berhasil diperbarui');
    }

    public function destroy(Guest $guest)
    {
        $guest->delete();
        return redirect()->route('guests.index')->with('success', 'Tamu berhasil dihapus');
    }

    public function export(Request $request)
    {
        $query = Guest::query();

        if ($request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', '%' . $search . '%')
                    ->orWhere('id_number', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $guests = $query->orderBy('guest_name')->get();

        // Generate CSV
        $filename = 'master-tamu-' . now()->format('Y-m-d-His') . '.csv';
        
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
