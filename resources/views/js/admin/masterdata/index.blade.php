<script>
    $(document).ready(function () {
        var activeType = '{!! $firstType !!}';

        // Set judul awal
        $('#judulTabAktif').text('Daftar ' + activeType);

        var obj_master_data = dataTableBoilerPlate(
            'tableMasterData',
            "{!! route('admin.masterdata.datatable') !!}",
            function (d) {
                d._token = "{{ csrf_token() }}";
                d.filter_type = activeType;
            },
            [[0, 'asc']],
            [
                // Tambahkan target kolom 1 (Aktif) dan kolom 2 (Aksi) agar tidak bisa disorting
                { "targets": 1, "orderable": false, "className": "text-center align-middle" },
                { "targets": 2, "orderable": false, "className": "text-center align-middle" }
            ]
        );

        // 2. EVENT TAB DIKLIK
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            activeType = $(e.target).data('type');

            // Update judul dengan animasi fade lembut
            $('#judulTabAktif').fadeOut(150, function () {
                $(this).text('Daftar ' + activeType).fadeIn(150);
            });

            obj_master_data.ajax.reload();
        });

        // 3. TAMBAH DATA 
        $('#btnBukaModal').on('click', function () {
            $('#modalTambahMaster #type').val(activeType);
            $('#modalTambahMaster #name').val('');

            $('#modalTambahMaster').modal('show');

            // Auto-focus pada input nama setelah modal terbuka
            setTimeout(() => $('#modalTambahMaster #name').focus(), 500);
        });

        // 4. SUBMIT TAMBAH DATA (Proses AJAX)
        $('#formMasterData').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);
            let btnSubmit = form.find('button[type="submit"]');
            let btnText = btnSubmit.html();

            $.ajax({
                url: form.attr('action'),
                type: "POST",
                data: form.serialize(),
                beforeSend: function () {
                    btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
                },
                success: function (response) {
                    if (response.status === 'success') {
                        // Tutup modal dengan mulus
                        $('#modalTambahMaster').modal('hide');

                        // Jeda 300ms untuk SweetAlert
                        setTimeout(function () {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, showConfirmButton: false, timer: 1500 });
                            obj_master_data.ajax.reload(null, false);
                        }, 300);

                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
                    }
                    btnSubmit.prop('disabled', false).html(btnText);
                },
                error: handleAjaxError(btnSubmit, btnText)
            });
        });

        // 5. EDIT DATA (Membuka Modal Edit & Tarik Data)
        // menggunakan event delegation 'on' dari body karena tombol Edit dirender oleh DataTable
        $('body').on('click', '.btn-edit', function () {
            let id = $(this).data('id');
            let urlEdit = "{{ url('admin/masterdata') }}/" + id + "/edit";
            let urlUpdate = "{{ url('admin/masterdata') }}/" + id;

            // Memanggil data lama dari server
            $.get(urlEdit, function (response) {
                if (response.status === 'success') {
                    // Isi form dengan data lama
                    $('#modalEditMaster #edit_type').val(response.data.type);
                    $('#modalEditMaster #edit_name').val(response.data.name);

                    // Ubah action form agar mengarah ke route update dengan ID yang benar
                    $('#formEditMasterData').attr('action', urlUpdate);

                    // Buka modal Edit
                    $('#modalEditMaster').modal('show');
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
                }
            });
        });

        // 6. SUBMIT EDIT DATA (Proses Update AJAX)
        $('#formEditMasterData').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);
            let btnSubmit = form.find('button[type="submit"]');
            let btnText = btnSubmit.html();

            $.ajax({
                url: form.attr('action'),
                type: "POST",
                data: form.serialize(),
                beforeSend: function () {
                    btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengupdate...');
                },
                success: function (response) {
                    if (response.status === 'success') {
                        // Tutup modal dengan mulus
                        $('#modalEditMaster').modal('hide');

                        // Jeda 300ms untuk SweetAlert
                        setTimeout(function () {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, showConfirmButton: false, timer: 1500 });
                            obj_master_data.ajax.reload(null, false);
                        }, 300);

                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
                    }
                    btnSubmit.prop('disabled', false).html(btnText);
                },
                error: handleAjaxError(btnSubmit, btnText)
            });
        });

        // Helper Function untuk Menangkap Error Validasi agar kode tidak berulang
        function handleAjaxError(btnSubmit, btnText) {
            return function (xhr) {
                let res = xhr.responseJSON;
                let message = 'Terjadi kesalahan sistem.';
                if (res && res.errors) {
                    message = Object.values(res.errors)[0][0];
                }
                Swal.fire({ icon: 'error', title: 'Validasi Gagal!', text: message });
                btnSubmit.prop('disabled', false).html(btnText);
            }
        }

        // TOGLE ACTIVE INACTIVE
        $('body').on('change', '.toggle-active', function () {
            let id = $(this).data('id');
            // Jika toggle ditarik ke kanan (checked), nilainya 1. Jika ke kiri, nilainya 0.
            let status = $(this).is(':checked') ? '1' : '0';
            let toggleElement = $(this);

            $.ajax({
                url: "{{ route('admin.masterdata.toggleActive') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    status: status
                },
                success: function (data) {
                    if (data.status == 'success') {
                        toastr.success(data.message)
                    } else {
                        toastr.error(data.message)
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        });
    });
</script>