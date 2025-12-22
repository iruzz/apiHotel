<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // Kode unik untuk tamu (misal: ASMA-20251222-001)
            $table->string('booking_code')->unique();

            // Relasi ke tabel rooms
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');

            // Data Diri Tamu (diambil dari form atau chat AI)
            $table->string('customer_name');
            $table->string('customer_whatsapp'); // Format: 628123xxx
            $table->string('customer_email');

            // Detail Reservasi
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('guest_count'); // Untuk divalidasi dengan max_capacity di tabel rooms
            $table->integer('duration_nights'); // Disimpan biar gampang buat laporan

            // Detail Harga
            $table->bigInteger('total_price');

            // Status Alur Kerja
            // status: pending (baru booking), confirmed (sudah bayar), cancelled, completed (setelah check-out)
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            
            // payment_status: unpaid, paid, expired
            $table->enum('payment_status', ['unpaid', 'paid', 'expired'])->default('unpaid');

            // Token untuk Midtrans Snap (biar tamu bisa buka link bayar lagi kalau gak sengaja ketutup)
            $table->string('snap_token')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
