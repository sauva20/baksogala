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
use Illuminate\Support\Facades\Http; // Wajib untuk nembak API Firebase & Telegram
use Midtrans\Config;
use Midtrans\Snap;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        Config::$isProduction = config('midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // Helper untuk mengambil item keranjang
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
                'menu_items.image_url',
                'menu_items.category'
            )
            ->where(function($query) use ($userId, $sessionId) {
                $query->where('cart_items.session_id', $sessionId);
                if ($userId) {
                    $query->orWhere('cart_items.user_id', $userId);
                }
            })
            ->get();
    }

    // Menampilkan Halaman Checkout
    public function index()
    {
        $rawCartItems = $this->getCartItems();

        if ($rawCartItems->isEmpty()) {
            return redirect()->route('menu.index')->with('error', 'Keranjang kosong.');
        }

        $grandTotal = 0; 
        $totalPackagingFee = 0; 
        $cartItems = [];
        $collectedNotes = [];
        
        $allAddons = MenuItem::whereIn('category', ['Tambahan', 'Side Dish', 'Topping'])->get()->keyBy('id');
        
        $packagingChargeCategories = ['Bakso', 'Bakmie', 'Wonton']; 
        $specificChargeMenus = ['Bakpau Telur Asin'];

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

            $isCategoryMatch = false;
            foreach ($packagingChargeCategories as $cat) {
                if (stripos($item->category, $cat) !== false) {
                    $isCategoryMatch = true; break;
                }
            }
            $isMenuMatch = false;
            foreach ($specificChargeMenus as $menuName) {
                if (stripos($item->menu_name, $menuName) !== false) {
                    $isMenuMatch = true; break;
                }
            }

            if ($isCategoryMatch || $isMenuMatch) {
                $totalPackagingFee += (2000 * $item->quantity);
            }

            $subtotal = $unitPrice * $item->quantity;
            $grandTotal += $subtotal;

            if (!empty($item->notes)) {
                $collectedNotes[] = $item->notes; 
            }

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

        $compiledNotes = implode(", ", $collectedNotes);
        $scannedTable = session('table_number', null);
        $scannedArea  = session('table_area', null);

        return view('checkout.index', compact('cartItems', 'grandTotal', 'totalPackagingFee', 'scannedTable', 'scannedArea', 'compiledNotes'));
    }

    // Memproses Pesanan
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => [
                'required', 
                'numeric', 
                'starts_with:08', 
                'digits_between:10,13'
            ],
            'dining_option' => 'required|in:dine_in,take_away',
            'table_number' => 'required_if:dining_option,dine_in',
            'dining_area' => 'required_if:dining_option,dine_in',
        ], [
            'customer_phone.starts_with' => 'Nomor WhatsApp harus diawali dengan 08.',
            'customer_phone.digits_between' => 'Nomor WhatsApp harus antara 10 s/d 13 digit.',
            'customer_phone.numeric' => 'Nomor WhatsApp harus berupa angka.',
        ]);

        DB::beginTransaction();
        try {
            $rawCartItems = $this->getCartItems();

            if ($rawCartItems->isEmpty()) {
                return redirect()->route('menu.index')->with('error', 'Gagal: Keranjang kosong.');
            }

            $calculatedTotal = 0;
            $allMenuItems = MenuItem::all()->keyBy('id'); 
            $isTakeAway = $request->dining_option == 'take_away';
            
            foreach ($rawCartItems as $cItem) {
                $uPrice = $cItem->menu_price;
                if ($cItem->addons) {
                    $aIds = json_decode($cItem->addons, true);
                    if (is_array($aIds)) {
                        foreach ($aIds as $id) {
                            if (isset($allMenuItems[$id])) {
                                $uPrice += $allMenuItems[$id]->price;
                            }
                        }
                    }
                }

                if ($isTakeAway) {
                    $isCategoryMatch = false;
                    foreach (['Bakso', 'Bakmie', 'Wonton'] as $cat) {
                        if (stripos($cItem->category, $cat) !== false) {
                            $isCategoryMatch = true; break;
                        }
                    }
                    if ($isCategoryMatch || stripos($cItem->menu_name, 'Bakpau Telur Asin') !== false) {
                        $uPrice += 2000;
                    }
                }
                $calculatedTotal += $uPrice * $cItem->quantity;
            }

            $appFee = $calculatedTotal * 0.007; 
            $finalTotal = ceil($calculatedTotal + $appFee);

            if (Auth::check()) {
                $user = Auth::user();
                if (empty($user->phone_number)) $user->update(['phone_number' => $request->customer_phone]);
            } else {
                $user = User::firstOrCreate(
                    ['phone_number' => $request->customer_phone],
                    ['name' => $request->customer_name, 'password' => Hash::make('gala123'), 'role' => 'customer']
                );
                Auth::login($user);
            }

            $order = new Order();
            $order->user_id = $user->id;
            $order->customer_name = $request->customer_name;
            $order->customer_phone = $request->customer_phone;
            $order->order_type = ($request->dining_option == 'dine_in') ? 'Dine In' : 'Take Away';
            $order->shipping_address = ($request->dining_option == 'dine_in') ? $request->dining_area . ' - Meja ' . $request->table_number : 'Take Away (Bungkus)';
            $order->total_price = $finalTotal;
            $order->status = 'new';
            $order->payment_method = 'midtrans';
            $order->payment_status = 'pending';
            $order->order_notes = $request->order_notes;
            $order->save();

            // --- NOTIFIKASI DIHAPUS DARI SINI ---
            // Agar tidak bunyi saat orang baru checkout (belum bayar)

            foreach ($rawCartItems as $item) {
                $detail = new OrderDetail();
                $detail->order_id = $order->id;
                $detail->menu_item_id = $item->menu_item_id;
                $detail->quantity = $item->quantity;
                
                $uPrice = $item->menu_price;
                $addonNamesArray = [];

                if ($item->addons) {
                    $aIds = json_decode($item->addons, true);
                    if (is_array($aIds)) {
                        foreach ($aIds as $id) {
                            if (isset($allMenuItems[$id])) {
                                $uPrice += $allMenuItems[$id]->price; 
                                $addonNamesArray[] = $allMenuItems[$id]->name; 
                            }
                        }
                    }
                }

                if ($isTakeAway && (stripos($item->category, 'Bakso') !== false || stripos($item->menu_name, 'Bakpau') !== false)) {
                    $uPrice += 2000;
                }

                $detail->price = $uPrice;
                $detail->subtotal = $uPrice * $item->quantity;
                
                $finalNote = $item->notes ?? '';
                if (!empty($addonNamesArray)) {
                    $addonString = "Topping: " . implode(", ", $addonNamesArray);
                    $finalNote = empty($finalNote) ? $addonString : $finalNote . " | " . $addonString;
                }

                $detail->item_notes = $finalNote;
                $detail->save();
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $order->id . '-' . time(),
                    'gross_amount' => (int) $finalTotal,
                ],
                'customer_details' => [
                    'first_name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                ],
                'enabled_payments' => ['other_qris'], 
            ];
            
            $snapToken = Snap::getSnapToken($params);
            $order->snap_token = $snapToken;
            $order->save();

            DB::table('cart_items')->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('session_id', session()->getId());
            })->delete();
            
            session()->forget(['table_number', 'table_area']); 

            DB::commit();
            return redirect()->route('orders.show', $order->id)->with('success', 'Pesanan dibuat! Silakan lakukan pembayaran.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * FUNGSI 1: KIRIM TELEGRAM BOT
     * (Panggil fungsi ini di Payment Callback saat status = settlement)
     */
    private function sendTelegramNotif($order)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$token || !$chatId) return;

        $message = "💰 *PESANAN LUNAS!* 💰\n\n";
        $message .= "🆔 *Order ID:* #{$order->id}\n";
        $message .= "👤 *Pelanggan:* {$order->customer_name}\n";
        $message .= "📍 *Lokasi:* {$order->shipping_address}\n";
        $message .= "💰 *Total:* Rp " . number_format($order->total_price, 0, ',', '.') . "\n\n";
        $message .= "✅ *Segera proses pesanan di dapur!*";

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    /**
     * FUNGSI 2: KIRIM FIREBASE (WEB PUSH)
     * (Panggil fungsi ini di Payment Callback saat status = settlement)
     */
    private function sendNotificationToAdmin($order)
    {
        $tokens = User::whereIn('role', ['owner', 'admin'])
                      ->whereNotNull('fcm_token')
                      ->pluck('fcm_token')
                      ->toArray();

        if (empty($tokens)) return;

        $serverKey = env('FIREBASE_SERVER_KEY');
        $url = 'https://fcm.googleapis.com/fcm/send';

        $payload = [
            "registration_ids" => $tokens,
            "notification" => [
                "title" => "💰 PESANAN LUNAS!",
                "body" => "Order #{$order->id} dari {$order->customer_name} SUDAH DIBAYAR. Cek sekarang!",
                "icon" => asset('assets/images/GALA.png'),
                "sound" => "default",
                "click_action" => url('/admin/orders')
            ],
            "data" => [
                "order_id" => $order->id
            ],
            "priority" => "high",
            "content_available" => true
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);
    }
}