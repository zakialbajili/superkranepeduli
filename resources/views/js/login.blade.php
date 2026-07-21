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

            // Sembunyikan alert lama dan ubah state tombol
            $('#login-alert').hide();
            btnLogin.html('<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...').prop('disabled', true);

            // Tembak langsung ke controller lokal Laravel (TIDAK LAGI ke port 9000)
            $.ajax({
                url: '/login-user',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                data: {
                    employee_no: employee_no,
                    password: password
                },
                success: function (response) {
                    if (response.status === 200) {
                        // Gunakan fungsi alert sukses modern Anda
                        // showAlert('success', 'Login Berhasil! Mengalihkan...', 'fa-circle-check');
                        setTimeout(function () {
                            window.location.href = '/formreport';
                        }, 1000);
                    }
                },
                error: function (xhr) {
                    // Kembalikan tombol
                    btnLogin.text(originalText).prop('disabled', false);

                    // Tampilkan error
                    let errorMsg = "Login gagal. Periksa kembali NIK dan Password.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    // Gunakan fungsi alert merah Anda
                    showAlert('danger', errorMsg, 'fa-circle-xmark');
                }
            });
        });

       
    });
</script>