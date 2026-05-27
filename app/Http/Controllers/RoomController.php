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
            'price_weekday' => 'nullable|numeric|min:0',
            'price_weekend' => 'nullable|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,maintenance,cleaning',
        ]);

        if ($request->room_type_id) {
            $roomType = RoomType::find($request->room_type_id);
            $validated['room_type_name'] = $roomType->name;
        }

        // Default weekday/weekend to price_per_night if not set
        $validated['price_weekday'] = $validated['price_weekday'] ?? $validated['price_per_night'];
        $validated['price_weekend'] = $validated['price_weekend'] ?? $validated['price_per_night'];

        $room = Room::create($validated);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kamar berhasil ditambahkan',
                'redirect_url' => route('rooms.index'),
                'room' => $room
            ]);
        }

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
            'price_weekday' => 'nullable|numeric|min:0',
            'price_weekend' => 'nullable|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,maintenance,cleaning',
        ]);

        if ($request->room_type_id) {
            $roomType = RoomType::find($request->room_type_id);
            $validated['room_type_name'] = $roomType->name;
        }

        // Default weekday/weekend to price_per_night if not set
        $validated['price_weekday'] = $validated['price_weekday'] ?? $validated['price_per_night'];
        $validated['price_weekend'] = $validated['price_weekend'] ?? $validated['price_per_night'];

        $room->update($validated);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kamar berhasil diupdate',
                'redirect_url' => route('rooms.index'),
                'room' => $room
            ]);
        }

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil diupdate');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        
        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kamar berhasil dihapus',
                'redirect_url' => route('rooms.index')
            ]);
        }
        
        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil dihapus');
    }
}