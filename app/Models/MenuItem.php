<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'menu_items';

    // PENTING: Daftar kolom yang BOLEH diisi secara massal
    protected $fillable = [
        'name',
        'description',
        'price',
        'image_url',
        'category',
        'is_available',
        'is_favorite',
        'show_on_homepage',
    ];

    // Casting tipe data (opsional, biar otomatis jadi boolean/integer saat diambil)
    protected $casts = [
        'price' => 'integer',
        'is_available' => 'boolean',
        'is_favorite' => 'boolean',
        'show_on_homepage' => 'boolean',
    ];
}