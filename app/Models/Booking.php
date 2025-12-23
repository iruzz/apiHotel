<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_code',
        'room_id',
        'customer_name',
        'customer_whatsapp',
        'customer_email',
        'check_in',
        'check_out',
        'guest_count',
        'duration_nights',
        'special_requests',
        'room_price_per_night',
        'total_price',
        'status',
        'payment_status',
        'payment_gateway',
        'conversation_id',
        'ai_notes',
        'booking_source',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'room_price_per_night' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // ========================================
    // EXISTING RELATIONSHIPS
    // ========================================
    
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // ========================================
    // NEW: ADDITIONAL SERVICES RELATIONSHIP
    // ========================================
    
    /**
     * Relasi ke services lewat pivot table
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_services')
            ->withPivot('quantity', 'price_snapshot', 'notes')
            ->withTimestamps();
    }

    // ========================================
    // EXISTING SCOPES
    // ========================================
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    // ========================================
    // EXISTING METHODS
    // ========================================
    
    public static function generateBookingCode()
    {
        return 'BOOK-' . strtoupper(uniqid());
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isExpired()
    {
        return $this->payment_status === 'expired';
    }

    public function markAsPaid()
    {
        $this->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
    }

    public function markAsExpired()
    {
        $this->update([
            'payment_status' => 'expired',
            'status' => 'cancelled',
        ]);
        
        // Return stock
        $this->room->incrementStock();
    }

    // ========================================
    // NEW: ADDITIONAL SERVICES METHODS
    // ========================================
    
    /**
     * Calculate total dari additional services
     */
    public function getAdditionalServicesTotal()
    {
        return $this->services->sum(function ($service) {
            return $service->pivot->price_snapshot * $service->pivot->quantity;
        });
    }

    /**
     * Get subtotal (room price + services, before service fee)
     */
    public function getSubtotal()
    {
        $roomTotal = $this->room_price_per_night * $this->duration_nights;
        $servicesTotal = $this->getAdditionalServicesTotal();
        
        return $roomTotal + $servicesTotal;
    }

    /**
     * Calculate service fee (10% dari subtotal)
     */
    public function calculateServiceFee()
    {
        return $this->getSubtotal() * 0.10;
    }

    /**
     * Calculate grand total (room + services + fee)
     * This should be called setelah services di-attach
     */
    public function calculateGrandTotal()
    {
        $subtotal = $this->getSubtotal();
        $serviceFee = $this->calculateServiceFee();
        
        return $subtotal + $serviceFee;
    }

    /**
     * Update total_price including services
     * Call ini setelah attach/detach services
     */
    public function updateTotalPrice()
    {
        $this->total_price = $this->calculateGrandTotal();
        $this->save();
    }

    /**
     * Check if booking has additional services
     */
    public function hasAdditionalServices()
    {
        return $this->services()->count() > 0;
    }
}