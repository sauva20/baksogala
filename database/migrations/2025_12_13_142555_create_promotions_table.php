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
    Schema::create('promotions', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // Kode: GALA50
        $table->enum('type', ['percentage', 'fixed']); // Tipe: Persen / Nominal
        $table->decimal('discount_amount', 10, 2); // Nilai: 50 (untuk 50%) atau 10000 (untuk Rp 10k)
        $table->decimal('min_purchase', 10, 2)->default(0); // Min. Belanja
        $table->decimal('max_discount', 10, 2)->nullable(); // Maks. Potongan (Khusus persen)
        $table->integer('quota')->default(0); // Kuota Voucher
        $table->date('start_date');
        $table->date('end_date');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
