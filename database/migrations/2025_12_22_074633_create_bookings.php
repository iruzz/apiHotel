<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // Booking identifier
            $table->string('booking_code')->unique();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');

            // Customer info
            $table->string('customer_name');
            $table->string('customer_whatsapp');
            $table->string('customer_email');

            // Booking details
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('guest_count');
            $table->integer('duration_nights');
            $table->text('special_requests')->nullable(); // ✅ NEW
            
            // Pricing
            $table->decimal('room_price_per_night', 15, 2); // Snapshot harga saat booking
            $table->decimal('total_price', 15, 2);

            // Status tracking
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])
                  ->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'expired', 'refunded'])
                  ->default('unpaid');
            
            // Payment integration
            $table->string('snap_token')->nullable();
            $table->string('payment_reference')->nullable(); // ✅ NEW: Midtrans Order ID
            $table->timestamp('payment_date')->nullable(); // ✅ NEW
            $table->timestamp('payment_expired_at')->nullable(); // ✅ NEW: Auto-cancel
            
            // AI/n8n integration
            $table->string('conversation_id')->nullable(); // ✅ NEW: n8n conversation tracking
            $table->text('ai_notes')->nullable(); // ✅ NEW: Notes from AI
            $table->string('booking_source')->default('web'); // web, whatsapp, api
            
            $table->timestamps();
            $table->softDeletes(); // Soft delete buat audit trail
            
            // Indexes untuk performa
            $table->index('booking_code');
            $table->index('customer_whatsapp');
            $table->index('status');
            $table->index('payment_status');
            $table->index(['check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};