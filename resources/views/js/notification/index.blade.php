    <script>
        $(function() {
            var table = $("#dataTable").DataTable({
                "iDisplayLength": 50,
                "responsive": false,
                "scrollX": true,
                "scrollY": 300,
                "processing": true,
                "serverSide": true,
                "autoWidth": false,
                "ajax": {
                    url: "{!! route('admin.notification.unreaddatatables') !!}",
                    type: "POST",
                    data: function(d) {
                        d.data = $("#data-filter").serializeArrayFormJSON();
                    }
                },
                "order": [
                    [0, 'desc'],
                    [2, 'asc']
                ],
                "columnDefs": [{
                    "targets": [0, 3],
                    "width": 100,
                }, {
                    "targets": [3],
                    "orderable": false,
                }, ],
            });

            var readtable = $("#readdataTable").DataTable({
                "iDisplayLength": 50,
                "responsive": false,
                "scrollX": true,
                "scrollY": 300,
                "processing": true,
                "serverSide": true,
                "autoWidth": false,
                "ajax": {
                    url: "{!! route('admin.notification.readdatatables') !!}",
                    type: "POST",
                    data: function(d) {
                        d.data = $("#data-filter").serializeArrayFormJSON();
                    }
                },
                "order": [
                    [0, 'desc'],
                    [2, 'asc']
                ],
                "columnDefs": [{
                    "targets": [0, 3],
                    "width": 100,
                }, {
                    "targets": [3],
                    "orderable": false,
                }, ],
            });

            $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
                $($.fn.dataTable.tables(true)).DataTable()
                    .columns.adjust();
            });
        });
    </script>
