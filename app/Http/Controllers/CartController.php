<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuItem; 
use Carbon\Carbon; // Wajib untuk logika jam operasional

class CartController extends Controller
{
    /**
     * Helper: Mengecek apakah toko sedang buka berdasarkan jadwal
     */
    private function isStoreOpen()
    {
        $now = Carbon::now('Asia/Jakarta');
        $day = $now->dayOfWeek; // 0 (Sun) - 6 (Sat)
        $time = $now->format('H:i');

        // 1. Senin: Libur
        if ($day === Carbon::MONDAY) {
            return false;
        }

        // 2. Selasa - Kamis & Minggu: 11.00 - 20.00
        if (in_array($day, [Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::THURSDAY, Carbon::SUNDAY])) {
            return ($time >= '11:00' && $time <= '20:00');
        }

        // 3. Jumat & Sabtu: 11.30 - 21.00
        if (in_array($day, [Carbon::FRIDAY, Carbon::SATURDAY])) {
            return ($time >= '11:30' && $time <= '21:00');
        }

        return false;
    }

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
        // Cek status buka toko
        $isOpen = $this->isStoreOpen();

        // 1. Ambil item keranjang
        $cartItems = $this->getCartQuery()
            ->join('menu_items', 'cart_items.menu_item_id', '=', 'menu_items.id')
            ->select(
                'cart_items.*', 
                'menu_items.name as menu_name', 
                'menu_items.price as base_menu_price', 
                'menu_items.image_url',
                'menu_items.category' 
            )
            ->get();

        $subtotal = 0;
        $totalPackagingFee = 0; 
        $finalCartItems = []; 
        
        $isTakeAway = session('dining_option') == 'take_away';
        $packagingChargeCategories = ['Bakso', 'Bakmie', 'Wonton']; 
        $specificChargeMenus = ['Bakpau Telur Asin'];

        $allMenuItems = MenuItem::all()->keyBy('id');

        foreach ($cartItems as $item) {
            $currentPrice = $item->base_menu_price;
            $addonNames = [];

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

            // LOGIKA BIAYA BUNGKUS
            $packagingFeePerItem = 0;
            if ($isTakeAway) {
                $isCategoryMatch = false;
                foreach ($packagingChargeCategories as $cat) {
                    if (stripos($item->category, $cat) !== false) {
                        $isCategoryMatch = true;
                        break;
                    }
                }

                $isMenuMatch = false;
                foreach ($specificChargeMenus as $menuName) {
                    if (stripos($item->menu_name, $menuName) !== false) {
                        $isMenuMatch = true;
                        break;
                    }
                }

                if ($isCategoryMatch || $isMenuMatch) {
                    $packagingFeePerItem = 2000; 
                    $totalPackagingFee += ($packagingFeePerItem * $item->quantity);
                }
            }

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
                'packaging_fee' => $packagingFeePerItem 
            ];
        }

        $cartMenuIds = $cartItems->pluck('menu_item_id')->toArray();
        $relatedMenus = MenuItem::whereNotIn('id', $cartMenuIds)
            ->whereNotIn('category', ['Tambahan', 'Side Dish', 'Topping']) 
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return view('cart.index', compact('finalCartItems', 'subtotal', 'totalPackagingFee', 'relatedMenus', 'isOpen'));
    }

    public function addToCart(Request $request)
    {
        // SECURITY CHECK: Blokir jika toko sedang tutup
        if (!$this->isStoreOpen()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maaf, pemesanan sedang tutup. Silakan cek jam operasional kami.'
            ], 403);
        }

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