<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#loginForm').on('submit', function (e) {
            e.preventDefault();

            let employee_no = $('#employee_no').val();
            let password = $('#password').val();
            let btnLogin = $('#btnLogin');
            let originalText = btnLogin.text();
            btnLogin.text('Memproses...').prop('disabled', true);

            $.ajax({
                url: 'http://127.0.0.1:9000/api/loginless',
                type: 'POST',
                headers: {
                    'X-API-Key': '{{ env('API_BACKEND_KEY') }}',
                    'Accept': 'application/json'
                },
                data: {
                    employee_no: employee_no,
                    password: password
                },
                success: function (response) {
                    let token = response.token ||
                        response.access_token ||
                        (response.data && response.data.token);

                    if (token) {
                        localStorage.setItem('bearer_token', token);
                        window.location.href = '/dashboard';
                    } else {
                        btnLogin.text(originalText).prop('disabled', false);
                        alert("Token tidak ditemukan pada response.");
                    }
                },
                error: function (xhr) {
                    btnLogin.text(originalText).prop('disabled', false);

                    let errorMsg = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : "Login gagal. Periksa kembali NIK dan Password.";

                    alert(errorMsg);
                }
            });
        });
    });
</script>