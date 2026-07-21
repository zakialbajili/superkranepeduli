<script>
    $(document).ready(function () {

        function showAlert(type, message, icon) {
            let alertBox = $('#login-alert');
            alertBox.removeClass('d-none alert-danger alert-success')
                .addClass('alert-' + type)
                .html('<i class="fa-solid ' + icon + ' me-2"></i>' + message)
                .hide()
                .fadeIn('fast');
        }

        $('#employee_no, #password').on('input', function () {
            $('#login-alert').fadeOut('fast');
        });

        $('#loginForm').on('submit', function (e) {
            e.preventDefault();

            let employee_no = $('#employee_no').val();
            let password = $('#password').val();
            let btnLogin = $('#btnLogin');
            let originalText = btnLogin.text();

            $('#login-alert').hide();
            btnLogin.html('<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...').prop('disabled', true);

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
                        setTimeout(function () {
                            window.location.href = '/formreport';
                        }, 1000);
                    }
                },
                error: function (xhr) {
                    btnLogin.text(originalText).prop('disabled', false);

                    let errorMsg = "Login gagal. Periksa kembali NIK dan Password.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    showAlert('danger', errorMsg, 'fa-circle-xmark');
                }
            });
        });

       
    });
</script>