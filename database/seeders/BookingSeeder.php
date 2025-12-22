<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('bookings')->delete();

        $bookings = [
            // Booking 1 - Confirmed (Past)
            [
                'booking_code' => 'BK' . strtoupper(substr(uniqid(), -8)),
                'room_id' => 1,
                'customer_name' => 'Budi Santoso',
                'customer_whatsapp' => '081234567890',
                'customer_email' => 'budi@example.com',
                'check_in' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'check_out' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'guest_count' => 2,
                'duration_nights' => 3,
                'special_requests' => 'Early check-in',
                'room_price_per_night' => 1500000,
                'total_price' => 4500000,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_gateway' => 'duitku',
                'conversation_id' => null,
                'ai_notes' => null,
                'booking_source' => 'web',
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(7),
                'deleted_at' => null,
            ],

            // Booking 2 - Confirmed (Upcoming - akan bentrok kalo user pilih tanggal ini)
            [
                'booking_code' => 'BK' . strtoupper(substr(uniqid(), -8)),
                'room_id' => 2,
                'customer_name' => 'Siti Nurhaliza',
                'customer_whatsapp' => '081234567891',
                'customer_email' => 'siti@example.com',
                'check_in' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'check_out' => Carbon::now()->addDays(8)->format('Y-m-d'),
                'guest_count' => 2,
                'duration_nights' => 3,
                'special_requests' => 'Honeymoon package',
                'room_price_per_night' => 2800000,
                'total_price' => 8400000,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_gateway' => 'midtrans',
                'conversation_id' => null,
                'ai_notes' => 'Celebrating anniversary',
                'booking_source' => 'whatsapp',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
                'deleted_at' => null,
            ],

            // Booking 3 - Confirmed (Upcoming - Villa)
            [
                'booking_code' => 'BK' . strtoupper(substr(uniqid(), -8)),
                'room_id' => 3,
                'customer_name' => 'Andi Wijaya',
                'customer_whatsapp' => '081234567892',
                'customer_email' => 'andi@example.com',
                'check_in' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'check_out' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'guest_count' => 4,
                'duration_nights' => 5,
                'special_requests' => 'Family vacation, need baby cot',
                'room_price_per_night' => 4500000,
                'total_price' => 22500000,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_gateway' => 'duitku',
                'conversation_id' => null,
                'ai_notes' => 'Family with 2 kids',
                'booking_source' => 'web',
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7),
                'deleted_at' => null,
            ],

            // Booking 4 - Pending (belum bayar)
            [
                'booking_code' => 'BK' . strtoupper(substr(uniqid(), -8)),
                'room_id' => 1,
                'customer_name' => 'Dewi Lestari',
                'customer_whatsapp' => '081234567893',
                'customer_email' => 'dewi@example.com',
                'check_in' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'check_out' => Carbon::now()->addDays(22)->format('Y-m-d'),
                'guest_count' => 2,
                'duration_nights' => 2,
                'special_requests' => null,
                'room_price_per_night' => 1500000,
                'total_price' => 3000000,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_gateway' => 'duitku',
                'conversation_id' => null,
                'ai_notes' => null,
                'booking_source' => 'web',
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2),
                'deleted_at' => null,
            ],

            // Booking 5 - Cancelled (soft deleted)
            [
                'booking_code' => 'BK' . strtoupper(substr(uniqid(), -8)),
                'room_id' => 2,
                'customer_name' => 'Rudi Hartono',
                'customer_whatsapp' => '081234567894',
                'customer_email' => 'rudi@example.com',
                'check_in' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'check_out' => Carbon::now()->addDays(17)->format('Y-m-d'),
                'guest_count' => 2,
                'duration_nights' => 2,
                'special_requests' => null,
                'room_price_per_night' => 2800000,
                'total_price' => 5600000,
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'payment_gateway' => 'midtrans',
                'conversation_id' => null,
                'ai_notes' => 'Customer cancelled due to emergency',
                'booking_source' => 'web',
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(1),
                'deleted_at' => Carbon::now()->subDays(1), // Soft deleted
            ],
        ];

        DB::table('bookings')->insert($bookings);

        $this->command->info('✅ Bookings seeded successfully!');
        $this->command->info('   - 1 completed booking');
        $this->command->info('   - 2 confirmed upcoming bookings');
        $this->command->info('   - 1 pending booking');
        $this->command->info('   - 1 cancelled booking (soft deleted)');
    }
}