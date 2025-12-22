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
       Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->onDelete('cascade');
            
            // Payment details
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable(); // VC, VA, QRIS, dll
            $table->enum('payment_status', ['pending', 'success', 'failed', 'expired'])->default('pending');
            
            // Duitku specific fields
            $table->string('duitku_merchant_order_id')->unique(); // BOOK-{id}-{timestamp}
            $table->string('duitku_reference')->nullable(); // Reference dari Duitku
            $table->string('duitku_signature')->nullable(); // Untuk validasi callback
            $table->text('payment_url')->nullable(); // Redirect URL
            $table->string('duitku_payment_code')->nullable(); // Kode pembayaran (VA number, QRIS, dll)
            
            // Additional data
            $table->json('payment_details')->nullable(); // Simpan detail lengkap dari Duitku
            
            // Timestamps
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('payment_status');
            $table->index('duitku_merchant_order_id');
            $table->index('duitku_reference');
            $table->index('payment_method');
            $table->index('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
