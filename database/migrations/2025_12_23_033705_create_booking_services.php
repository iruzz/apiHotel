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
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->integer('quantity')->default(1); // Jumlah yang dipesan
            $table->decimal('price_snapshot', 10, 2); // Snapshot harga saat booking (biar nggak berubah kalau price di tabel services diupdate)
            $table->text('notes')->nullable(); // Catatan khusus (misal "Massage jam 16.00" atau "Pickup flight GA123")
            $table->timestamps();

            // Prevent duplicate entries
            $table->unique(['booking_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};