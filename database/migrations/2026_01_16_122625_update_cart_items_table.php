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
    Schema::table('cart_items', function (Blueprint $table) {
        // Hapus kolom lama jika perlu, atau tambahkan yang baru
        // Kita asumsikan tabel cart_items sebelumnya kosong melompong
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Jika user login
        $table->string('session_id')->nullable(); // Jika user guest (tamu)
        $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
        $table->integer('quantity')->default(1);
        $table->json('addons')->nullable(); // MENYIMPAN ID SIDE DISH/TETELAN (Format JSON)
        $table->text('notes')->nullable(); // Catatan pedas/tidak
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            //
        });
    }
};
