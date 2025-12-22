<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('slug')->unique(); // Buat SEO-friendly URL
            $table->text('description'); 
            $table->json('additional_features'); // ["WiFi", "AC", "TV"]
            $table->json('images')->nullable(); // Multiple images
            $table->integer('max_capacity'); 
            $table->decimal('price_per_night', 15, 2); // Lebih presisi untuk harga
            $table->boolean('is_active')->default(true); // Buat temporary disable kamar
            $table->timestamps();
            
            // Index untuk performa query
            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};