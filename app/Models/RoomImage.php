<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    protected $fillable = [
        'room_id',
        'image_url',
        'alt_text',
        'order',
        'type'
    ];

    /**
     * Relationship ke room
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}