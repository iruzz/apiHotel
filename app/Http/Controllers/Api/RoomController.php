<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RoomController extends Controller
{
    /**
     * 1. List semua kamar (untuk katalog landing page)
     */
    public function index(Request $request)
    {
        $query = Room::active();

        // Filter by capacity (optional)
        if ($request->has('min_capacity')) {
            $query->where('max_capacity', '>=', $request->min_capacity);
        }

        // Filter by price range (optional)
        if ($request->has('max_price')) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        $rooms = $query->get()->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name,
                'slug' => $room->slug,
                'description' => $room->description,
                'facilities' => $room->additional_features,
                'max_capacity' => $room->max_capacity,
                'price_per_night' => $room->price_per_night,
                'formatted_price' => $room->formatted_price,
                'main_image' => $room->main_image,
                'images' => $room->images,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $rooms->count(),
            'data' => $rooms
        ]);
    }

    /**
     * 2. Detail kamar by ID atau Slug
     */
    public function show($identifier)
    {
        $room = Room::where('id', $identifier)
                    ->orWhere('slug', $identifier)
                    ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Kamar tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $room->id,
                'name' => $room->name,
                'slug' => $room->slug,
                'description' => $room->description,
                'facilities' => $room->additional_features,
                'max_capacity' => $room->max_capacity,
                'capacity_text' => $room->max_capacity . ' Orang',
                'price_per_night' => $room->price_per_night,
                'formatted_price' => $room->formatted_price,
                'main_image' => $room->main_image,
                'images' => $room->images ?? [],
            ]
        ]);
    }

    /**
     * 3. Cek ketersediaan kamar (untuk Web & AI)
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'check_in'  => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests'    => 'required|integer|min:1'
        ]);

        $checkIn = $validated['check_in'];
        $checkOut = $validated['check_out'];
        $guests = $validated['guests'];

        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

        // Cari kamar yang kapasitasnya cukup
        $rooms = Room::active()
            ->where('max_capacity', '>=', $guests)
            ->get();

        // Filter kamar yang available
        $availableRooms = $rooms->filter(function ($room) use ($checkIn, $checkOut) {
            return $room->isAvailable($checkIn, $checkOut);
        });

        $results = $availableRooms->map(function ($room) use ($nights) {
            return [
                'id' => $room->id,
                'name' => $room->name,
                'slug' => $room->slug,
                'description' => $room->description,
                'facilities' => $room->additional_features,
                'max_capacity' => $room->max_capacity,
                'price_per_night' => $room->price_per_night,
                'total_price' => $room->price_per_night * $nights,
                'formatted_price_per_night' => $room->formatted_price,
                'formatted_total_price' => 'Rp ' . number_format($room->price_per_night * $nights, 0, ',', '.'),
                'main_image' => $room->main_image,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'period' => [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => $nights
            ],
            'guest_count' => $guests,
            'available_rooms' => $results->count(),
            'data' => $results
        ]);
    }

    /**
     * 4. Endpoint khusus untuk AI (format simplified)
     * Digunakan oleh n8n untuk mendapatkan info kamar dengan format yang AI-friendly
     */
    public function getForAI($identifier)
    {
        $room = Room::where('id', $identifier)
                    ->orWhere('slug', $identifier)
                    ->first();

        if (!$room) {
            return response()->json([
                'error' => 'Room not found'
            ], 404);
        }

        // Format simple untuk AI parsing
        return response()->json([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'facilities' => implode(', ', $room->additional_features),
            'max_guests' => $room->max_capacity,
            'price_per_night' => (int) $room->price_per_night,
            'price_idr' => 'Rp' . number_format($room->price_per_night, 0, ',', '.'),
        ]);
    }
}