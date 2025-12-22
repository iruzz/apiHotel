<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomController;
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