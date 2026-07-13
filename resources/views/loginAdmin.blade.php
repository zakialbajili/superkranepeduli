<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login Admin — Superkrane Peduli</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('backend/plugins/fontawesome-free/css/all.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            background: #062e1b;
            position: relative;
            overflow: hidden;
        }

        /* Animated background grid */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
        }

        /* Gradient orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            z-index: 0;
            opacity: 0.35;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: #10b981;
            top: -100px;
            right: -100px;
            animation: float 8s ease-in-out infinite;
        }

        .orb-2 {
            width: 350px;
            height: 350px;
            background: #059669;
            bottom: -80px;
            left: -80px;
            animation: float 10s ease-in-out infinite reverse;
        }

        .orb-3 {
            width: 250px;
            height: 250px;
            background: #047857;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: float 12s ease-in-out infinite 2s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -30px) scale(1.05);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.95);
            }
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 44px 36px 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-brand .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(5, 150, 105, 0.3);
        }

        .login-brand .icon-circle i {
            font-size: 28px;
            color: #fff;
        }

        .login-brand h1 {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.3px;
        }

        .login-brand p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 4px;
        }

        .alert {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }

        .input-box {
            position: relative;
            margin-bottom: 16px;
        }

        .input-box i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
            font-size: 16px;
            transition: color 0.2s;
            pointer-events: none;
        }

        .input-box:focus-within i {
            color: #10b981;
        }

        .input-box input {
            width: 100%;
            height: 50px;
            padding: 0 14px 0 44px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #fff;
            outline: none;
            transition: all 0.2s;
        }

        .input-box input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .input-box input:focus {
            border-color: #10b981;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .btn-login {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 16px rgba(5, 150, 105, 0.3);
            margin-top: 8px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(5, 150, 105, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
        }

        .login-footer p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.25);
        }

        .login-footer p i {
            margin-right: 4px;
        }
    </style>
</head>

<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="login-wrapper">
        <div class="login-card">

            <div class="login-brand">
                <div class="icon-circle">
                    <i class="fas fa-hard-hat"></i>
                </div>
                <h1>Superkrane Peduli</h1>
                <p>Portal Admin</p>
            </div>

            <form id="loginForm">
                @csrf

                <div id="loginAlert" class="alert" style="display:none;"></div>

                <div class="input-box">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" id="username" placeholder="Username" required autocomplete="off">
                </div>

                <div class="input-box">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    <i class="fas fa-right-to-bracket mr-2" id="loginIcon" style="margin-right: 8px;"></i><span id="loginText">Masuk</span>
                </button>
            </form>

            <div class="login-footer">
                <p><i class="fas fa-industry"></i> PT. Superkrane Mitra Utama, Tbk.</p>
            </div>

        </div>
    </div>

</body>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#loginForm').on('submit', function (e) {
            e.preventDefault();

            var btn = $('#btnLogin');
            btn.prop('disabled', true);
            $('#loginIcon').attr('class', 'fas fa-circle-notch fa-spin mr-2');
            $('#loginText').text('Memproses...');
            $('#loginAlert').hide();

            $.ajax({
                url: "{{ route('login.custom') }}",
                method: 'POST',
                data: {
                    _token: $('input[name=_token]').val(),
                    username: $('#username').val(),
                    password: $('#password').val(),
                },
                success: function (res) {
                    if (res.status === 'success' && res.redirect) {
                        window.location.href = res.redirect;
                    }
                },
                error: function (xhr) {
                    btn.prop('disabled', false);
                    $('#loginIcon').attr('class', 'fas fa-right-to-bracket mr-2');
                    $('#loginText').text('Masuk');

                    var msg = 'Username / Password Salah';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $('#loginAlert')
                        .html('<i class="fas fa-circle-exclamation mr-1"></i> ' + msg)
                        .show();
                }
            });
        });
    });
</script>

</html>