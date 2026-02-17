<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuItem; 

class CartController extends Controller
{
    private function getCartQuery()
    {
        if (Auth::check()) {
            return DB::table('cart_items')->where('user_id', Auth::id());
        } else {
            return DB::table('cart_items')->where('session_id', session()->getId());
        }
    }

    public function index()
    {
        // 1. Ambil item keranjang
        $cartItems = $this->getCartQuery()
            ->join('menu_items', 'cart_items.menu_item_id', '=', 'menu_items.id')
            ->select(
                'cart_items.*', 
                'menu_items.name as menu_name', 
                'menu_items.price as base_menu_price', 
                'menu_items.image_url',
                'menu_items.category' // Penting: Ambil kategori untuk filter charge
            )
            ->get();

        $subtotal = 0;
        $totalPackagingFee = 0; // Variabel total biaya bungkus
        $finalCartItems = []; 
        
        // Ambil info session (apakah take away?)
        $isTakeAway = session('dining_option') == 'take_away';

        // Daftar kategori/menu yang kena charge bungkus Rp 2.000
        // Sesuaikan string ini dengan data di database Anda (Case Insensitive nanti di logic)
        $packagingChargeCategories = ['Bakso', 'Bakmie', 'Wonton']; 
        $specificChargeMenus = ['Bakpau Telur Asin']; // Menu spesifik

        $allMenuItems = MenuItem::all()->keyBy('id');

        foreach ($cartItems as $item) {
            
            $currentPrice = $item->base_menu_price;
            $addonNames = [];

            // Hitung harga topping
            if (!empty($item->addons)) {
                $addonIds = json_decode($item->addons, true);
                if (is_array($addonIds)) {
                    foreach ($addonIds as $addonId) {
                        if (isset($allMenuItems[$addonId])) {
                            $currentPrice += $allMenuItems[$addonId]->price;
                            $addonNames[] = $allMenuItems[$addonId]->name;
                        }
                    }
                }
            }

            // --- LOGIKA BIAYA BUNGKUS (TAKE AWAY) ---
            $packagingFeePerItem = 0;
            
            if ($isTakeAway) {
                // Cek Kategori (Bakso, Bakmie, Wonton)
                // Menggunakan stripos agar tidak sensitif huruf besar/kecil
                $isCategoryMatch = false;
                foreach ($packagingChargeCategories as $cat) {
                    if (stripos($item->category, $cat) !== false) {
                        $isCategoryMatch = true;
                        break;
                    }
                }

                // Cek Nama Menu Spesifik (Bakpau Telur Asin)
                $isMenuMatch = false;
                foreach ($specificChargeMenus as $menuName) {
                    if (stripos($item->menu_name, $menuName) !== false) {
                        $isMenuMatch = true;
                        break;
                    }
                }

                if ($isCategoryMatch || $isMenuMatch) {
                    $packagingFeePerItem = 2000; // Charge Rp 2.000 per porsi
                    $totalPackagingFee += ($packagingFeePerItem * $item->quantity);
                }
            }

            // Hitung total baris (Harga Menu + Topping) * Qty
            $lineTotal = $currentPrice * $item->quantity;
            $subtotal += $lineTotal;

            $finalCartItems[] = (object) [
                'id' => $item->id, 
                'menu_name' => $item->menu_name,
                'image_url' => $item->image_url,
                'price_per_unit' => $currentPrice, 
                'quantity' => $item->quantity,
                'price' => $lineTotal, 
                'notes' => $item->notes,
                'addons_list' => implode(', ', $addonNames),
                'packaging_fee' => $packagingFeePerItem // Info untuk view jika mau ditampilkan per item
            ];
        }

        // Ambil Menu Rekomendasi
        $cartMenuIds = $cartItems->pluck('menu_item_id')->toArray();
        $relatedMenus = MenuItem::whereNotIn('id', $cartMenuIds)
            ->whereNotIn('category', ['Tambahan', 'Side Dish', 'Topping']) 
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return view('cart.index', compact('finalCartItems', 'subtotal', 'totalPackagingFee', 'relatedMenus'));
    }

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

        DB::table('cart_items')->insert([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'menu_item_id' => $request->menu_id,
            'quantity' => $request->quantity,
            'addons' => !empty($request->addons) ? json_encode($request->addons) : null,
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $newCount = $userId 
            ? DB::table('cart_items')->where('user_id', $userId)->sum('quantity')
            : DB::table('cart_items')->where('session_id', $sessionId)->sum('quantity');

        return response()->json([
            'status' => 'success', 
            'message' => 'Berhasil masuk keranjang!',
            'cart_count' => $newCount
        ]);
    }

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

    public function remove(Request $request)
    {
        $request->validate(['cart_id' => 'required']);

        $this->getCartQuery()
            ->where('id', $request->cart_id)
            ->delete();

        return response()->json(['status' => 'success', 'message' => 'Item dihapus!']);
    }

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

    public function saveInfo(Request $request)
    {
        $request->validate([
            'dining_option' => 'required|in:dine_in,take_away',
            'table_number' => 'nullable|numeric',
            'table_area' => 'nullable|string'
        ]);

        session([
            'dining_option' => $request->dining_option,
            'table_number' => $request->dining_option == 'dine_in' ? $request->table_number : null,
            'table_area' => $request->dining_option == 'dine_in' ? $request->table_area : null
        ]);

        return response()->json(['status' => 'success']);
    }
}