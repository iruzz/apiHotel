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


Route::prefix('services')->group(function () {
    Route::get('/', [ServiceController::class, 'index']); // Get all active services
    Route::get('/{id}', [ServiceController::class, 'show']); // Get service detail
    Route::get('/category/{category}', [ServiceController::class, 'byCategory']); // Get by category
});

// Booking with services
Route::prefix('bookings')->group(function () {
    Route::post('/', [BookingController::class, 'store']); // Create booking with services
    Route::get('/{bookingCode}', [BookingController::class, 'show']); // Get booking detail
    Route::post('/{bookingCode}/add-services', [BookingController::class, 'addServices']); // Add services to existing booking
});