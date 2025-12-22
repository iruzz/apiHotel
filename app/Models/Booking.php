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

    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Scopes
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

    // Methods
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
}
