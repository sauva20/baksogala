<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem; 
use Illuminate\Support\Facades\DB;   
use Illuminate\Support\Facades\Auth; 

class MenuController extends Controller
{
    public function index(Request $request)
    {
        // 1. LOGIKA QR CODE
        if ($request->has('meja')) {
            session(['table_number' => $request->query('meja')]);
        }
        if ($request->has('area')) {
            session(['dining_area' => $request->query('area')]);
        }
        $nomorMeja = session('table_number', null);

        // --- DEFINISI KATEGORI SIDE DISH ---
        $sideDishCategories = ['Tambahan', 'Side Dish', 'Topping']; 

        // 2. AMBIL SEMUA MENU UTAMA (TERMASUK SIDE DISH)
        // [PERBAIKAN]: Menghapus whereNotIn agar Side Dish bisa dibeli satuan
        $mainItems = MenuItem::where('is_available', 1)
            ->orderBy('category', 'asc')
            ->orderBy('is_favorite', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $menuGrouped = $mainItems->groupBy('category');

        // 3. AMBIL MENU TAMBAHAN (Hanya Untuk Checkbox di Modal)
        $sideDishes = MenuItem::where('is_available', 1)
            ->whereIn('category', $sideDishCategories) 
            ->orderBy('price', 'asc')
            ->get();

        // 4. HITUNG KERANJANG SAAT INI
        $userId = Auth::id();
        $sessionId = session()->getId();
        
        $cartItems = DB::table('cart_items')
            ->join('menu_items', 'cart_items.menu_item_id', '=', 'menu_items.id')
            ->select('cart_items.*', 'menu_items.price as menu_price')
            ->where(function($query) use ($userId, $sessionId) {
                $query->where('cart_items.session_id', $sessionId);
                if ($userId) {
                    $query->orWhere('cart_items.user_id', $userId);
                }
            })
            ->get();

        $currentQty = 0;
        $currentTotal = 0;
        
        $allAddons = MenuItem::whereIn('category', $sideDishCategories)->get()->keyBy('id');

        foreach ($cartItems as $item) {
            $price = $item->menu_price;
            
            if ($item->addons) {
                $addonIds = json_decode($item->addons, true);
                if (is_array($addonIds)) {
                    foreach ($addonIds as $id) {
                        if (isset($allAddons[$id])) {
                            $price += $allAddons[$id]->price;
                        }
                    }
                }
            }

            $currentQty += $item->quantity;
            $currentTotal += ($price * $item->quantity);
        }

        return view('menu.index', [
            'menuGrouped'  => $menuGrouped,
            'sideDishes'   => $sideDishes,
            'nomorMeja'    => $nomorMeja,
            'currentQty'   => $currentQty,
            'currentTotal' => $currentTotal
        ]);
    }
}