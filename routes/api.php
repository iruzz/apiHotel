<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rooms endpoints
Route::prefix('rooms')->group(function () {
    // Get all rooms
    Route::get('/', [RoomController::class, 'index']);

    // Check availability (untuk form search)
    Route::post('/check-availability', [RoomController::class, 'checkAvailability']);
    
    // Get specific room by slug
    Route::get('/{slug}', [RoomController::class, 'show']);
    
    // Get available count for specific room
    Route::post('/{roomId}/available-count', [RoomController::class, 'getAvailableCount']);
});

// Services endpoints
Route::prefix('services')->group(function () {
    Route::get('/', [ServiceController::class, 'index']); // Get all active services
    Route::get('/{id}', [ServiceController::class, 'show']); // Get service detail
    Route::get('/category/{category}', [ServiceController::class, 'byCategory']); // Get by category
});

// Booking endpoints
Route::prefix('bookings')->group(function () {
    // Create booking with services
    Route::post('/', [BookingController::class, 'store']);
    
    // Check booking by code (dipindahkan ke atas untuk menghindari konflik)
    Route::get('/check/{bookingCode}', [BookingController::class, 'checkBooking']);
    
    // Get booking detail (route ini juga bisa digunakan untuk check booking)
    Route::get('/{bookingCode}', [BookingController::class, 'show']);
    
    // Add services to existing booking
    Route::post('/{bookingCode}/add-services', [BookingController::class, 'addServices']);
});