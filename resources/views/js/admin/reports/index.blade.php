<script>
    $(function() {
        // Cek filter_status dari URL (dari dashboard)
        var filterStatus = '{{ $filterStatus ?? '' }}';
        var filterKategori = '{{ $filterKategori ?? '' }}';
        var filterTanggal = '{{ $filterTanggal ?? '' }}';

        if (filterStatus !== '') {
            $('#status_pelaporan option').each(function() {
                if ($(this).val() === filterStatus) {
                    $('#status_pelaporan').val($(this).val());
                    return false;
                }
            });
        }

        if (filterKategori !== '') {
            $('#kategori_bahaya option').each(function() {
                if ($(this).val() === filterKategori) {
                    $('#kategori_bahaya').val($(this).val());
                    return false;
                }
            });
        }

        if (filterTanggal !== '') {
            // filter_tanggal format: YYYY-MM
            var parts = filterTanggal.split('-');
            if (parts.length === 2) {
                var year = parseInt(parts[0], 10);
                var month = parseInt(parts[1], 10);
                // Set range tanggal ke bulan tersebut
                var firstDay = year + '-' + String(month).padStart(2, '0') + '-01';
                var lastDay = new Date(year, month, 0).getDate();
                var lastDayStr = year + '-' + String(month).padStart(2, '0') + '-' + String(lastDay).padStart(2, '0');
                $('#tgl_pelaporan').val(firstDay + ' - ' + lastDayStr);
            }
        }

        var obj_report = dataTableBoilerPlate(
            'dataTable',
            "{!! route('admin.reports.datatable') !!}",
            function(d) {
                var formData = $('#filter-data').serializeArrayFormJSON();
                d.data = formData;
            },
            [[0, 'desc']],
            [{
                "targets":9,
                "orderable":false
            }]
        );

        // Auto-reload jika ada filter dari URL
        if (filterStatus || filterKategori || filterTanggal) {
            obj_report.ajax.reload();
        }

        // Date Range Picker
        $('#tgl_pelaporan').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD'
            },
            autoUpdateInput: false
        });

        $('#tgl_pelaporan').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#tgl_pelaporan').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Filter button
        $('#btn-filter').on('click', function() {
            obj_report.ajax.reload();
        });

        // Reset button
        $('#btn-reset').on('click', function() {
            $('#filter-data')[0].reset();
            $('#tgl_pelaporan').val('');
            obj_report.ajax.reload();
        });
    })
</script>
