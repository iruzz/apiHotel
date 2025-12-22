<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'additional_features',
        'images',
        'max_capacity',
        'price_per_night',
        'is_active',
    ];

    protected $casts = [
        'additional_features' => 'array',
        'images' => 'array',
        'price_per_night' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Auto-generate slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($room) {
            if (empty($room->slug)) {
                $room->slug = Str::slug($room->name);
            }
        });
    }

    // Relationships
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Helper: Cek apakah kamar available di tanggal tertentu
    public function isAvailable($checkIn, $checkOut)
    {
        return !$this->bookings()
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    // Check-in baru jatuh di tengah booking lama
                    $q->where('check_in', '<=', $checkIn)
                      ->where('check_out', '>', $checkIn);
                })
                ->orWhere(function ($q) use ($checkIn, $checkOut) {
                    // Check-out baru jatuh di tengah booking lama
                    $q->where('check_in', '<', $checkOut)
                      ->where('check_out', '>=', $checkOut);
                })
                ->orWhere(function ($q) use ($checkIn, $checkOut) {
                    // Booking baru menelan booking lama
                    $q->where('check_in', '>=', $checkIn)
                      ->where('check_out', '<=', $checkOut);
                });
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereIn('payment_status', ['unpaid', 'paid'])
            ->exists();
    }

    // Accessor: Format harga untuk display
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price_per_night, 0, ',', '.');
    }

    // Accessor: Main image (ambil foto pertama)
    public function getMainImageAttribute()
    {
        return $this->images[0] ?? asset('images/default-room.jpg');
    }

    // Scope: Hanya kamar aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}