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
        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('image_url');
            $table->string('alt_text')->nullable();
            $table->integer('order')->default(0);
            $table->enum('type', ['main', 'gallery'])->default('gallery');
            $table->timestamps();
            
            // Indexes
            $table->index(['room_id', 'order']);
            $table->index(['room_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_images');
    }
};