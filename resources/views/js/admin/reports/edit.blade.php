<script>
    // ========== KATEGORI BAHAYA ⇢ JENIS BAHAYA DINAMIS ==========
    $('#kategori_bahaya').on('change', function() {
        var val = $(this).val();
        if (val === '') {
            $('#opt-kondisi').hide();
            $('#opt-tindakan').hide();
            $('#desc_kategori_bahaya').val('');
            // $('#desc_kategori_bahaya_other').hide().val('');
            $('#desc_kategori_bahaya_other').hide();
            return;
        }
        var text = $(this).find('option:selected').text().trim();
        if (text === 'Kondisi Tidak Aman') {
            $('#opt-kondisi').show();
            $('#opt-tindakan').hide();
        } else if (text === 'Tindakan Tidak Aman') {
            $('#opt-kondisi').hide();
            $('#opt-tindakan').show();
        } else {
            $('#opt-kondisi').hide();
            $('#opt-tindakan').hide();
        }
        $('#desc_kategori_bahaya').val('');
        // $('#desc_kategori_bahaya_other').hide().val('');
        $('#desc_kategori_bahaya_other').hide();
    });

    // ========== LOKASI BAHAYA ⇢ TOGGLE INPUT OTHER ==========
    $('#lokasi_bahaya').on('change', function() {
        if ($(this).val() === 'other') {
            $('#lokasi_bahaya_other').show();
        } else {
            $('#lokasi_bahaya_other').hide();
        }
    });

    // ========== JENIS BAHAYA ⇢ TOGGLE INPUT OTHER ==========
    $('#desc_kategori_bahaya').on('change', function() {
        if ($(this).val() === 'other') {
            $('#desc_kategori_bahaya_other').show();
        } else {
            $('#desc_kategori_bahaya_other').hide();
        }
    });

    // ========== DRAG-DROP UPLOAD CUSTOM ==========
    var $dropzone = $('#dropzone-upload');
    var $fileInput = $('#document');
    var $placeholder = $('#upload-placeholder');
    var $preview = $('#upload-preview');
    var $previewImg = $('#preview-image');
    var $fileNameDisplay = $('#file-name-display');

    // Klik area upload → trigger file input
    $dropzone.on('click', function(e) {
        if (!$(e.target).closest('#btn-remove-file, #existing-document a').length) {
            $fileInput.click();
        }
    });

    // File input change → preview
    $fileInput.on('change', function() {
        handleFile(this.files[0]);
    });

    // Drag events
    $dropzone.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });
    $dropzone.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });
    $dropzone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            $fileInput[0].files = files;
            $fileInput.trigger('change');
        }
    });

    function handleFile(file) {
        if (!file) {
            $placeholder.show();
            $preview.hide();
            return;
        }
        $fileNameDisplay.text(file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)');

        if (file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $previewImg.attr('src', e.target.result);
                $placeholder.hide();
                $preview.show();
            };
            reader.readAsDataURL(file);
        } else {
            // Non-image (PDF etc)
            $previewImg.attr('src', '');
            $placeholder.hide();
            $preview.show();
        }
    }

    // Tombol hapus file
    $('#btn-remove-file').on('click', function(e) {
        e.stopPropagation();
        $fileInput.val('');
        $placeholder.show();
        $preview.hide();
    });

    // ========== SAVE FORM ==========
    $("#save").click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Mengupdate data ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Iya, Update Data ini!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.LoadingOverlay("show");
                var dataform = $('#dataform').serializeArrayFormJSON();
                var formData = new FormData();
                formData.append('_token', $('input[name=_token]').val());
                formData.append('_method', 'PUT');
                formData.append('data[dataform][0][tgl_pelaporan]', dataform[0].tgl_pelaporan || '');
                formData.append('data[dataform][0][lokasi_bahaya]', dataform[0].lokasi_bahaya || '');
                if($('#lokasi_bahaya').val() != 'other'){
                    $('#lokasi_bahaya_other').val('')
                }
                formData.append('data[dataform][0][lokasi_bahaya_other]', $('#lokasi_bahaya_other').val() || '');
                formData.append('data[dataform][0][shift]', dataform[0].shift || '');
                formData.append('data[dataform][0][data_pelaporan]', dataform[0].data_pelaporan || '');
                formData.append('data[dataform][0][kategori_bahaya]', dataform[0].kategori_bahaya || '');
                formData.append('data[dataform][0][desc_kategori_bahaya]', dataform[0].desc_kategori_bahaya || '');
                if($('#desc_kategori_bahaya').val() != 'other'){
                    $('#desc_kategori_bahaya_other').val('')
                }
                formData.append('data[dataform][0][desc_kategori_bahaya_other]', $('#desc_kategori_bahaya_other').val() || '');
                formData.append('data[dataform][0][desc_temuan_bahaya]', dataform[0].desc_temuan_bahaya || '');
                formData.append('data[dataform][0][rekomendasi_perbaikan]', dataform[0].rekomendasi_perbaikan || '');
                formData.append('data[dataform][0][dept_penanggungjwb]', dataform[0].dept_penanggungjwb || '');
                formData.append('data[dataform][0][nama_pengawas]', dataform[0].nama_pengawas || '');
                formData.append('data[dataform][0][employee_no]', dataform[0].employee_no || '');
                formData.append('data[dataform][0][full_name]', dataform[0].full_name || '');
                formData.append('data[dataform][0][posisi]', dataform[0].posisi || '');
                formData.append('data[dataform][0][due_date]', dataform[0].due_date || '');
                formData.append('data[dataform][0][status_pelaporan]', dataform[0].status_pelaporan || '');
                if ($('#document')[0].files[0]) {
                    var fileSize = $('#document')[0].files[0].size;
                    if (fileSize > 5 * 1024 * 1024) {
                        $.LoadingOverlay("hide");
                        toastr.error('Ukuran file tidak boleh lebih dari 5 MB');
                        return;
                    }
                    formData.append('data[dataform][0][document]', $('#document')[0].files[0]);
                }

                $.ajax({
                    url: "{!! route('admin.reports.update', encrypt($report->pk_hsepelaporanbahaya_id)) !!}",
                    type: "post",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $.LoadingOverlay("hide");
                        if (response.status == "success") {
                            toastr.success(response.message);
                            if (response.url) {
                                window.location = response.url;
                            }
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        $.LoadingOverlay("hide");
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            var messages = Object.values(xhr.responseJSON.errors).flat();
                            toastr.error(messages[0] || 'Validasi gagal');
                        } else {
                            toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan');
                        }
                    }
                });
            }
        })
    });
</script>
