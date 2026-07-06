$(function() {
    var datafilter = [];
    var table = $("#dataTable").DataTable({
        "iDisplayLength": 50,
        "responsive": false,
        "scrollX": true,
        "scrollY": 300,
        "processing": true,
        "serverSide": true,
        "autoWidth": false,
        "ajax": {
            url: "{!! route('admin.costsubmissionreport.datatables') !!}",
            type: "POST",
            data: function(d) {
                d.data = $("#data-filter").serializeArrayFormJSON();
            }
        },
        "order": [
            [3, 'desc'],
            [2, 'desc']
        ],
        'select': {
            'style': 'multi'
        },
        "columnDefs": [{
            "targets": [9],
            "orderable": false,
        }, {
            "targets": [1, 2, 3, 9, 11],
            "width": 100,
        }, {
            "targets": [8],
            "width": 400,
        }, {
            'targets': 0,
            'checkboxes': {
                'selectRow': true,
                'selectAll': false,
            }
        }, ],
    });

    $("#generate-data").click(function(e) {
        e.preventDefault();
        // var rows_selected = table.column(0).checkboxes.selected();
        var data = [];
        $.each(rows_selected, function(index, rowId) {
            data.push(rowId);
        });
        // window.location.href = "{!! route('admin.costsubmissionreport.create') !!}/" + encodeURIComponent(JSON.stringify(
        //     data));

        $.ajax({
            url: "{!! route('admin.costsubmissionreport.generatecostsubmissionreport') !!}",
            type: "post",
            data: JSON.stringify(data),
            success: function (response) {
                $.LoadingOverlay("hide");
                if (response.status == "success") {
                    window.location = "{!! route('admin.costsubmissionreport.create') !!}";
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $.LoadingOverlay("hide");
                toastr.error(xhr.responseJSON.message);
            }
        });
        
    });

    $('#filter-data').click(function(e) {
        e.preventDefault();
        table.draw();
    });
});