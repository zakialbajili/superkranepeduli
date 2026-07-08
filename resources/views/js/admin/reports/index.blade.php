<script>
    $(function() {
        var obj_report = dataTableBoilerPlate(
            'dataTable',
            "{!! route('admin.reports.datatable') !!}",
            function(d) {
                var formData = $('#filter-data').serializeArrayFormJSON();
                // var filterObj = {};
                // $.each(formData, function(i, field) {
                //     if (field.value !== '') {
                //         filterObj[field.name] = field.value;
                //     }
                // });
                // d.data = [filterObj];
                d.data = formData;
            },
            [[0, 'desc']],
            [{
                "targets":9,
                "orderable":false
            }]
        );

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