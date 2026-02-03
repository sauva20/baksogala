<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Guarded kosong artinya SEMUA kolom di tabel orders boleh diisi.
    // Ini aman untuk menghindari error "Mass Assignment" saat create data.
    protected $guarded = []; 

    // --- RELASI PENTING ---

    /**
     * Relasi ke Detail Item (Menu yang dipesan)
     * Satu Order memiliki BANYAK OrderDetail.
     * Fungsi ini wajib ada agar halaman struk bisa menampilkan list menu.
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Relasi ke User (Pemesan)
     * Satu Order dimiliki oleh SATU User.
     * (Bisa null jika tamu/guest yang pesan).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * [BARU] Relasi ke Review (Ulasan)
     * Satu Order memiliki SATU Review.
     * Fungsi ini dibutuhkan di halaman 'Detail Order' untuk mengecek 
     * apakah user sudah memberi bintang atau belum.
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }
}