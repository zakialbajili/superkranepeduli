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
        // DataTable Ranking Pelapor — filter di modal
        // =========================================================
        function fmt(date) {
            return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
        }

        // Default filter: current month
        var now = new Date();
        var defaultStart = fmt(new Date(now.getFullYear(), now.getMonth(), 1));
        var defaultEnd = fmt(new Date(now.getFullYear(), now.getMonth() + 1, 0));
        $('#rank-filter-start').val(defaultStart);
        $('#rank-filter-end').val(defaultEnd);

        dataTableBoilerPlate(
            'rankTable',
            "{!! route('admin.dashboard.rankreportdatatable') !!}",
            function (d) {
                d.start_date = $('#rank-filter-start').val() || '';
                d.end_date = $('#rank-filter-end').val() || '';
            },
            [],
            [{ orderable: false, targets: '_all' }]
        );

        // Klik badge — isi input tanggal
        $('.filter-badge').on('click', function () {
            $('.filter-badge').removeClass('btn-primary').addClass('btn-light');
            $(this).removeClass('btn-light').addClass('btn-primary');

            var range = $(this).data('range');
            var y = now.getFullYear(), m = now.getMonth(), d = now.getDate();

            switch (range) {
                case 'current-month':
                    $('#rank-filter-start').val(fmt(new Date(y, m, 1)));
                    $('#rank-filter-end').val(fmt(new Date(y, m + 1, 0)));
                    break;
                case 'last-month':
                    $('#rank-filter-start').val(fmt(new Date(y, m - 1, 1)));
                    $('#rank-filter-end').val(fmt(new Date(y, m, 0)));
                    break;
                case 'last-30':
                    $('#rank-filter-start').val(fmt(new Date(y, m, d - 30)));
                    $('#rank-filter-end').val(fmt(new Date(y, m, d)));
                    break;
                case 'last-90':
                    $('#rank-filter-start').val(fmt(new Date(y, m, d - 90)));
                    $('#rank-filter-end').val(fmt(new Date(y, m, d)));
                    break;
                case 'ytd':
                    $('#rank-filter-start').val(y + '-01-01');
                    $('#rank-filter-end').val(fmt(new Date(y, m, d)));
                    break;
                default: // all
                    $('#rank-filter-start').val('');
                    $('#rank-filter-end').val('');
                    break;
            }
        });

        // Terapkan — baca input tanggal, tutup modal, reload
        $('#btn-apply-filter-rank').on('click', function () {
            $('#modalFilterRank').modal('hide');
            $('#rankTable').DataTable().ajax.reload();
        });

        // =========================================================
        // Statistik Laporan (Chart Bar) — filter bulan
        // =========================================================
        function fmtMonth(date) {
            return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
        }

        // Default: current month
        var now = new Date();
        var statsStart = fmtMonth(new Date(now.getFullYear(), now.getMonth(), 1));
        var statsEnd = fmtMonth(now);
        $('#stats-filter-start').val(statsStart);
        $('#stats-filter-end').val(statsEnd);
        $('#stats-filter-label').text('(' + statsStart + ' s/d ' + statsEnd + ')');

        // Klik badge — isi input bulan
        $('.filter-stats-badge').on('click', function () {
            $('.filter-stats-badge').removeClass('btn-primary').addClass('btn-light');
            $(this).removeClass('btn-light').addClass('btn-primary');

            var range = $(this).data('range');
            var y = now.getFullYear(), m = now.getMonth();

            switch (range) {
                case 'current-month':
                    $('#stats-filter-start').val(fmtMonth(new Date(y, m, 1)));
                    $('#stats-filter-end').val(fmtMonth(now));
                    break;
                case 'last-month':
                    $('#stats-filter-start').val(fmtMonth(new Date(y, m - 1, 1)));
                    $('#stats-filter-end').val(fmtMonth(new Date(y, m, 0)));
                    break;
                case 'last-3':
                    $('#stats-filter-start').val(fmtMonth(new Date(y, m - 2, 1)));
                    $('#stats-filter-end').val(fmtMonth(now));
                    break;
                case 'last-6':
                    $('#stats-filter-start').val(fmtMonth(new Date(y, m - 5, 1)));
                    $('#stats-filter-end').val(fmtMonth(now));
                    break;
                case 'ytd':
                    $('#stats-filter-start').val(y + '-01');
                    $('#stats-filter-end').val(fmtMonth(now));
                    break;
                default: // all
                    $('#stats-filter-start').val('');
                    $('#stats-filter-end').val('');
                    break;
            }
        });

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
        // Chart: Type Data Laporan (doughnut) — via AJAX boilerplate
        // =========================================================
        chartBoilerPlate({
            canvasId: 'chartTypeData',
            type: 'doughnut',
            url: "{!! route('admin.dashboard.charttypedata') !!}",
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
                            window.location.href = reportsIndexUrl + '?type_data=' + encodeURIComponent(paramKey);
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
        // Chart: Jenis Kondisi Tidak Aman (doughnut) — via AJAX
        // =========================================================
        chartBoilerPlate({
            canvasId: 'chartJenisKondisi',
            type: 'doughnut',
            url: "{!! route('admin.dashboard.chartjeniskondisi') !!}",
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
                        var label = chart.data.labels[elements[0].index] || '';
                        if (label) {
                            window.location.href = reportsIndexUrl + '?filter_jenis=' + encodeURIComponent(label) + '&filter_kategori=' + encodeURIComponent('{{ encryptId("3") }}');
                        }
                    }
                }
            }
        });

        // =========================================================
        // Chart: Jenis Tindakan Tidak Aman (doughnut) — via AJAX
        // =========================================================
        chartBoilerPlate({
            canvasId: 'chartJenisTindakan',
            type: 'doughnut',
            url: "{!! route('admin.dashboard.chartjenistindakan') !!}",
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
                        var label = chart.data.labels[elements[0].index] || '';
                        if (label) {
                            window.location.href = reportsIndexUrl + '?filter_jenis=' + encodeURIComponent(label) + '&filter_kategori=' + encodeURIComponent('{{ encryptId("4") }}');
                        }
                    }
                }
            }
        });
        // =========================================================
        // Chart: Laporan Per Bulan (Bar) — via AJAX filter bulan
        // =========================================================
        var reportsIndexUrl = "{!! route('admin.reports.index') !!}";
        var chartBulanInstance = null;

        // Default: render current month
        reloadChartBulan();
        
        // Terapkan filter — reload chart + update label
        $('#btn-apply-filter-stats').on('click', function () {
            var s = $('#stats-filter-start').val() || '';
            var e = $('#stats-filter-end').val() || '';
            $('#stats-filter-label').text(s || e ? '(' + s + ' s/d ' + e + ')' : '(semua waktu)');
            $('#modalFilterStats').modal('hide');
            reloadChartBulan();
        });
    });
    function reloadChartBulan() {
        var s = $('#stats-filter-start').val() || '';
        var e = $('#stats-filter-end').val() || '';

        chartBoilerPlate({
            canvasId: 'chartBulan',
            type: 'bar',
            url: "{!! route('admin.dashboard.chartcountreport') !!}",
            ajaxParams: {
                data: {
                    start_month: s,
                    end_month: e,
                }
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
                        var label = chart.data.labels[elements[0].index] || '';
                        var parts = label.split(' ');
                        var year = parts[0];
                        var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                        var monthIdx = monthNames.indexOf(parts[1]) + 1;
                        if (year && monthIdx) {
                            window.location.href = reportsIndexUrl + '?filter_tanggal=' + year + '-' + String(monthIdx).padStart(2, '0');
                        }
                    }
                }
            }
        });
    }

</script>