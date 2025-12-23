<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'image_url',
        'has_quantity',
        'max_quantity',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'has_quantity' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope untuk ambil services yang aktif aja
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk sorting berdasarkan sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Relasi ke bookings lewat pivot table
     */
    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_services')
            ->withPivot('quantity', 'price_snapshot', 'notes')
            ->withTimestamps();
    }

    /**
     * Formatted price untuk display
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}