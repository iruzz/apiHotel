<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('room_images')->delete();
        DB::table('rooms')->delete();

        // Insert Rooms
        $rooms = [
            [
                'id' => 1,
                'name' => 'Deluxe Garden View',
                'slug' => 'deluxe-garden-view',
                'description' => 'Kamar nyaman dengan pemandangan taman tropis yang asri. Dilengkapi dengan fasilitas modern dan desain yang elegan untuk pengalaman menginap yang tak terlupakan.',
                'additional_features' => json_encode(['AC', 'TV LED 43"', 'Mini Bar', 'Balkon Privat', 'Wi-Fi Gratis', 'Safe Box']),
                'max_capacity' => 2,
                'price_per_night' => 1500000,
                'available_rooms' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Pool Suite',
                'slug' => 'pool-suite',
                'description' => 'Suite mewah dengan akses kolam renang privat. Nikmati kemewahan dan privasi dengan fasilitas lengkap untuk pasangan atau keluarga kecil.',
                'additional_features' => json_encode(['Private Pool', 'Living Area', 'Kitchenette', 'Smart TV 55"', 'Jacuzzi', 'Butler Service']),
                'max_capacity' => 4,
                'price_per_night' => 2800000,
                'available_rooms' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Royal Villa',
                'slug' => 'royal-villa',
                'description' => 'Villa eksklusif dengan infinity pool dan pemandangan sawah yang memukau. Sempurna untuk liburan romantis atau keluarga dengan ruang yang luas dan privasi maksimal.',
                'additional_features' => json_encode(['Infinity Pool', 'Full Kitchen', '2 Bedrooms', 'Outdoor Shower', 'Private Garden', 'BBQ Area']),
                'max_capacity' => 6,
                'price_per_night' => 4500000,
                'available_rooms' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('rooms')->insert($rooms);

        // Insert Room Images
        $images = [
            // Deluxe Garden View (Room ID 1)
            [
                'room_id' => 1,
                'image_url' => 'rooms/room-deluxe.jpg',
                'alt_text' => 'Deluxe Garden View - Main',
                'order' => 0,
                'type' => 'main',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_id' => 1,
                'image_url' => 'rooms/room-deluxe.jpg',
                'alt_text' => 'Deluxe Garden View - Bathroom',
                'order' => 1,
                'type' => 'gallery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_id' => 1,
                'image_url' => 'rooms/room-deluxe.jpg',
                'alt_text' => 'Deluxe Garden View - Balcony',
                'order' => 2,
                'type' => 'gallery',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pool Suite (Room ID 2)
            [
                'room_id' => 2,
                'image_url' => 'rooms/room-suite.jpg',
                'alt_text' => 'Pool Suite - Main',
                'order' => 0,
                'type' => 'main',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_id' => 2,
                'image_url' => 'rooms/room-suite.jpg',
                'alt_text' => 'Pool Suite - Private Pool',
                'order' => 1,
                'type' => 'gallery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_id' => 2,
                'image_url' => 'rooms/room-suite.jpg',
                'alt_text' => 'Pool Suite - Living Area',
                'order' => 2,
                'type' => 'gallery',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Royal Villa (Room ID 3)
            [
                'room_id' => 3,
                'image_url' => 'rooms/room-villa.jpg',
                'alt_text' => 'Royal Villa - Main',
                'order' => 0,
                'type' => 'main',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_id' => 3,
                'image_url' => 'rooms/room-villa.jpg',
                'alt_text' => 'Royal Villa - Infinity Pool',
                'order' => 1,
                'type' => 'gallery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_id' => 3,
                'image_url' => 'rooms/room-villa.jpg',
                'alt_text' => 'Royal Villa - Master Bedroom',
                'order' => 2,
                'type' => 'gallery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_id' => 3,
                'image_url' => 'rooms/room-villa.jpg',
                'alt_text' => 'Royal Villa - Garden View',
                'order' => 3,
                'type' => 'gallery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('room_images')->insert($images);

        $this->command->info('✅ Rooms and images seeded successfully!');
    }
}