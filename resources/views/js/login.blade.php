<script>
    $(document).ready(function () {

        // Fungsi helper untuk menampilkan alert
        function showAlert(type, message, icon) {
            let alertBox = $('#login-alert');
            alertBox.removeClass('d-none alert-danger alert-success')
                .addClass('alert-' + type)
                .html('<i class="fa-solid ' + icon + ' me-2"></i>' + message)
                .hide()
                .fadeIn('fast');
        }

        // Hilangkan alert saat user mulai mengetik ulang di inputan
        $('#employee_no, #password').on('input', function () {
            $('#login-alert').fadeOut('fast');
        });

        $('#loginForm').on('submit', function (e) {
            e.preventDefault();

            let employee_no = $('#employee_no').val();
            let password = $('#password').val();
            let btnLogin = $('#btnLogin');
            let originalText = btnLogin.text();

            // Sembunyikan alert lama saat tombol ditekan
            $('#login-alert').hide();
            btnLogin.html('<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...').prop('disabled', true);

            // LANGKAH 1: Tembak API Utama port 9000
            $.ajax({
                url: 'http://127.0.0.1:9000/api/loginless',
                type: 'POST',
                headers: {
                    'X-API-Key': '{{ env("API_BACKEND_KEY") }}',
                    'Accept': 'application/json'
                },
                data: {
                    employee_no: employee_no,
                    password: password
                },
                success: function (response) {
                    let token = response.token || (response.data && response.data.token);

                    if (token) {
                        // LANGKAH 2: Kirim data hasil API ke Rute Jembatan
                        $.ajax({
                            url: '/auth/bridge',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('input[name="_token"]').val()
                            },
                            data: {
                                employee_no: response.data.employee_no,
                                full_name: response.data.full_name,
                                position: response.data.position,
                                token: token
                            },
                            success: function (bridgeResponse) {
                                // Tampilkan pesan sukses sebentar sebelum redirect
                                showAlert('success', 'Login Berhasil!', 'fa-circle-check');
                                setTimeout(function () {
                                    window.location.href = '/formreport';
                                }, 1000); // Jeda 1 detik agar animasi sukses terlihat
                            },
                            error: function (xhr) {
                                btnLogin.text(originalText).prop('disabled', false);
                                let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Gagal sinkronisasi session lokal.";
                                showAlert('danger', msg, 'fa-triangle-exclamation');
                            }
                        });

                    } else {
                        btnLogin.text(originalText).prop('disabled', false);
                        showAlert('danger', 'Token tidak ditemukan pada response API.', 'fa-circle-xmark');
                    }
                },
                error: function (xhr) {
                    btnLogin.text(originalText).prop('disabled', false);
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Login gagal. Periksa kembali NIK dan Password.";

                    // Tampilkan pesan error di atas inputan NIK
                    showAlert('danger', errorMsg, 'fa-circle-xmark');
                }
            });
        });
    });
</script>