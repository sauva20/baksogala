<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuItem; // Pastikan model ini ada

class CartController extends Controller
{
    /**
     * Helper Private: Mendapatkan Query Builder untuk user saat ini
     * (Otomatis memilih antara User Login atau Tamu)
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
                'menu_items.price as menu_price', 
                'menu_items.image_url'
            )
            ->get();

        $subtotal = 0;
        $finalCartItems = []; 

        // 2. Ambil harga Side Dishes untuk perhitungan
        $allAddons = MenuItem::where('category', 'Tambahan')->get()->keyBy('id');

        // 3. Loop item untuk hitung total (Menu Utama + Addons)
        foreach ($cartItems as $item) {
            $itemTotal = $item->menu_price;
            $addonNames = [];

            if ($item->addons) {
                $addonIds = json_decode($item->addons, true);
                if (is_array($addonIds)) {
                    foreach ($addonIds as $addonId) {
                        if (isset($allAddons[$addonId])) {
                            $itemTotal += $allAddons[$addonId]->price;
                            $addonNames[] = $allAddons[$addonId]->name;
                        }
                    }
                }
            }

            $subtotal += $itemTotal * $item->quantity;

            $finalCartItems[] = (object) [
                'id' => $item->id, 
                'menu_name' => $item->menu_name,
                'image_url' => $item->image_url,
                'price_per_unit' => $itemTotal,
                'quantity' => $item->quantity,
                'total_price' => $itemTotal * $item->quantity,
                'notes' => $item->notes,
                'addons_list' => implode(', ', $addonNames)
            ];
        }

        // [BARU] 4. Ambil Menu Rekomendasi (Related Menus)
        // Ambil 5 menu secara acak selain yang ada di keranjang
        $cartMenuIds = $cartItems->pluck('menu_item_id')->toArray();
        $relatedMenus = MenuItem::whereNotIn('id', $cartMenuIds)
            ->where('category', '!=', 'Tambahan') // Jangan rekomendasikan topping
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
        $request->validate([
            'menu_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1',
            'addons' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $userId = Auth::id();
        $sessionId = session()->getId();

        // 1. Masukkan ke Database
        DB::table('cart_items')->insert([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'menu_item_id' => $request->menu_id,
            'quantity' => $request->quantity,
            'addons' => json_encode($request->addons ?? []),
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. HITUNG ULANG Total Item untuk Badge Navbar
        $newCount = 0;
        if ($userId) {
            $newCount = DB::table('cart_items')->where('user_id', $userId)->sum('quantity');
        } else {
            $newCount = DB::table('cart_items')->where('session_id', $sessionId)->sum('quantity');
        }

        // 3. Kembalikan Response JSON termasuk cart_count terbaru
        return response()->json([
            'status' => 'success', 
            'message' => 'Berhasil masuk keranjang!',
            'cart_count' => $newCount // Data ini akan dipakai Javascript untuk update navbar
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
     * [BARU] Update Catatan Item (AJAX)
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
     * [BARU] Simpan Info Meja & Tipe Pesanan ke Session (AJAX)
     */
    public function saveInfo(Request $request)
    {
        // Validasi input
        $request->validate([
            'dining_option' => 'required|in:dine_in,take_away',
            'table_number'  => 'nullable|numeric'
        ]);

        // Simpan ke Session Laravel agar bisa diambil saat Checkout
        session([
            'dining_option' => $request->dining_option,
            'table_number'  => $request->dining_option == 'dine_in' ? $request->table_number : null
        ]);

        return response()->json(['status' => 'success']);
    }
}