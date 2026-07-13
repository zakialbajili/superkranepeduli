<script>
    $(document).ready(function () {

        // LOGIKA TANGGAL OTOMATIS (Bisa diedit manual)
        function setDateNow() {
            let today = new Date();

            // Menyesuaikan zona waktu lokal (WIB/WITA/WIT) agar tanggal tidak meleset
            today.setMinutes(today.getMinutes() - today.getTimezoneOffset());

            // Memotong string menjadi format YYYY-MM-DD untuk input type="date"
            let formattedDate = today.toISOString().split('T')[0];

            $('#tgl_pelaporan').val(formattedDate);
        }

        // Set tanggal default saat form pertama kali dibuka
        setDateNow();

        // -----------------------------------------------------
        // LOGIKA DYNAMIC DROPDOWN LOKASI BAHAYA
        // -----------------------------------------------------
        $('#lokasi_bahaya_select').on('change', function () {
            // Ambil TEKS dari opsi yang dipilih, bukan valuenya
            let lokasiText = $(this).find("option:selected").text().trim().toLowerCase();

            // Cek apakah teksnya mengandung kata "other" atau "lainnya"
            if (lokasiText.includes('other') || lokasiText.includes('lainnya')) {
                $('#lokasi_bahaya_other').show().prop('required', true);
            } else {
                $('#lokasi_bahaya_other').hide().prop('required', false).val('');
            }
        });

        // -----------------------------------------------------
        // LOGIKA DYNAMIC DROPDOWN KATEGORI BAHAYA (Tindakan vs Kondisi)
        // -----------------------------------------------------
        $('#kategori_bahaya').on('change', function () {
            // Ambil TEKS dari opsi yang dipilih
            let kategoriText = $(this).find("option:selected").text().trim();

            // Reset kedua dropdown detail dan input text 'other' setiap kali kategori utama berubah
            $('#select-tindakan').val('').prop('required', false).prop('disabled', true);
            $('#select-kondisi').val('').prop('required', false).prop('disabled', true);
            $('#input-detail-other').val('').prop('required', false);

            $('#wrapper-tindakan').hide();
            $('#wrapper-kondisi').hide();
            $('#wrapper-detail-other').hide();

            // Cek menggunakan teks yang sesuai dengan data master di database Anda
            if (kategoriText.includes('Tindakan Tidak Aman') || kategoriText.includes('Unsafe Action')) {
                $('#wrapper-tindakan').fadeIn();
                $('#select-tindakan').prop('disabled', false).prop('required', true);
            }
            else if (kategoriText.includes('Kondisi Tidak Aman') || kategoriText.includes('Unsafe Condition')) {
                $('#wrapper-kondisi').fadeIn();
                $('#select-kondisi').prop('disabled', false).prop('required', true);
            }
        });

        // -----------------------------------------------------
        // LOGIKA MUNCULKAN INPUT TEXT JIKA MEMILIH "OTHER" PADA DETAIL
        // -----------------------------------------------------
        $('#select-tindakan, #select-kondisi').on('change', function () {
            let detailText = $(this).find("option:selected").text().trim().toLowerCase();

            if (detailText.includes('other') || detailText.includes('lainnya')) {
                $('#wrapper-detail-other').fadeIn();
                $('#input-detail-other').prop('required', true);
            } else {
                $('#wrapper-detail-other').hide();
                $('#input-detail-other').prop('required', false).val('');
            }
        });

        // 2. LOGIKA PREVIEW FOTO / GAMBAR KAMERA
        $('#foto_input').on('change', function (e) {
            $('#preview-container').html('');
            let file = e.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function (event) {
                    let imgElement = $('<img>')
                        .attr('src', event.target.result)
                        .addClass('img-fluid rounded mt-3 border')
                        .css({ 'max-height': '300px', 'width': '100%', 'object-fit': 'cover' });

                    $('#preview-container').append(imgElement);
                }
                reader.readAsDataURL(file);
            }
        });

        // =====================================================
        // LOGIKA KAMERA LAPTOP (WEBRTC)
        // =====================================================
        let webcamStream = null;
        const videoElement = document.getElementById('video-webcam');
        const canvasElement = document.getElementById('canvas-webcam');
        const fallbackText = document.getElementById('webcam-fallback');

        // Saat Modal Kamera Terbuka
        $('#kameraModal').on('shown.bs.modal', function () {
            // Reset UI
            $('#btn-capture').removeClass('d-none');
            $('#btn-use-photo, #btn-retake').addClass('d-none');
            $(videoElement).show();
            $(canvasElement).hide();
            $(fallbackText).show().text('Meminta akses kamera...');

            // Minta akses kamera
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                    .then(function (stream) {
                        webcamStream = stream;
                        videoElement.srcObject = stream;
                        $(fallbackText).hide();
                    })
                    .catch(function (err) {
                        $(fallbackText).html('<i class="fa-solid fa-triangle-exclamation fa-2x mb-2 text-warning"></i><br>Kamera tidak ditemukan atau akses ditolak browser.');
                    });
            } else {
                $(fallbackText).text('Browser Anda tidak mendukung fitur kamera web.');
            }
        });

        // Saat Modal Kamera Ditutup (Matikan lampu kamera laptop)
        $('#kameraModal').on('hidden.bs.modal', function () {
            if (webcamStream) {
                webcamStream.getTracks().forEach(track => track.stop());
                webcamStream = null;
            }
        });

        // Tombol "Ambil Foto" ditekan
        $('#btn-capture').click(function () {
            // Sesuaikan ukuran resolusi canvas dengan resolusi video asli
            canvasElement.width = videoElement.videoWidth;
            canvasElement.height = videoElement.videoHeight;

            // Cetak (gambar) frame video saat ini ke dalam canvas
            canvasElement.getContext('2d').drawImage(videoElement, 0, 0);

            // Ubah Tampilan: Sembunyikan video (live), tampilkan canvas (hasil freeze)
            $(videoElement).hide();
            $(canvasElement).show();

            // Ganti tombol
            $('#btn-capture').addClass('d-none');
            $('#btn-use-photo, #btn-retake').removeClass('d-none');
        });

        // Tombol "Foto Ulang" ditekan
        $('#btn-retake').click(function () {
            $(canvasElement).hide();
            $(videoElement).show();

            $('#btn-retake, #btn-use-photo').addClass('d-none');
            $('#btn-capture').removeClass('d-none');
        });

        // Tombol "Gunakan Foto Ini" ditekan
        $('#btn-use-photo').click(function () {
            canvasElement.toBlob(function (blob) {
                let fileName = "webcam_capture_" + new Date().getTime() + ".jpg";
                let file = new File([blob], fileName, { type: "image/jpeg", lastModified: new Date().getTime() });

                let container = new DataTransfer();
                container.items.add(file);
                document.getElementById('foto_input').files = container.files;

                $('#foto_input').trigger('change');

                // (BARU) Matikan kamera secara instan sebelum modal ditutup
                if (webcamStream) {
                    webcamStream.getTracks().forEach(track => track.stop());
                    webcamStream = null;
                }

                $('#kameraModal').modal('hide');
            }, 'image/jpeg', 0.85);
        });

        // 3. LOGIKA SUBMIT FORM KE CONTROLLER LARAVEL
        $('#formPelaporan').on('submit', function (e) {
            e.preventDefault();

            let btnSubmit = $('#btnSubmitReport');
            btnSubmit.html('<i class="fa-solid fa-circle-notch fa-spin me-2"></i> Mengirim Laporan...').prop('disabled', true);

            let formData = new FormData(this);

            $.ajax({
                url: '/submit-hse-report',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 200) {
                        // Alert Sukses Modern
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Laporan Bahaya Berhasil Terkirim!',
                            icon: 'success',
                            confirmButtonText: 'Selesai',
                            confirmButtonColor: '#198754', // Warna hijau Bootstrap
                            allowOutsideClick: false // Mencegah user klik di luar kotak
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // window.location.reload();
                                window.location.href = '/riwayat-pelaporan';
                            }
                        });
                    }
                },
                error: function (xhr) {
                    let msg = "Gagal mengirim data. Silakan coba lagi.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }

                    // Alert Error Modern
                    Swal.fire({
                        title: 'Gagal!',
                        text: msg,
                        icon: 'error',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#dc3545' // Warna merah Bootstrap
                    });
                },
                complete: function () {
                    // Kembalikan tombol seperti semula jika terjadi error
                    // (Jika sukses, tombol tidak perlu dikembalikan karena halaman akan direload)
                    btnSubmit.html('<i class="fa-solid fa-paper-plane me-2"></i> Submit').prop('disabled', false);
                }
            });
        });

        // 4. LOGIKA TOMBOL LOGOUT
        $('#btnLogout').click(function () {
            window.location.href = '/logout';
        });
    });
</script>