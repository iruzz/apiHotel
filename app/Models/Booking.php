<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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
        'snap_token',
        'payment_reference',
        'payment_date',
        'payment_expired_at',
        'conversation_id',
        'ai_notes',
        'booking_source',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'payment_date' => 'datetime',
        'payment_expired_at' => 'datetime',
        'room_price_per_night' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Auto-generate booking code
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = self::generateBookingCode();
            }
            
            // Set payment expiry (24 jam dari sekarang)
            if (empty($booking->payment_expired_at)) {
                $booking->payment_expired_at = now()->addHours(24);
            }
        });
    }

    // Generate unique booking code
    public static function generateBookingCode()
    {
        $prefix = 'ASMA';
        $date = date('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        
        return sprintf('%s-%s-%03d', $prefix, $date, $count);
    }

    // Helper: Cek apakah booking sudah expired
    public function isExpired()
    {
        return $this->payment_status === 'unpaid' 
               && $this->payment_expired_at 
               && now()->isAfter($this->payment_expired_at);
    }

    // Helper: Format total harga
    public function getFormattedTotalPriceAttribute()
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    // Scope: Booking aktif (belum cancelled/completed)
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }

    // Scope: Booking yang butuh dicek expiry
    public function scopeNeedExpiryCheck($query)
    {
        return $query->where('payment_status', 'unpaid')
                     ->where('status', 'pending')
                     ->whereNotNull('payment_expired_at')
                     ->where('payment_expired_at', '<', now());
    }
}