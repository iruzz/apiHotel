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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Floating Breakfast"
            $table->text('description')->nullable(); // Deskripsi detail
            $table->decimal('price', 10, 2); // Harga dalam rupiah
            $table->string('category')->default('general'); // food, transport, amenity, activity, service
            $table->string('image_url')->nullable(); // Path gambar service
            $table->boolean('has_quantity')->default(false); // Apakah bisa multiple qty (misal breakfast bisa 2 porsi)
            $table->integer('max_quantity')->nullable(); // Max qty yang bisa dipesan (opsional)
            $table->boolean('is_active')->default(true); // Biar bisa enable/disable tanpa hapus data
            $table->integer('sort_order')->default(0); // Buat ngurutin tampilan di frontend
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};