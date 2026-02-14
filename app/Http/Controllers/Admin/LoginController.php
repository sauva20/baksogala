<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\Admin\LogController;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Jika sudah login, cek role dan arahkan sesuai hak akses
        if (Auth::check()) {
            $role = Auth::user()->role;
            
            if ($role === 'owner' || $role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($role === 'kasir') {
                return redirect()->route('admin.orders.index');
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

        // Cek apakah input berupa Email atau Nama biasa
        $input = $request->username;
        $fieldType = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $credentials = [
            $fieldType => $input,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $role = $user->role;

            // DAFTAR ROLE YANG BOLEH MASUK ADMIN PANEL
            $allowedRoles = ['owner', 'admin', 'kasir'];

            if (in_array($role, $allowedRoles)) {
                
                // --- LOGGING ---
                LogController::record(
                    $user->id,
                    'Login',
                    'Auth',
                    "User {$user->name} ({$role}) berhasil masuk.",
                    null,
                    'info'
                );

                // --- REDIRECT SESUAI ROLE ---
                if ($role === 'owner' || $role === 'admin') {
                    return redirect()->route('admin.dashboard');
                } else {
                    // Kasir tidak punya dashboard, langsung ke pesanan
                    return redirect()->route('admin.orders.index');
                }
            }

            // Jika role tidak diizinkan (misal: customer biasa mencoba login di admin)
            Auth::logout();
            return back()->withErrors(['username' => 'Maaf, Anda tidak memiliki akses Admin/Kasir.']);
        }

        return back()->withErrors(['username' => 'Akun tidak ditemukan atau Password salah.']);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            LogController::record(
                Auth::id(),
                'Logout',
                'Auth',
                'Keluar dari sistem.',
                null,
                'info'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}