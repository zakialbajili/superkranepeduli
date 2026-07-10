<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Superkrane Peduli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Menerapkan Gradasi ke Background Utama */
        body {
            background: linear-gradient(135deg, #059669 0%, #111827 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }

        /* Styling Card Login agar menonjol */
        .login-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header i {
            font-size: 3rem;
            color: #059669;
            margin-bottom: 10px;
        }

        .login-header h3 {
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .login-header p {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* Styling Input Form (Sesuai kode Anda sebelumnya) */
        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .form-control {
            padding-left: 45px;
            height: 50px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .form-control:focus {
            border-color: #059669;
            box-shadow: 0 0 0 0.25rem rgba(5, 150, 105, 0.15);
            background-color: #ffffff;
        }

        /* Styling Button Login */
        .btn-login {
            background-color: #059669;
            color: white;
            font-weight: 600;
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }

        .btn-login:hover {
            background-color: #047857;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header">
            <i class="fa-solid fa-hard-hat"></i>
            <h3>PROGRAM PEDULI</h3>
            <p>(Pekerja Dukung Lingkungan Aman)</p>
        </div>

        <form id="loginForm" method="POST" action="javascript:void(0);">
            @csrf

            <div id="login-alert" class="alert d-none mb-3 text-center" role="alert"
                style="font-size: 0.85rem; padding: 10px;">
            </div>

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('js.login')

</body>

</html>