<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('roomType')->orderBy('room_number')->get();
        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $roomTypes = RoomType::all();
        return view('admin.rooms.create', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|unique:rooms',
            'room_type_id' => 'nullable|exists:room_types,id',
            'price_per_night' => 'required|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,maintenance,cleaning',
        ]);

        if ($request->room_type_id) {
            $roomType = RoomType::find($request->room_type_id);
            $validated['room_type_name'] = $roomType->name;
        }

        Room::create($validated);

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil ditambahkan');
    }

    public function edit(Room $room)
    {
        $roomTypes = RoomType::all();
        return view('admin.rooms.edit', compact('room', 'roomTypes'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|unique:rooms,room_number,' . $room->id,
            'room_type_id' => 'nullable|exists:room_types,id',
            'price_per_night' => 'required|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,maintenance,cleaning',
        ]);

        if ($request->room_type_id) {
            $roomType = RoomType::find($request->room_type_id);
            $validated['room_type_name'] = $roomType->name;
        }

        $room->update($validated);

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil diupdate');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil dihapus');
    }
}