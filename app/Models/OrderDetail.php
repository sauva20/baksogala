<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    // Guarded kosong agar mudah create data (Mass Assignment)
    protected $guarded = [];

    // --- RELASI PENTING ---

    /**
     * Relasi ke Menu Item
     * Setiap detail pesanan pasti merujuk ke SATU menu makanan/minuman.
     * Fungsi ini WAJIB ada agar kita bisa mengambil Nama Menu & Gambar di halaman struk.
     */
    public function menuItem()
    {
        // Parameter ke-2 'menu_item_id' memastikan Laravel membaca kolom yang benar
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    /**
     * Relasi balik ke Order Utama
     * (Opsional, tapi berguna jika ingin cek detail ini milik order nomor berapa)
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}