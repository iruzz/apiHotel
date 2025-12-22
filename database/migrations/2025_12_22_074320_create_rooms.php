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
            $table->string('slug')->unique();
            $table->text('description'); 
            $table->json('additional_features');
            $table->integer('max_capacity'); 
            $table->decimal('price_per_night', 15, 2);
            $table->integer('available_rooms')->default(0); // ✅ Stock kamar
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('slug');
            $table->index('is_active');
            $table->index('available_rooms');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};