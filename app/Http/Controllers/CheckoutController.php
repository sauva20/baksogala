<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        Config::$isProduction = config('midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // Helper untuk mengambil keranjang (Support Login & Tamu)
    private function getCartItems()
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        return DB::table('cart_items')
            ->join('menu_items', 'cart_items.menu_item_id', '=', 'menu_items.id')
            ->select(
                'cart_items.*', 
                'menu_items.name as menu_name', 
                'menu_items.price as menu_price',
                'menu_items.image_url'
            )
            ->where(function($query) use ($userId, $sessionId) {
                $query->where('cart_items.session_id', $sessionId);
                if ($userId) {
                    $query->orWhere('cart_items.user_id', $userId);
                }
            })
            ->get();
    }

    public function index()
    {
        $rawCartItems = $this->getCartItems();

        if ($rawCartItems->isEmpty()) {
            return redirect()->route('menu.index')->with('error', 'Keranjang kosong.');
        }

        $grandTotal = 0;
        $cartItems = [];
        $allAddons = MenuItem::where('category', 'Tambahan')->get()->keyBy('id');

        foreach ($rawCartItems as $item) {
            $unitPrice = $item->menu_price;
            $addonNames = [];

            if ($item->addons) {
                $addonIds = json_decode($item->addons, true);
                if (is_array($addonIds)) {
                    foreach ($addonIds as $addonId) {
                        if (isset($allAddons[$addonId])) {
                            $unitPrice += $allAddons[$addonId]->price;
                            $addonNames[] = $allAddons[$addonId]->name;
                        }
                    }
                }
            }

            $subtotal = $unitPrice * $item->quantity;
            $grandTotal += $subtotal;

            $cartItems[] = (object) [
                'id' => $item->id,
                'menu_name' => $item->menu_name,
                'quantity' => $item->quantity,
                'price_per_unit' => $unitPrice,
                'total_price' => $subtotal,
                'addons_list' => implode(', ', $addonNames),
                'notes' => $item->notes,
                'image_url' => $item->image_url
            ];
        }

        // Ambil Data Session (Hasil Scan QR)
        $scannedTable = session('table_number', null);
        $scannedArea  = session('dining_area', null); // Ambil lokasi juga

        return view('checkout.index', compact('cartItems', 'grandTotal', 'scannedTable', 'scannedArea'));
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'dining_option' => 'required|in:dine_in,take_away',
            'table_number' => 'required_if:dining_option,dine_in',
            'dining_area' => 'required_if:dining_option,dine_in',
        ]);

        DB::beginTransaction();
        try {
            // 2. Ambil Keranjang
            $rawCartItems = $this->getCartItems();

            if ($rawCartItems->isEmpty()) {
                return redirect()->back()->with('error', 'Gagal: Keranjang kosong atau sesi kadaluarsa. Silakan refresh.');
            }

            // 3. Hitung Total (Backend Calculation)
            $calculatedTotal = 0;
            $allAddons = MenuItem::where('category', 'Tambahan')->get()->keyBy('id');

            foreach ($rawCartItems as $cItem) {
                $uPrice = $cItem->menu_price;
                if ($cItem->addons) {
                    $aIds = json_decode($cItem->addons, true);
                    if (is_array($aIds)) {
                        foreach ($aIds as $id) {
                            if (isset($allAddons[$id])) $uPrice += $allAddons[$id]->price;
                        }
                    }
                }
                $calculatedTotal += $uPrice * $cItem->quantity;
            }

            // --- PERHITUNGAN BIAYA LAYANAN (0.7%) ---
            $appFee = $calculatedTotal * 0.007; 
            $finalTotal = ceil($calculatedTotal + $appFee); // Bulatkan ke atas

            // 4. Handle User (Auto Register & Login Tamu)
            $user = null;
            if (Auth::check()) {
                $user = Auth::user();
                if (empty($user->phone_number)) {
                    $user->update(['phone_number' => $request->customer_phone]);
                }
            } else {
                // Cari user berdasarkan No HP
                $user = User::where('phone_number', $request->customer_phone)->first();
                if (!$user) {
                    // Buat User Baru
                    $user = User::create([
                        'name' => $request->customer_name,
                        'phone_number' => $request->customer_phone,
                        'email' => null,
                        'password' => Hash::make('gala123'), // Password Default
                        'role' => 'customer'
                    ]);
                }
                // LOGIN KAN USER SECARA OTOMATIS
                Auth::login($user);
            }

            // 5. Simpan Order
            $order = new Order();
            $order->user_id = $user->id;
            $order->customer_name = $request->customer_name;
            $order->customer_phone = $request->customer_phone;
            $order->customer_email = $request->customer_email;
            
            if ($request->dining_option == 'dine_in') {
                $order->order_type = 'Dine In';
                // Gabungkan Area dan Nomor Meja
                $order->shipping_address = $request->dining_area . ' - Meja ' . $request->table_number;
            } else {
                $order->order_type = 'Take Away';
                $order->shipping_address = 'Take Away (Bungkus)';
            }

            $order->total_price = $finalTotal;
            $order->status = 'new';
            $order->payment_method = 'midtrans';
            $order->payment_status = 'pending';
            $order->order_notes = $request->order_notes;
            $order->save();

            // 6. Simpan Detail Item
            foreach ($rawCartItems as $item) {
                $detail = new OrderDetail();
                $detail->order_id = $order->id;
                $detail->menu_item_id = $item->menu_item_id;
                $detail->quantity = $item->quantity;
                
                $unitPrice = $item->menu_price;
                $addonNames = [];

                if ($item->addons) {
                    $addonIds = json_decode($item->addons, true);
                    if (is_array($addonIds)) {
                        foreach ($addonIds as $addonId) {
                            if (isset($allAddons[$addonId])) {
                                $unitPrice += $allAddons[$addonId]->price;
                                $addonNames[] = $allAddons[$addonId]->name;
                            }
                        }
                    }
                }

                $detail->price = $unitPrice;
                $detail->subtotal = $unitPrice * $item->quantity;
                
                $noteString = $item->notes ?? '';
                if(!empty($addonNames)) {
                    $noteString .= (empty($noteString) ? '' : ' | ') . "Topping: " . implode(", ", $addonNames);
                }
                $detail->item_notes = $noteString;
                $detail->save();
            }

            // 7. Snap Token Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $order->id . '-' . time(),
                    'gross_amount' => (int) $finalTotal,
                ],
                'customer_details' => [
                    'first_name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                ],
                // 👇 KHUSUS QRIS (Langsung muncul QR tanpa pilih menu) 👇
                'enabled_payments' => ['other_qris'],
            ];
            
            $snapToken = Snap::getSnapToken($params);
            $order->snap_token = $snapToken;
            $order->save();

            // 8. Hapus Keranjang & Session
            DB::table('cart_items')->where('user_id', $user->id)->delete();
            session()->forget(['table_number', 'dining_area']); // Hapus session meja & area

            DB::commit();

            // Redirect ke Halaman Pembayaran (Order Detail)
            return redirect()->route('orders.show', $order->id)->with('success', 'Pesanan dibuat! Silakan bayar.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}