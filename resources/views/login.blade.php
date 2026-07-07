<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>System Login</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('backend/dist/css/loginstyle.css') }}">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{ asset('backend/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">


    <style>
        /* Reset & Base Styling */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        /* Background warna Hijau Teal sesuai gambar referensi */
        body {
            background-color: #29b89d;
            /* Warna hijau teal modern */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Card Login Box */
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 40px 35px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        /* Header / Judul */
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 24px;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #7f8c8d;
            font-size: 13px;
            font-weight: 400;
        }

        /* skrn-logo */
        .skrn-logo {
            width: 70px;
            height: auto;
            margin-bottom: 15px;
            object-fit: contain;
            /* Memastikan gambar tidak gepeng */
        }

        /* Form Elements dengan Ikon di dalam Input */
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #29b89d;
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            /* Padding kiri lebih besar untuk spasi ikon */
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            font-size: 15px;
            color: #2c3e50;
            transition: all 0.3s ease;
        }

        /* Efek fokus input */
        .form-control:focus {
            border-color: #29b89d;
            outline: none;
            box-shadow: 0 0 0 3px rgba(41, 184, 157, 0.15);
        }

        .form-control::placeholder {
            color: #8395a7;
        }

        /* Button Login */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: #29b89d;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.1s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #219982;
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .btn-login:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }

        /* Pesan Error */
        .alert {
            display: none;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header">
            {{-- <img src="{{ asset('images/logo.png') }}" alt="Logo HSE" class="skrn-logo"> --}}
            <h2>SUPERKRANE PEDULI</h2>
            <p>Portal Pelaporan Kondisi Berbahaya</p>
        </div>

        <div class="alert alert-danger" id="error-message"></div>

        <form id="loginForm">
            <div class="form-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" id="employee_no" name="employee_no" class="form-control" required
                    placeholder="NIK Karyawan" autocomplete="off">
            </div>

            <div class="form-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="password" name="password" class="form-control" required
                    placeholder="Tanggal Lahir (DDMMYYYY)">
            </div>

            <button type="submit" class="btn-login" id="btnLogin">Login</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @include('js.login')

</body>

</html>