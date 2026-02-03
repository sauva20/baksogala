<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin; // Pastikan Model Admin sudah ada
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan Role "Super Admin" ada dulu (jika pakai tabel roles)
        // Cek apakah role id 1 sudah ada, kalau belum insert manual
        $roleExists = DB::table('roles')->where('id', 1)->exists();
        if (!$roleExists) {
            DB::table('roles')->insert([
                'id' => 1,
                'role_name' => 'Super Admin'
            ]);
        }

        // 2. Buat Akun Admin
        // Kita pakai updateOrCreate agar tidak error jika dijalankan berulang
        Admin::updateOrCreate(
            ['username' => 'admin'], // Cek berdasarkan username
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('password123'), // Password di-hash (dienkripsi)
                'role_id' => 1
            ]
        );
    }
}