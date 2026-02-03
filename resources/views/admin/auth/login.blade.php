<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - Bakso Gala</title>
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}"> --}}
    <link href="https://fonts.cdnfonts.com/css/bolton-sans" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Bolton Sans', sans-serif;
        }
        .admin-login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .admin-login-logo img {
            width: 80px;
            margin-bottom: 20px;
        }
        .admin-login-card h2 {
            color: #2F3D65; /* Warna Navy Premium */
            margin-bottom: 5px;
            font-weight: 700;
        }
        .admin-login-card p {
            color: #888;
            margin-bottom: 30px;
            font-size: 0.9em;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 600;
            font-size: 0.9em;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            box-sizing: border-box; /* Agar padding tidak merusak layout */
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2F3D65;
        }
        .btn-admin {
            width: 100%;
            padding: 12px;
            background-color: #2F3D65;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-admin:hover {
            background-color: #1a2540;
        }
        .error-msg {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.9em;
            margin-bottom: 20px;
            text-align: left;
            border-left: 4px solid #c62828;
        }
    </style>
</head>
<body>

    <div class="admin-login-card">
        <div class="admin-login-logo">
            <img src="{{ asset('assets/images/GALA.png') }}" alt="Logo Bakso Gala">
        </div>
        <h2>Administrator</h2>
        <p>Masuk untuk mengelola dashboard</p>

        {{-- Pesan Error --}}
        @if($errors->any())
            <div class="error-msg">
                <strong>Gagal Masuk:</strong> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            
            {{-- Input Email/Username --}}
            <div class="form-group">
                <label>Email atau Username</label>
                <input type="text" name="username" placeholder="Contoh: admin@gmail.com" required autofocus value="{{ old('username') }}">
            </div>
            
            {{-- Input Password --}}
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-admin">LOGIN SEKARANG</button>
        </form>
    </div>

</body>
</html>