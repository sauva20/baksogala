<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. AKUN OWNER (Akses Full: Dashboard, Laporan, Menu, Pesanan, User)
        User::create([
            'name' => 'Owner Bakso Gala',
            'email' => 'owner@gala.com',
            'password' => Hash::make('gala123'), // Password
            'role' => 'owner', // Role Kunci
            'phone_number' => '081211111111',
        ]);

        // 2. AKUN KASIR (Akses Terbatas: Hanya Menu & Pesanan)
        User::create([
            'name' => 'Kasir Staff',
            'email' => 'kasir@gala.com',
            'password' => Hash::make('gala123'),
            'role' => 'kasir', // Role Terbatas
            'phone_number' => '081222222222',
        ]);

        // 3. AKUN PELANGGAN (Hanya untuk tes login di depan)

    }
}