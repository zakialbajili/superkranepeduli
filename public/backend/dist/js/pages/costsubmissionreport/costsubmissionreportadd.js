let startNum = "{!! $unique !!}";
const uniqId = (() => {
    return () => {
        return startNum++;
    }
})();

$(function () {
    $('#rekening').hide();
    var obj_employeeTable = $("#employeeDatatable").DataTable({
        "responsive": true,
        "lengthChange": false,
        "processing": true,
        "serverSide": true,
        "autoWidth": false,
        "ajax": {
            url: "{!! route('admin.costsubmission.employeedatatable') !!}",
            type: "POST",
        },
    });

    $(".delimited-number").each(function () {
        initnumber($(this));
    });

    $(".rowcalc").each(function () {
        initCalc($(this));
    });

    $(".employee").each(function () {
        initializeEmployeeSelect($(this));
    });
    
    $('#employeeDatatable tbody').on('click', '.selected-item', function () {
        var row = $(this).closest('tr');
        var id = $(this).attr("data-id");
        var data = obj_employeeTable.row(row).data();
        var newOption = new Option(data[0] + " | " + data[1] + " | " + data[2], id, true, false);

        var select2item;
        var source = $('#sourcename').val();
        var sourceid = $('#sourceid').val();
        switch (source) {
            case "pic":
                select2item = $("#pic")
                break;
            case "submittedby":
                select2item = $("#submittedby")
                break;
            case "approvedby":
                select2item = $("#approvedby")
                break;
            case "turnedoverby":
                select2item = $("#turnedoverby")
                break;
            case "receivedby":
                select2item = $("#receivedby")
                break;
            case "employee":
                select2item = $("#employee-" + sourceid)
                break;
            case "picdana":
                select2item = $("#picdana-" + sourceid)
                break;
            default:
                break;
        }
        if (select2item.select2('data').length > 0) {
            select2item.empty().trigger("change");
        }
        select2item.append(newOption).trigger('change');

        $('#modal-employee').modal('hide');
    });
});

$('#paymenttype').on('change', function () {
    if (this.value == 1) {
        $("#rekening").show();
    } else {
        $("#rekening").hide();
    }
});


$("#add-request").click(function (e) {
    e.preventDefault();
    const unique = uniqId();
    var htmldata = '<tr>' +
        '<td style="display: none"><label id="id-' + unique + '" name="id">0</label></td>' +
        '<td><input type="text" class="form-control datepicker" id="detaildate-' + unique +
        '" name="detaildate" /></td>' +
        '<td><input type="text" id="description-' + unique +
        '" name="description" class="form-control"></td>' +
        '<td><input type="text" id="noreg-' + unique + '" name="noreg" class="form-control"></td>' +
        '<td>' +
        '<select id="costtype-' + unique + '" name="costtype" class="form-control custom-select">' +
        '<option value="">Pilih Tipe Biaya</option>{!! $costtype !!}' +
        '</select>' +
        '</td>' +
        '<td><input type="text" id="amount-' + unique +
        '" name="amount" class="form-control delimited-number amount rowcalc"></td>' +
        '<td><button class="btn btn-sm btn-danger ml-2 delete-row"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';

    $('#reqtable tbody tr:last').after(htmldata);

    $('#reqtable tbody tr:last').find(".delimited-number").each(function () {
        initnumber($(this));
    });
    $('#reqtable tbody tr:last').find(".datepicker").each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true
        });
    });
    $('#reqtable tbody tr:last').find(".rowcalc").each(function () {
        initCalc($(this));
    });
});

$("#reqtable").on('click', '.delete-row', function () {
    var rowCount = $("#reqtable tbody tr").length;
    if (rowCount > 1) {
        $(this).closest('tr').remove();
        calcTotal();
    }
});

$('.project').on('select2:select', function (e) {
    $('#customer').val(e.params.data.customer);
    $('#location').val(e.params.data.location);
});

function initializeEmployeeSelect(selectElementObj) {
    //selectElementObj.select2();
    selectElementObj.select2({
        width: "80%",
        ajax: {
            url: "{!! route('admin.costsubmission.getemployee') !!}",
            dataType: "json",
            type: "GET",
            delay: 250,
            data: function (params) {
                return {
                    search: params.term
                }
            },
            processResults: function (res) {
                return {
                    results: $.map(res.data.data, function (item) {
                        return {
                            id: item.id,
                            text: item.text
                        }
                    })
                };
            },
            cache: false
        },
        minimumInputLength: 3
    });

    $('#search-pic').click(function (e) {
        e.preventDefault();
        $('#sourcename').val($(this).data('source'));
        $('#sourceid').val($(this).data('id'));
        $('#modal-employee').modal('show');
    });

};


function initCalc(element) {
    element.change(function () {
        calcTotal();
    });
}

function calcTotal() {
    let etotal = 0;
    let esubmissiontotal = clearnumbermask($("#submissiontotal").val());
    let emoney = clearnumbermask($("#emoney").val());

    $('.amount').each(function (e) {
        etotal = etotal + clearnumbermask($(this).val());
    });

    let eremaining = esubmissiontotal - etotal;

    $("#reporttotal").val(Number(etotal).toLocaleString('id-ID'));
    $("#remainingtotal").val(Number(eremaining).toLocaleString('id-ID'));
}

$("#save").click(function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Apakah Anda Yakin?',
        text: "Menyimpan data ini!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Iya, Simpan Data ini!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.LoadingOverlay("show");
            var formdata1 = $("#dataform1").serializeArrayFormJSON();
            var formdata2 = $("#dataform2").serializeArrayFormJSON();
            var formdata3 = $("#dataform3").serializeArrayFormJSON();
            var reqtable = $('#reqtable').serializeArrayTableJSON();
            $.ajax({
                url: "{!! route('admin.costsubmission.store') !!}",
                type: "post",
                data: {
                    "data": {
                        formdata1: formdata1,
                        formdata2: formdata2,
                        formdata3: formdata3,
                        reqtable: reqtable,
                    }
                },
                success: function (response) {
                    $.LoadingOverlay("hide");
                    if (response.status == "success") {
                        window.location = "{!! route('admin.costsubmission.index') !!}";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    $.LoadingOverlay("hide");
                    toastr.error(xhr.responseJSON.message);
                }
            });
        }
    })
});