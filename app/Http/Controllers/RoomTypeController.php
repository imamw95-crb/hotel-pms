<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::orderBy('sequence')->get();
        return view('admin.room-types.index', compact('roomTypes'));
    }

    public function create()
    {
        return view('admin.room-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:room_types',
            'name' => 'required|string|max:100',
            'sequence' => 'nullable|integer',
        ]);

        RoomType::create($validated);

        return redirect()->route('room-types.index')->with('success', 'Tipe kamar berhasil ditambahkan');
    }

    public function edit(RoomType $roomType)
    {
        return view('admin.room-types.edit', compact('roomType'));
    }

    public function update(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:room_types,code,' . $roomType->id,
            'name' => 'required|string|max:100',
            'sequence' => 'nullable|integer',
        ]);

        $roomType->update($validated);

        return redirect()->route('room-types.index')->with('success', 'Tipe kamar berhasil diupdate');
    }

    public function destroy(RoomType $roomType)
    {
        $roomType->delete();
        return redirect()->route('room-types.index')->with('success', 'Tipe kamar berhasil dihapus');
    }
}