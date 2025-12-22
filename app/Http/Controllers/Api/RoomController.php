<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoomController extends Controller
{
    /**
     * Get all active rooms with images
     */
    public function index()
    {
        $rooms = Room::with(['mainImage', 'images' => function($query) {
            $query->orderBy('order', 'asc');
        }])
            ->where('is_active', true)
            ->where('available_rooms', '>', 0)
            ->get()
            ->map(function ($room) {
                return $this->formatRoomData($room);
            });

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    /**
     * Get room detail by slug with all images
     */
    public function show($slug)
    {
        $room = Room::with(['mainImage', 'galleryImages' => function($query) {
            $query->orderBy('order', 'asc');
        }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatRoomData($room, true)
        ]);
    }

    /**
     * Check room availability with images
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'room_id' => 'nullable|exists:rooms,id'
        ]);

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $guests = $request->guests;
        $roomId = $request->room_id;

        // Query builder dengan images
        $query = Room::with(['mainImage', 'images' => function($q) {
            $q->orderBy('order', 'asc');
        }])
            ->where('is_active', true)
            ->where('max_capacity', '>=', $guests);

        if ($roomId) {
            $query->where('id', $roomId);
        }

        $rooms = $query->get();

        $availableRooms = [];

        foreach ($rooms as $room) {
            // ✅ Cek berapa kamar yang sudah dibooking di range tanggal tersebut (PAKE TABEL BOOKINGS)
            $bookedRooms = DB::table('bookings')
                ->where('room_id', $room->id)
                ->whereIn('status', ['confirmed', 'completed']) // Status aktif
                ->whereNull('deleted_at') // Exclude soft deleted
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($q) use ($checkIn, $checkOut) {
                            $q->where('check_in', '<=', $checkIn)
                              ->where('check_out', '>=', $checkOut);
                        });
                })
                ->count();

            // Hitung kamar yang tersedia
            $availableCount = $room->available_rooms - $bookedRooms;

            if ($availableCount > 0) {
                $nights = $checkIn->diffInDays($checkOut);
                $totalPrice = $room->price_per_night * $nights;

                $availableRooms[] = [
                    'id' => $room->id,
                    'name' => $room->name,
                    'slug' => $room->slug,
                    'description' => $room->description,
                    'additional_features' => $room->additional_features,
                    'max_capacity' => $room->max_capacity,
                    'price_per_night' => $room->price_per_night,
                    'total_price' => $totalPrice,
                    'available_count' => $availableCount,
                    'nights' => $nights,
                    'main_image' => $room->mainImage ? [
                        'id' => $room->mainImage->id,
                        'url' => $room->mainImage->image_url,
                        'alt' => $room->mainImage->alt_text,
                        'type' => $room->mainImage->type
                    ] : null,
                    'images' => $room->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'url' => $img->image_url,
                            'alt' => $img->alt_text,
                            'type' => $img->type,
                            'order' => $img->order
                        ];
                    })
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $checkOut->format('Y-m-d'),
                'guests' => $guests,
                'available_rooms' => $availableRooms,
                'total_found' => count($availableRooms)
            ]
        ]);
    }

    /**
     * Get available room count for specific dates
     */
    public function getAvailableCount(Request $request, $roomId)
    {
        $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $room = Room::with(['mainImage', 'images'])->find($roomId);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found'
            ], 404);
        }

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        // ✅ Hitung kamar yang sudah dibooking (PAKE TABEL BOOKINGS)
        $bookedRooms = DB::table('bookings')
            ->where('room_id', $roomId)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereNull('deleted_at')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                          ->where('check_out', '>=', $checkOut);
                    });
            })
            ->count();

        $availableCount = $room->available_rooms - $bookedRooms;
        $nights = $checkIn->diffInDays($checkOut);

        return response()->json([
            'success' => true,
            'data' => [
                'room_id' => $room->id,
                'room_name' => $room->name,
                'room_slug' => $room->slug,
                'main_image' => $room->mainImage ? [
                    'id' => $room->mainImage->id,
                    'url' => $room->mainImage->image_url,
                    'alt' => $room->mainImage->alt_text
                ] : null,
                'images' => $room->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'url' => $img->image_url,
                        'alt' => $img->alt_text,
                        'type' => $img->type,
                        'order' => $img->order
                    ];
                }),
                'price_per_night' => $room->price_per_night,
                'total_price' => $room->price_per_night * $nights,
                'nights' => $nights,
                'total_rooms' => $room->available_rooms,
                'booked_rooms' => $bookedRooms,
                'available_rooms' => max(0, $availableCount),
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $checkOut->format('Y-m-d')
            ]
        ]);
    }

    /**
     * Helper: Format room data dengan images
     */
    private function formatRoomData($room, $includeGallery = false)
    {
        $data = [
            'id' => $room->id,
            'name' => $room->name,
            'slug' => $room->slug,
            'description' => $room->description,
            'additional_features' => $room->additional_features,
            'max_capacity' => $room->max_capacity,
            'price_per_night' => $room->price_per_night,
            'available_rooms' => $room->available_rooms,
            'main_image' => $room->mainImage ? [
                'id' => $room->mainImage->id,
                'url' => $room->mainImage->image_url,
                'alt' => $room->mainImage->alt_text,
                'type' => $room->mainImage->type
            ] : null,
            // Include ALL images (main + gallery)
            'images' => $room->images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => $img->image_url,
                    'alt' => $img->alt_text,
                    'type' => $img->type,
                    'order' => $img->order
                ];
            })
        ];

        return $data;
    }
}