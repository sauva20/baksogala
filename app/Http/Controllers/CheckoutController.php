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
        Config::$serverKey = config('midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        Config::$isProduction = config('midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

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
        $collectedNotes = []; // Array penampung catatan
        
        $allAddons = MenuItem::whereIn('category', ['Tambahan', 'Side Dish', 'Topping'])->get()->keyBy('id');

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

            // --- PERBAIKAN DI SINI ---
            // Hanya mengambil isi catatan, TANPA nama menu
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

        // Gabungkan catatan dengan koma
        $compiledNotes = implode(", ", $collectedNotes);

        $scannedTable = session('table_number', null);
        $scannedArea  = session('table_area', null);

        return view('checkout.index', compact('cartItems', 'grandTotal', 'scannedTable', 'scannedArea', 'compiledNotes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'dining_option' => 'required|in:dine_in,take_away',
            'table_number' => 'required_if:dining_option,dine_in',
            'dining_area' => 'required_if:dining_option,dine_in',
        ]);

        DB::beginTransaction();
        try {
            $rawCartItems = $this->getCartItems();

            if ($rawCartItems->isEmpty()) {
                return redirect()->route('menu.index')->with('error', 'Gagal: Keranjang kosong.');
            }

            $calculatedTotal = 0;
            $allMenuItems = MenuItem::all()->keyBy('id'); 

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
                $calculatedTotal += $uPrice * $cItem->quantity;
            }

            $appFee = $calculatedTotal * 0.007; 
            $finalTotal = ceil($calculatedTotal + $appFee);

            $user = null;
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
            
            if ($request->dining_option == 'dine_in') {
                $order->order_type = 'Dine In';
                $order->shipping_address = $request->dining_area . ' - Meja ' . $request->table_number;
            } else {
                $order->order_type = 'Take Away';
                $order->shipping_address = 'Take Away (Bungkus)';
            }

            $order->total_price = $finalTotal;
            $order->status = 'new';
            $order->payment_method = 'midtrans';
            $order->payment_status = 'pending';
            
            // Simpan catatan global yang mungkin sudah diedit user di checkout
            $order->order_notes = $request->order_notes;
            
            $order->save();

            foreach ($rawCartItems as $item) {
                $detail = new OrderDetail();
                $detail->order_id = $order->id;
                $detail->menu_item_id = $item->menu_item_id;
                $detail->quantity = $item->quantity;
                
                $unitPrice = $item->menu_price;
                $addonNamesArray = [];

                if ($item->addons) {
                    $addonIds = json_decode($item->addons, true);
                    if (is_array($addonIds)) {
                        foreach ($addonIds as $addonId) {
                            if (isset($allMenuItems[$addonId])) {
                                $unitPrice += $allMenuItems[$addonId]->price; 
                                $addonNamesArray[] = $allMenuItems[$addonId]->name; 
                            }
                        }
                    }
                }

                $detail->price = $unitPrice;
                $detail->subtotal = $unitPrice * $item->quantity;
                
                // Simpan juga catatan item spesifik ke database detail
                $finalNote = $item->notes ?? '';
                if (!empty($addonNamesArray)) {
                    $addonString = "Topping: " . implode(", ", $addonNamesArray);
                    if (!empty($finalNote)) {
                        $finalNote .= " | " . $addonString;
                    } else {
                        $finalNote = $addonString;
                    }
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
                'enabled_payments' => ['other_qris'], // QRIS ONLY
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
            return redirect()->route('orders.show', $order->id)->with('success', 'Pesanan dibuat! Silakan scan QRIS.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}