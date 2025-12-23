<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Floating Breakfast',
                'description' => 'Nikmati sarapan romantis di kolam renang dengan pemandangan sunrise. Include: Nasi goreng/pancake, fresh juice, buah segar, kopi/teh.',
                'price' => 150000,
                'category' => 'food',
                'image_url' => '/rooms/room-villa.jpg',
                'has_quantity' => true,
                'max_quantity' => 4,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Airport Pickup',
                'description' => 'Layanan antar-jemput dari Bandara Ngurah Rai ke villa. Termasuk driver profesional dan mobil ber-AC.',
                'price' => 250000,
                'category' => 'transport',
                'image_url' => '/rooms/room-villa.jpg',
                'has_quantity' => false,
                'max_quantity' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'In-Villa Massage (90 menit)',
                'description' => 'Pijat tradisional Bali oleh terapis profesional. Dilakukan di villa Anda dengan aromatherapy dan musik relaksasi.',
                'price' => 400000,
                'category' => 'wellness',
                'image_url' => '/rooms/room-villa.jpg',
                'has_quantity' => true,
                'max_quantity' => 4,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Late Check-out',
                'description' => 'Perpanjang waktu check-out hingga jam 16:00 (dari standar 12:00). Subject to availability.',
                'price' => 200000,
                'category' => 'service',
                'image_url' => null,
                'has_quantity' => false,
                'max_quantity' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'BBQ Dinner Package',
                'description' => 'Paket BBQ untuk 2 orang dengan menu seafood, sayuran, dan nasi. Termasuk peralatan dan chef.',
                'price' => 500000,
                'category' => 'food',
                'image_url' => '/rooms/room-villa.jpg',
                'has_quantity' => true,
                'max_quantity' => 10,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Romantic Decoration',
                'description' => 'Dekorasi romantis di kamar dengan rose petals, lilin, balon, dan bunga segar. Cocok untuk honeymoon atau anniversary.',
                'price' => 350000,
                'category' => 'amenity',
                'image_url' => '/rooms/room-villa.jpg',
                'has_quantity' => false,
                'max_quantity' => null,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Private Ubud Tour',
                'description' => 'Tour privat 8 jam mengunjungi Tegalalang Rice Terrace, Monkey Forest, Tegenungan Waterfall, dan Ubud Market. Include driver + mobil.',
                'price' => 800000,
                'category' => 'activity',
                'image_url' => '/rooms/room-villa.jpg',
                'has_quantity' => false,
                'max_quantity' => null,
                'is_active' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->insert(array_merge($service, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}