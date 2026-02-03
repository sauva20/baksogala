<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // Relasi ke user yang memberi ulasan
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Opsional: Relasi ke order tertentu untuk memastikan dia beneran beli
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');

            $table->integer('rating'); // Misal skala 1-5
            $table->text('comment');
            $table->string('image_path')->nullable(); // Path foto yang diupload user

            // STATUS MODERASI (Ini kuncinya)
            // 'pending': Baru disubmit, belum dicek AI
            // 'approved': Lolos cek AI, tampil di web
            // 'rejected': Ditolak AI
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Menyimpan alasan dari AI kenapa diterima/ditolak (untuk debugging admin)
            $table->text('ai_moderation_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};