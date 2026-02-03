<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            
            // 1. Cek Customer Name
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->after('user_id');
            }

            // 2. Cek Customer Phone
            if (!Schema::hasColumn('orders', 'customer_phone')) {
                // Saya buat nullable agar tidak error jika data lama kosong
                $table->string('customer_phone')->nullable()->after('user_id'); 
            }

            // 3. Cek Shipping Address (Alamat/Meja)
            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('user_id');
            }

            // 4. Cek Order Type (Dine In / Take Away) - PENTING
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type')->default('Dine In')->after('shipping_address');
            }

            // 5. Cek Order Notes
            if (!Schema::hasColumn('orders', 'order_notes')) {
                $table->text('order_notes')->nullable()->after('shipping_address');
            }

            // 6. Cek Snap Token (Midtrans)
            if (!Schema::hasColumn('orders', 'snap_token')) {
                $table->string('snap_token')->nullable()->after('total_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Hapus kolom jika di-rollback
            $columnsDrop = [];
            
            if (Schema::hasColumn('orders', 'customer_name')) $columnsDrop[] = 'customer_name';
            if (Schema::hasColumn('orders', 'customer_phone')) $columnsDrop[] = 'customer_phone';
            if (Schema::hasColumn('orders', 'shipping_address')) $columnsDrop[] = 'shipping_address';
            if (Schema::hasColumn('orders', 'order_type')) $columnsDrop[] = 'order_type';
            if (Schema::hasColumn('orders', 'order_notes')) $columnsDrop[] = 'order_notes';
            if (Schema::hasColumn('orders', 'snap_token')) $columnsDrop[] = 'snap_token';

            if (!empty($columnsDrop)) {
                $table->dropColumn($columnsDrop);
            }
        });
    }
};