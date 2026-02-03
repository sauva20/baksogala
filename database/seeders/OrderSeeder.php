<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Pastikan ada user dulu
        if(User::count() == 0) {
            User::factory(5)->create();
        }
        
        $user = User::first();

        // Buat 10 transaksi dummy
        for ($i = 0; $i < 10; $i++) {
            Order::create([
                'user_id' => $user->id,
                'status' => 'completed',
                'total_price' => rand(50000, 200000), // Harga acak
                'payment_method' => 'cash',
                'created_at' => now()->subDays(rand(0, 7)) // Tanggal acak 7 hari terakhir
            ]);
        }
    }
}