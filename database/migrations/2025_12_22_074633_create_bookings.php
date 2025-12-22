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
            $table->text('special_requests')->nullable();
            
            // Pricing
            $table->decimal('room_price_per_night', 15, 2);
            $table->decimal('total_price', 15, 2);

            // Status tracking
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'expired', 'refunded'])->default('unpaid');
            
            // Payment gateway
            $table->string('payment_gateway')->default('duitku'); // duitku, midtrans, manual
            
            // AI/n8n integration
            $table->string('conversation_id')->nullable();
            $table->text('ai_notes')->nullable();
            $table->string('booking_source')->default('web'); // web, whatsapp, api
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('booking_code');
            $table->index('customer_whatsapp');
            $table->index('customer_email');
            $table->index('status');
            $table->index('payment_status');
            $table->index(['check_in', 'check_out']);
            $table->index('payment_gateway');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};