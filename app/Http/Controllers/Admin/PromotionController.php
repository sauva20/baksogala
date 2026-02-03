<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promotion;

class PromotionController extends Controller
{
    public function index()
    {
        // Ambil data, urutkan yang aktif dan terbaru duluan
        $promotions = Promotion::orderBy('is_active', 'desc')
                               ->orderBy('created_at', 'desc')
                               ->paginate(9); // Grid 3x3
        return view('admin.promotions.index', compact('promotions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:promotions,code|uppercase',
            'type' => 'required',
            'discount_amount' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $data = $request->except(['_token']);
        
        // Handle checkbox boolean
        $data['is_active'] = $request->has('is_active');
        
        // Set default 0 jika kosong
        $data['min_purchase'] = $request->input('min_purchase', 0);
        $data['quota'] = $request->input('quota', 100);

        Promotion::create($data);

        return back()->with('success', 'Voucher berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $promo = Promotion::findOrFail($id);
        
        $request->validate([
            'code' => 'required|uppercase|unique:promotions,code,'.$id,
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $data = $request->except(['_token', '_method']);
        $data['is_active'] = $request->has('is_active');

        $promo->update($data);

        return back()->with('success', 'Voucher berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Promotion::findOrFail($id)->delete();
        return back()->with('success', 'Voucher dihapus!');
    }
}