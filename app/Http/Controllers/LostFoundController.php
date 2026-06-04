<?php

namespace App\Http\Controllers;

use App\Models\LostFound;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LostFoundController extends Controller
{
    public function index(Request $request)
    {
        $query = LostFound::with(['room', 'createdBy', 'housekeepingTask'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('guest_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('found_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('found_date', '<=', $request->date_to);
        }

        $items = $query->paginate(20);
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        return view('lost-and-found.index', compact('items', 'rooms'));
    }

    public function create()
    {
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        if (request()->expectsJson()) {
            $view = view('lost-and-found.create', compact('rooms'))->render();

            return response()->json(['success' => true, 'view' => $view]);
        }

        return view('lost-and-found.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'guest_name' => 'nullable|string|max:255',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'found_date' => 'required|date',
            'storage_location' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')
                ->store('lost-and-found', 'public');
        }

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'reported';

        $item = LostFound::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Barang temuan berhasil dilaporkan',
                'item' => $item->load(['room', 'createdBy']),
            ]);
        }

        return redirect()->route('lost-and-found.index')
            ->with('success', 'Barang temuan berhasil dilaporkan');
    }

    public function show(LostFound $lostFound)
    {
        $lostFound->load(['room', 'createdBy', 'housekeepingTask']);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'item' => $lostFound]);
        }

        return view('lost-and-found.show', compact('lostFound'));
    }

    public function updateStatus(Request $request, LostFound $lostFound)
    {
        $validated = $request->validate([
            'status' => 'required|in:reported,claimed,disposed',
            'claimed_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'claimed') {
            $data['claimed_by'] = $validated['claimed_by'];
            $data['claimed_at'] = now();
        }

        if (! empty($validated['notes'])) {
            $data['notes'] = $validated['notes'];
        }

        $lostFound->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status barang temuan berhasil diperbarui',
            ]);
        }

        return redirect()->route('lost-and-found.index')
            ->with('success', 'Status barang temuan berhasil diperbarui');
    }

    public function destroy(LostFound $lostFound)
    {
        $lostFound->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data barang temuan berhasil dihapus',
            ]);
        }

        return redirect()->route('lost-and-found.index')
            ->with('success', 'Data barang temuan berhasil dihapus');
    }
}
