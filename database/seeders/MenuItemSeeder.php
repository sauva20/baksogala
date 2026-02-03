<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            // --- BAKSO SOUN ---
            ['name' => 'Bakso Soun Urat & Daging', 'price' => 25000, 'category' => 'makanan', 'is_favorite' => true],
            ['name' => 'Bakso Soun Urat', 'price' => 25000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakso Soun Daging', 'price' => 23000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakso Soun Urat & Daging (Tanpa Tetelan)', 'price' => 21000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakso Soun Urat (Tanpa Tetelan)', 'price' => 21000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakso Soun Daging (Tanpa Tetelan)', 'price' => 19000, 'category' => 'makanan', 'is_favorite' => false],

            // --- BAKMIE ---
            ['name' => 'Bakmie Sapi Sambal Matah', 'price' => 27000, 'category' => 'makanan', 'is_favorite' => true],
            ['name' => 'Bakmie Sapi Sambal Matah (JUMBO)', 'price' => 52000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakmie Ayam Sambal Matah', 'price' => 21000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakmie Ayam Sambal Matah (JUMBO)', 'price' => 41000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakmie Ayam Asap', 'price' => 21000, 'category' => 'makanan', 'is_favorite' => true],
            ['name' => 'Bakmie Ayam Asap (JUMBO)', 'price' => 41000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakmie Ayam Polos', 'price' => 18000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakmie Ayam Polos (JUMBO)', 'price' => 35000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakmie Polos', 'price' => 15000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Bakmie Polos (JUMBO)', 'price' => 30000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Mie Ayam Ala Gala', 'price' => 16000, 'category' => 'makanan', 'is_favorite' => true],
            ['name' => 'Mie Ayam Ala Gala (JUMBO)', 'price' => 30000, 'category' => 'makanan', 'is_favorite' => false],

            // --- SIDE DISH ---
            ['name' => 'Pangsit Goreng Ayam (Isi 3)', 'price' => 10000, 'category' => 'makanan', 'is_favorite' => true],
            ['name' => 'Pangsit Goreng Ayam (Isi 1)', 'price' => 4000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Tetelan', 'price' => 7000, 'category' => 'makanan', 'is_favorite' => true],
            ['name' => 'Bakso Urat (Satuan)', 'price' => 5000, 'category' => 'makanan', 'is_favorite' => true],
            ['name' => 'Bakso Daging (Satuan)', 'price' => 4000, 'category' => 'makanan', 'is_favorite' => false],
            ['name' => 'Kriuk-an Ala Gala', 'price' => 5000, 'category' => 'makanan', 'is_favorite' => true],

            // --- DRINKS ---
            ['name' => 'Es Serut Gala Merah', 'price' => 10000, 'category' => 'minuman', 'is_favorite' => true],
            ['name' => 'Es Serut Gala Hijau', 'price' => 7000, 'category' => 'minuman', 'is_favorite' => false],
            ['name' => 'Es Serut Gala Merah & Hijau', 'price' => 5000, 'category' => 'minuman', 'is_favorite' => true],
            ['name' => 'Teh Botol + Es', 'price' => 7000, 'category' => 'minuman', 'is_favorite' => false],
            ['name' => 'Teh Botol', 'price' => 5000, 'category' => 'minuman', 'is_favorite' => false],
            ['name' => 'Es Teh Manis', 'price' => 8000, 'category' => 'minuman', 'is_favorite' => false],
            ['name' => 'Es Teh Tawar', 'price' => 5000, 'category' => 'minuman', 'is_favorite' => false],
            ['name' => 'Teh Manis Hangat', 'price' => 6000, 'category' => 'minuman', 'is_favorite' => false],
            ['name' => 'Teh Tawar Hangat', 'price' => 5000, 'category' => 'minuman', 'is_favorite' => false],
            ['name' => 'Air Mineral', 'price' => 6000, 'category' => 'minuman', 'is_favorite' => false],
        ];

        foreach ($menus as $menu) {
            DB::table('menu_items')->insert([
                'name' => $menu['name'],
                'description' => 'Menu spesial Bakso Gala',
                'price' => $menu['price'],
                'category' => $menu['category'],
                'image_url' => 'assets/images/placeholder.jpg', // Ganti nanti
                'is_available' => true,
                'is_favorite' => $menu['is_favorite'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}