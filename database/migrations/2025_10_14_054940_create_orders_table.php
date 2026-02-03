<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke User (Nullable agar Tamu bisa order)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Data Pelanggan
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable(); // Kolom yang tadi error

            // Detail Pesanan
            $table->string('order_type'); // Dine In / Take Away
            $table->text('shipping_address'); 
            $table->text('order_notes')->nullable(); 

            // Pembayaran & Status
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('status')->default('new'); 
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending'); // Kolom yang tadi error
            
            // Midtrans Token
            $table->string('snap_token')->nullable(); // Kolom yang tadi error

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};