<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuItem; // Pastikan Model ini sesuai dengan file Anda

class CartController extends Controller
{
    /**
     * Helper Private: Mendapatkan Query Builder untuk user saat ini
     * (Otomatis memilih antara User Login atau Tamu via Session)
     */
    private function getCartQuery()
    {
        if (Auth::check()) {
            return DB::table('cart_items')->where('user_id', Auth::id());
        } else {
            return DB::table('cart_items')->where('session_id', session()->getId());
        }
    }

    /**
     * Menampilkan halaman keranjang belanja.
     */
    public function index()
    {
        // 1. Ambil item keranjang join dengan menu_items
        $cartItems = $this->getCartQuery()
            ->join('menu_items', 'cart_items.menu_item_id', '=', 'menu_items.id')
            ->select(
                'cart_items.*', 
                'menu_items.name as menu_name', 
                'menu_items.price as base_menu_price', // Harga dasar menu
                'menu_items.image_url'
            )
            ->get();

        $subtotal = 0;
        $finalCartItems = []; 

        // 2. Ambil semua data topping untuk lookup nama & harga
        // Kita ambil semua menu agar aman, atau bisa filter kategori 'Tambahan'/'Side Dish'
        $allMenuItems = MenuItem::all()->keyBy('id');

        // 3. Loop item untuk menyusun tampilan & hitung harga total
        foreach ($cartItems as $item) {
            
            // Mulai dengan harga dasar menu
            $currentPrice = $item->base_menu_price;
            $addonNames = [];

            // Cek apakah ada topping yang tersimpan (format JSON di database)
            if (!empty($item->addons)) {
                $addonIds = json_decode($item->addons, true);
                
                if (is_array($addonIds)) {
                    foreach ($addonIds as $addonId) {
                        // Cek apakah ID topping ada di database menu
                        if (isset($allMenuItems[$addonId])) {
                            // Tambahkan harga topping ke harga satuan
                            $currentPrice += $allMenuItems[$addonId]->price;
                            // Simpan nama topping untuk ditampilkan di view
                            $addonNames[] = $allMenuItems[$addonId]->name;
                        }
                    }
                }
            }

            // Hitung total per baris (Harga Satuan Akhir * Jumlah)
            $lineTotal = $currentPrice * $item->quantity;
            $subtotal += $lineTotal;

            // Format data object untuk dikirim ke View
            $finalCartItems[] = (object) [
                'id' => $item->id, 
                'menu_name' => $item->menu_name,
                'image_url' => $item->image_url,
                'price_per_unit' => $currentPrice, // Harga Satuan (Menu + Topping)
                'quantity' => $item->quantity,
                'price' => $lineTotal, // Total harga item ini
                'notes' => $item->notes,
                'addons_list' => implode(', ', $addonNames), // String nama topping (Ceker, Tetelan)
                'addons' => $item->addons // Raw JSON (untuk keperluan teknis jika butuh)
            ];
        }

        // 4. Ambil Menu Rekomendasi (Related Menus)
        // Ambil 5 menu acak selain yang ada di keranjang & bukan topping
        $cartMenuIds = $cartItems->pluck('menu_item_id')->toArray();
        $relatedMenus = MenuItem::whereNotIn('id', $cartMenuIds)
            ->whereNotIn('category', ['Tambahan', 'Side Dish', 'Topping']) // Hindari rekomendasi topping
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return view('cart.index', compact('finalCartItems', 'subtotal', 'relatedMenus'));
    }

    /**
     * Menambahkan item ke keranjang (AJAX).
     */
    public function addToCart(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'menu_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1',
            'addons' => 'nullable|array', // Harus array ID
            'notes' => 'nullable|string'
        ]);

        $userId = Auth::id();
        $sessionId = session()->getId();

        // 2. Masukkan ke Database
        // Kita tidak perlu menghitung harga di sini untuk disimpan ke kolom price,
        // karena harga bisa berubah. Kita hitung dinamis di fungsi index().
        // Tapi pastikan ID topping tersimpan sebagai JSON.
        
        DB::table('cart_items')->insert([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'menu_item_id' => $request->menu_id,
            'quantity' => $request->quantity,
            // Simpan ID Topping sebagai JSON String
            'addons' => !empty($request->addons) ? json_encode($request->addons) : null,
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. HITUNG ULANG Total Item untuk Badge Navbar (Realtime update)
        $newCount = 0;
        if ($userId) {
            $newCount = DB::table('cart_items')->where('user_id', $userId)->sum('quantity');
        } else {
            $newCount = DB::table('cart_items')->where('session_id', $sessionId)->sum('quantity');
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Berhasil masuk keranjang!',
            'cart_count' => $newCount
        ]);
    }

    /**
     * Update Quantity di Halaman Keranjang
     */
    public function update(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $this->getCartQuery()
            ->where('id', $request->cart_id)
            ->update(['quantity' => $request->quantity, 'updated_at' => now()]);

        return response()->json(['status' => 'success', 'message' => 'Jumlah diperbarui!']);
    }

    /**
     * Hapus Item
     */
    public function remove(Request $request)
    {
        $request->validate(['cart_id' => 'required']);

        $this->getCartQuery()
            ->where('id', $request->cart_id)
            ->delete();

        return response()->json(['status' => 'success', 'message' => 'Item dihapus!']);
    }

    /**
     * Update Catatan Item (AJAX)
     */
    public function updateNote(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|exists:cart_items,id',
            'note' => 'nullable|string|max:255'
        ]);

        $this->getCartQuery()
            ->where('id', $request->cart_id)
            ->update([
                'notes' => $request->note,
                'updated_at' => now()
            ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Simpan Info Meja & Tipe Pesanan ke Session (AJAX)
     * Dipanggil saat user memilih Dine In/Take Away di halaman Keranjang
     */
/**
     * Simpan Info Meja, Area & Tipe Pesanan
     */
    public function saveInfo(Request $request)
    {
        // Validasi input
        $request->validate([
            'dining_option' => 'required|in:dine_in,take_away',
            'table_number'  => 'nullable|numeric',
            'table_area'    => 'nullable|string' // Tambahkan validasi Area
        ]);

        // Simpan ke Session
        session([
            'dining_option' => $request->dining_option,
            // Jika take away, meja & area dikosongkan
            'table_number'  => $request->dining_option == 'dine_in' ? $request->table_number : null,
            'table_area'    => $request->dining_option == 'dine_in' ? $request->table_area : null
        ]);

        return response()->json(['status' => 'success']);
    }
}