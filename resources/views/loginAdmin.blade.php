<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Login</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('backend/dist/css/loginstyle.css') }}">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{ asset('backend/plugins/fontawesome-free/css/all.min.css') }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

<div class="container">
    <div class="login-header">
        <h1>SYSTEM</h1>
        <p></p>
    </div>

    <form action="{{ route('login.custom') }}" method="POST">
        @csrf

        @if (session('error'))
            <div class="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="input-box">
            <i class="fas fa-user"></i>
            <input
                type="text"
                name="username"
                placeholder="Username"
                required
                autocomplete="off"
            >
        </div>

        <div class="input-box">
            <i class="fas fa-lock"></i>
            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >
        </div>

        <div class="button">
            <input type="submit" value="Login">
        </div>
    </form>
</div>

</body>
</html>
