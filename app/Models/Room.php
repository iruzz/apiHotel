<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'additional_features',
        'max_capacity',
        'price_per_night',
        'available_rooms',
        'is_active'
    ];

    protected $casts = [
        'additional_features' => 'array',
        'price_per_night' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship ke room images
     */
    public function images()
    {
        return $this->hasMany(RoomImage::class)->orderBy('order');
    }

    /**
     * Get main image
     */
    public function mainImage()
    {
        return $this->hasOne(RoomImage::class)->where('type', 'main')->orderBy('order');
    }

    /**
     * Get gallery images
     */
    public function galleryImages()
    {
        return $this->hasMany(RoomImage::class)->where('type', 'gallery')->orderBy('order');
    }
}