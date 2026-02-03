<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    // --- OPSI 1: Paling Mudah (Direkomendasikan) ---
    // protected $guarded = []; // Artinya: Semua kolom boleh diisi (kecuali id)

    // --- OPSI 2: Strict Mode (Sesuai kode Anda) ---
    protected $fillable = [
        'order_id',
        'rating',
        'comment',
        'photo', // Wajib sama dengan nama di database (sebelumnya image_path)
        // 'status', 'ai_moderation_reason' // (Hanya nyalakan jika kolom ini SUDAH ADA di database)
    ];

    // 1. Relasi ke Order (Penting)
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // 2. Relasi ke User 
    // (Tips: Karena tabel reviews tidak punya user_id, kita ambil user lewat order)
    public function user()
    {
        // Akses user melalui relasi order
        return $this->hasOneThrough(User::class, Order::class, 'id', 'id', 'order_id', 'user_id');
    }
}