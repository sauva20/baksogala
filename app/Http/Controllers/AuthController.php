<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Tampilkan Halaman Login/Register
    public function index()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }
        return view('auth.index');
    }

    // Proses Login
    public function login(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Coba Login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // --- PERBAIKAN DISINI ---
            // Cek Role User lalu arahkan ke tempat yang sesuai
            return $this->redirectBasedOnRole()->with('success', 'Selamat Datang, ' . Auth::user()->name . '!');
        }

        // 3. Jika Gagal
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // Proses Register
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'customer' // Default role saat register adalah customer
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Registrasi berhasil, selamat datang!');
    }
    
    // Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }

    // --- FUNGSI BANTUAN UNTUK REDIRECT ---
    protected function redirectBasedOnRole()
    {
        $role = Auth::user()->role;

        // Sesuaikan nama route ini dengan yang ada di routes/web.php
        switch ($role) {
            case 'admin':
            case 'owner':
                return redirect()->route('admin.dashboard');
            case 'kasir': // Sesuaikan jika di database tulisannya 'cashier' atau 'kasir'
                return redirect()->route('admin.orders.index'); // Kasir biasanya langsung ke pesanan
            default:
                // Customer biasa
                return redirect()->intended('/');
        }
    }
}