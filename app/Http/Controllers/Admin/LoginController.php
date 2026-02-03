<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
// 1. PENTING: Import LogController agar bisa dipanggil
use App\Http\Controllers\Admin\LogController;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            // Cek jika user sudah login dan role-nya admin/owner
            if (in_array(auth()->user()->role, ['admin', 'owner'])) {
                return redirect()->route('admin.dashboard');
            }
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $input = $request->username;
        
        // Cek apakah input berupa Email atau Nama biasa
        $fieldType = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $credentials = [
            $fieldType => $input,
            'password' => $request->password
        ];

        // LOGIKA BARU: Pakai Auth::attempt (Otomatis cek ke tabel 'users')
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Ambil data user yang sedang login
            $user = Auth::user();
            $role = $user->role;

            // Cek Role
            if ($role === 'admin' || $role === 'owner') {
                
                // --- 2. LOGGING: Catat Login Berhasil ---
                LogController::record(
                    $user->id,              // Siapa yg login
                    'Login',                // Aksi
                    'Auth',                 // Modul
                    'Berhasil masuk ke Dashboard', // Pesan
                    null,                   // Data changes (kosong)
                    'info'                  // Level hijau
                );
                // ----------------------------------------

                return redirect()->route('admin.dashboard');
            }

            // Kalau bukan admin, tendang
            Auth::logout();
            return back()->withErrors(['username' => 'Anda bukan Admin!']);
        }

        return back()->withErrors(['username' => 'Akun tidak ditemukan atau Password salah.']);
    }

    public function logout(Request $request)
    {
        // --- 3. LOGGING: Catat Logout (Sebelum session dihancurkan) ---
        if (Auth::check()) {
            LogController::record(
                Auth::id(),
                'Logout',
                'Auth',
                'Keluar dari sistem (Logout)',
                null,
                'info'
            );
        }
        // -------------------------------------------------------------

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}