<script src="{{ asset('backend/plugins/chart.js/Chart.min.js') }}"></script>
<script>
    $(function () {
        // =========================================================
        // DataTable Due Date (server-side)
        // =========================================================
        dataTableBoilerPlate(
            'dueDateTable',
            "{!! route('admin.dashboard.duedatereportdatatable') !!}",
            function (d) {
                // no additional filter data
            },
            [[0, 'asc']],
            [{
                "targets": 8,
                "orderable": false
            }]
        );
        // =========================================================
        // DataTable Ranking Pelapor (dengan highlight top 3)
        // =========================================================
        dataTableBoilerPlate(
            'rankTable',
            "{!! route('admin.dashboard.rankreportdatatable') !!}",
            function (d) {},
            [],
            [{ orderable: false, targets: '_all' }]
        );

        // Highlight rows untuk top 3 gold/silver/bronze
        $('#rankTable').off('draw.dt').on('draw.dt', function () {
            $('#rankTable tbody tr').each(function () {
                var rankCell = $(this).find('td:first').html() || '';
                $(this).removeClass('rank-gold rank-silver rank-bronze');
                if (rankCell.indexOf('rank-1') !== -1) $(this).addClass('rank-gold');
                else if (rankCell.indexOf('rank-2') !== -1) $(this).addClass('rank-silver');
                else if (rankCell.indexOf('rank-3') !== -1) $(this).addClass('rank-bronze');
            });
        });

        // =========================================================
        // Chart: Laporan Per Bulan (Bar) — via AJAX boilerplate
        // =========================================================
        var bulanTahun = {!! $tahunIni !!};
        var reportsIndexUrl = "{!! route('admin.reports.index') !!}";

        function reloadChartBulan(tahun) {
            chartBoilerPlate({
                canvasId: 'chartBulan',
                type: 'bar',
                url: "{!! route('admin.dashboard.chartcountreport') !!}",
                ajaxParams: {
                    data: { tahun: tahun }
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    },
                    onClick: function (evt, elements, chart) {
                        if (elements && elements.length > 0) {
                            var idx = elements[0].index;
                            var month = idx + 1;
                            var year = tahun;
                            window.location.href = reportsIndexUrl + '?filter_tanggal=' + year + '-' + String(month).padStart(2, '0');
                        }
                    }
                }
            });
        }
        reloadChartBulan(bulanTahun);

        function reloadChartTahun() {
            var val = $('#filter-tahun').val();
            if (val) {
                var tahun = val.split('-')[0];
                reloadChartBulan(tahun);
            }
        }
        $('#btn-reload-chart').on('click', reloadChartTahun);
        $('#filter-tahun').on('change', reloadChartTahun);

        // =========================================================
        // Chart: Status Laporan (doughnut) — via AJAX boilerplate
        // =========================================================
        chartBoilerPlate({
            canvasId: 'chartStatus',
            type: 'doughnut',
            url: "{!! route('admin.dashboard.chartstatus') !!}",
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, padding: 8, font: { size: 11 } }
                    }
                },
                onClick: function (evt, elements, chart) {
                    if (elements && elements.length > 0) {
                        var idx = elements[0].index;
                        var paramKey = chart._encryptedParams && chart._encryptedParams[idx] ? chart._encryptedParams[idx] : '';
                        if (paramKey) {
                            window.location.href = reportsIndexUrl + '?filter_status=' + encodeURIComponent(paramKey);
                        }
                    }
                }
            }
        });

        // =========================================================
        // Chart: Kategori Bahaya (doughnut) — via AJAX boilerplate
        // =========================================================
        chartBoilerPlate({
            canvasId: 'chartKategori',
            type: 'doughnut',
            url: "{!! route('admin.dashboard.chartkategori') !!}",
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, padding: 6, font: { size: 10 } }
                    }
                },
                onClick: function (evt, elements, chart) {
                    if (elements && elements.length > 0) {
                        var idx = elements[0].index;
                        var paramKey = chart._encryptedParams && chart._encryptedParams[idx] ? chart._encryptedParams[idx] : '';
                        if (paramKey) {
                            window.location.href = reportsIndexUrl + '?filter_kategori=' + encodeURIComponent(paramKey);
                        }
                    }
                }
            }
        });

        // =========================================================
        // Chart: Jenis Kondisi Tidak Aman (pie) — via AJAX
        // =========================================================
        chartBoilerPlate({
            canvasId: 'chartJenisKondisi',
            type: 'doughnut',
            url: "{!! route('admin.dashboard.chartjeniskondisi') !!}",
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // =========================================================
        // Chart: Jenis Tindakan Tidak Aman (pie) — via AJAX
        // =========================================================
        chartBoilerPlate({
            canvasId: 'chartJenisTindakan',
            type: 'doughnut',
            url: "{!! route('admin.dashboard.chartjenistindakan') !!}",
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
