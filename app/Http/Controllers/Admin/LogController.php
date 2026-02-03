<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog; // Pastikan Model ActivityLog sudah ada
use App\Models\User;

class LogController extends Controller
{
    /**
     * Menampilkan halaman riwayat log (Audit Trail).
     */
    public function index(Request $request)
    {
        // Query Dasar: Ambil log terbaru + data usernya
        $query = ActivityLog::with('user')->latest();

        // 1. Filter Berdasarkan Module (Misal: Auth, Order, Menu)
        if ($request->has('module') && $request->module != 'all') {
            $query->where('module', $request->module);
        }

        // 2. Filter Berdasarkan Tingkat Bahaya (Info, Warning, Danger)
        if ($request->has('severity') && $request->severity != 'all') {
            $query->where('severity', $request->severity);
        }

        // 3. Pencarian (Cari di deskripsi log)
        if ($request->has('search') && $request->search != '') {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Pagination 15 data per halaman
        $logs = $query->paginate(15);
        
        // Ambil daftar module unik untuk dropdown filter di View
        $modules = ActivityLog::select('module')->distinct()->pluck('module');

        return view('admin.logs.index', compact('logs', 'modules'));
    }
    
    /**
     * Helper Statis: Untuk merekam log dari Controller lain dengan mudah.
     * Cara Pakai: LogController::record(...)
     */
    public static function record($user_id, $action, $module, $desc, $changes = null, $severity = 'info')
    {
        ActivityLog::create([
            'user_id'     => $user_id,
            'action'      => $action,      // Create, Update, Delete, Login
            'module'      => $module,      // Menu, Order, Auth
            'description' => $desc,        // Penjelasan aktivitas
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->header('User-Agent'),
            'changes'     => $changes,     // Data JSON (Old vs New)
            'severity'    => $severity     // info, warning, danger
        ]);
    }
}