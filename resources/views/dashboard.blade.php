<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Halaman Utama - Superkrane Peduli</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Selamat Datang di Halaman Utama!</h1>
    <div id="status">Memeriksa sesi...</div>
    <button id="btnLogout" style="display:none;">Logout</button>

    <script>
        $(document).ready(function() {
            // 1. Ambil token dari localStorage yang disimpan saat login
            let token = localStorage.getItem('bearer_token');
            
            if (!token) {
                // Jika tidak ada token, tendang kembali ke halaman login
                alert('Sesi Anda telah habis atau Anda belum login!');
                window.location.href = '/login'; // Sesuaikan dengan URL login Anda
            } else {
                // Jika token ada, izinkan user berada di halaman ini
                $('#status').html('Token valid. Anda berhasil masuk!');
                $('#btnLogout').show();
            }

            // 2. Fungsi Tombol Logout
            $('#btnLogout').click(function() {
                // Hapus token dari browser
                localStorage.removeItem('bearer_token');
                alert('Anda telah logout.');
                window.location.href = '/login'; 
            });
        });
    </script>
</body>
</html>