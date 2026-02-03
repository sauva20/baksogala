<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun OWNER (Segalanya)
        User::create([
            'name' => 'rafi',
            'email' => 'owner@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'owner',
        ]);

        // 2. Akun ADMIN (Manajemen Menu/Laporan)
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]);

        // 3. Akun KASIR (POS / Transaksi)
        User::create([
            'name' => 'Kasir',
            'email' => 'kasir@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'kasir',
        ]);

        // 4. Akun CUSTOMER (Pelanggan)
        User::create([
            'name' => 'Pelanggan',
            'email' => 'user@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'customer',
        ]);
        
        // Opsional: Buat data dummy lainnya (Order, Menu, dll) jika perlu
        // $this->call(OrderSeeder::class);
    }
}