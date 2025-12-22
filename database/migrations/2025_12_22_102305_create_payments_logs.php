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
      Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            
            // Log details
            $table->string('action'); // create_transaction, callback, inquiry, cancel
            $table->enum('type', ['request', 'response', 'callback'])->default('request');
            
            // Payload data
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('callback_data')->nullable();
            
            // Status & notes
            $table->string('status'); // success, failed, pending
            $table->integer('http_code')->nullable(); // HTTP status code
            $table->text('error_message')->nullable();
            $table->text('notes')->nullable();
            
            // IP tracking (buat security)
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamp('created_at');
            
            // Indexes
            $table->index('payment_id');
            $table->index('action');
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments_logs');
    }
};
