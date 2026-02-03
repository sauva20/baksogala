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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel orders
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Relasi ke tabel menu_items
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade'); 
            
            $table->integer('quantity');
            $table->decimal('price', 15, 2);    // Harga satuan saat beli
            $table->decimal('subtotal', 15, 2); // Total (qty * price)
            $table->text('item_notes')->nullable(); // Catatan per item (+topping)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};