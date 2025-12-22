<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RoomController extends Controller
{
    // 1. Ambil semua data kamar untuk katalog di landing page
    public function index()
    {
        $rooms = Room::all();
        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    // 2. Logic Cek Ketersediaan (Ditembak Web & AI)
    public function checkAvailability(Request $request)
    {
        // Validasi input
        $request->validate([
            'check_in'  => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests'    => 'required|integer|min:1'
        ]);

        $checkIn  = $request->check_in;
        $checkOut = $request->check_out;
        $guests   = $request->guests;

        // Cari kamar yang kapasitasnya cukup DAN tidak ada booking yang bentrok
        $availableRooms = Room::where('max_capacity', '>=', $guests)
            ->whereDoesntHave('bookings', function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    // Logic tabrakan tanggal:
                    // Ada booking yang check-in atau check-out nya masuk di range pilihan user
                    $q->whereBetween('check_in', [$checkIn, $checkOut])
                      ->orWhereBetween('check_out', [$checkIn, $checkOut])
                      // Atau booking yang sudah ada menelan seluruh tanggal pilihan user
                      ->orWhere(function ($sub) use ($checkIn, $checkOut) {
                          $sub->where('check_in', '<=', $checkIn)
                              ->where('check_out', '>=', $checkOut);
                      });
                })
                // Hanya anggap bentrok jika statusnya sudah CONFIRMED atau PENDING (belum expired)
                ->whereIn('status', ['confirmed', 'pending']);
            })
            ->get();

        return response()->json([
            'success' => true,
            'period' => [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut))
            ],
            'count' => $availableRooms->count(),
            'data' => $availableRooms
        ]);
    }

    public function show($id)
    {
        // Kita pake ID karena di migrasi lo gak ada kolom Slug
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Kamar tidak ditemukan, cog!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'facilities' => $room->additional_features, // Otomatis jadi array kalau di Model sudah di-cast
                'capacity' => $room->max_capacity . ' Orang',
                'price' => $room->price_per_night,
                'image' => $room->image_url,
                // Helper buat frontend biar gak perlu format ribet di React
                'formatted_price' => 'Rp ' . number_format($room->price_per_night, 0, ',', '.')
            ]
        ]);
    }
}