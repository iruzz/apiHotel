<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Create booking dengan additional services
     * Endpoint: POST /api/bookings
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'customer_name' => 'required|string|max:255',
            'customer_whatsapp' => 'required|string|max:20',
            'customer_email' => 'required|email',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guest_count' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
            
            // Additional services (optional)
            'services' => 'nullable|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Get room data
            $room = Room::findOrFail($validated['room_id']);

            // Calculate duration
            $checkIn = \Carbon\Carbon::parse($validated['check_in']);
            $checkOut = \Carbon\Carbon::parse($validated['check_out']);
            $durationNights = $checkIn->diffInDays($checkOut);

            // Calculate room total
            $roomTotal = $room->price_per_night * $durationNights;

            // Create booking (tanpa services dulu)
            $booking = Booking::create([
                'booking_code' => Booking::generateBookingCode(),
                'room_id' => $validated['room_id'],
                'customer_name' => $validated['customer_name'],
                'customer_whatsapp' => $validated['customer_whatsapp'],
                'customer_email' => $validated['customer_email'],
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'guest_count' => $validated['guest_count'],
                'duration_nights' => $durationNights,
                'special_requests' => $validated['special_requests'] ?? null,
                'room_price_per_night' => $room->price_per_night,
                'total_price' => $roomTotal, // Temporary, will be updated after services
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'booking_source' => 'website',
            ]);

            // Attach additional services (if any)
            if (!empty($validated['services'])) {
                foreach ($validated['services'] as $serviceData) {
                    $service = Service::findOrFail($serviceData['service_id']);
                    
                    // Validate quantity if service has max_quantity
                    if ($service->max_quantity && $serviceData['quantity'] > $service->max_quantity) {
                        throw new \Exception("Quantity for {$service->name} exceeds maximum allowed ({$service->max_quantity})");
                    }

                    $booking->services()->attach($service->id, [
                        'quantity' => $serviceData['quantity'],
                        'price_snapshot' => $service->price, // Save price snapshot
                        'notes' => $serviceData['notes'] ?? null,
                    ]);
                }
            }

            // Recalculate total price including services
            $booking->updateTotalPrice();

            // Refresh booking to get updated relationships
            $booking->refresh();
            $booking->load(['room', 'services']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => $booking,
                    'breakdown' => [
                        'room_total' => $roomTotal,
                        'services_total' => $booking->getAdditionalServicesTotal(),
                        'subtotal' => $booking->getSubtotal(),
                        'service_fee' => $booking->calculateServiceFee(),
                        'grand_total' => $booking->total_price,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get booking detail with services
     * Endpoint: GET /api/bookings/{bookingCode}
     */
    public function show($bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)
            ->with(['room', 'services'])
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'booking' => $booking,
                'breakdown' => [
                    'room_total' => $booking->room_price_per_night * $booking->duration_nights,
                    'services_total' => $booking->getAdditionalServicesTotal(),
                    'subtotal' => $booking->getSubtotal(),
                    'service_fee' => $booking->calculateServiceFee(),
                    'grand_total' => $booking->total_price,
                ],
            ],
        ]);
    }

     public function checkBooking($bookingCode)
    {
        try {
            // Find booking with room and services relationships
            $booking = Booking::with(['room.mainImage', 'services'])
                ->where('booking_code', $bookingCode)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking tidak ditemukan. Periksa kembali kode booking Anda.'
                ], 404);
            }

            // Format services from many-to-many relationship
            $services = $booking->services->map(function($service) {
                return [
                    'name' => $service->name,
                    'quantity' => $service->pivot->quantity,
                    'price' => (float) $service->pivot->price_snapshot, // price per unit
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'booking_code' => $booking->booking_code,
                    'room_name' => $booking->room->name,
                    'check_in' => $booking->check_in->format('Y-m-d'),
                    'check_out' => $booking->check_out->format('Y-m-d'),
                    'nights' => $booking->duration_nights,
                    'guests' => $booking->guest_count,
                    'total_amount' => (float) $booking->total_price,
                    'status' => $booking->status,
                    'guest_name' => $booking->customer_name,
                    'guest_email' => $booking->customer_email,
                    'guest_phone' => $booking->customer_whatsapp,
                    'services' => $services,
                    'created_at' => $booking->created_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error checking booking: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data booking.'
            ], 500);
        }
    }


    /**
     * Add services to existing booking
     * Endpoint: POST /api/bookings/{bookingCode}/add-services
     */
    public function addServices(Request $request, $bookingCode)
    {
        $validated = $request->validate([
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.notes' => 'nullable|string',
        ]);

        $booking = Booking::where('booking_code', $bookingCode)->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if booking is still in pending/unpaid status
        if ($booking->isPaid() || $booking->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add services to this booking',
            ], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($validated['services'] as $serviceData) {
                $service = Service::findOrFail($serviceData['service_id']);

                // Check if service already exists, if yes, update quantity
                $existingService = $booking->services()->where('service_id', $service->id)->first();

                if ($existingService) {
                    $newQuantity = $existingService->pivot->quantity + $serviceData['quantity'];
                    
                    // Validate max quantity
                    if ($service->max_quantity && $newQuantity > $service->max_quantity) {
                        throw new \Exception("Total quantity for {$service->name} exceeds maximum allowed ({$service->max_quantity})");
                    }

                    $booking->services()->updateExistingPivot($service->id, [
                        'quantity' => $newQuantity,
                    ]);
                } else {
                    $booking->services()->attach($service->id, [
                        'quantity' => $serviceData['quantity'],
                        'price_snapshot' => $service->price,
                        'notes' => $serviceData['notes'] ?? null,
                    ]);
                }
            }

            // Recalculate total
            $booking->updateTotalPrice();
            $booking->refresh();
            $booking->load(['room', 'services']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Services added successfully',
                'data' => [
                    'booking' => $booking,
                    'breakdown' => [
                        'room_total' => $booking->room_price_per_night * $booking->duration_nights,
                        'services_total' => $booking->getAdditionalServicesTotal(),
                        'subtotal' => $booking->getSubtotal(),
                        'service_fee' => $booking->calculateServiceFee(),
                        'grand_total' => $booking->total_price,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add services',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}