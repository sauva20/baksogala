<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // 1. Eager Loading Statistik Belanja (Total Spent & Total Orders)
        // Kita hitung hanya order yang statusnya 'completed'
        $query->withCount(['orders as completed_orders_count' => function ($q) {
            $q->where('status', 'completed');
        }]);

        $query->withSum(['orders as total_spent' => function ($q) {
            $q->where('status', 'completed');
        }], 'total_price');

        // 2. Pencarian
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // 3. Sorting (Misal: Urutkan berdasarkan "Sultan" / Terkaya)
        if ($request->get('sort') == 'vip') {
            $query->orderByDesc('total_spent');
        } else {
            $query->orderByDesc('created_at'); // Default: User terbaru
        }

        $users = $query->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    // Fungsi untuk mengambil detail user via AJAX (untuk Modal)
    public function show($id)
    {
        $user = User::with(['orders' => function($q) {
            $q->latest()->limit(5); // Ambil 5 order terakhir
        }])->withSum(['orders as total_spent' => function ($q) {
            $q->where('status', 'completed');
        }], 'total_price')->findOrFail($id);

        return response()->json($user);
    }
}