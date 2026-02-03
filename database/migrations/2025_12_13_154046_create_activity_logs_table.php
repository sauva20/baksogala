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
    Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Siapa
        $table->string('action'); // Create, Update, Delete, Login
        $table->string('module'); // Menu, Order, Promo, Auth
        $table->text('description'); // Penjelasan singkat
        $table->string('ip_address')->nullable();
        $table->string('user_agent')->nullable(); // Browser/Device
        $table->json('changes')->nullable(); // Data JSON (Old vs New)
        $table->string('severity')->default('info'); // info, warning, danger
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
