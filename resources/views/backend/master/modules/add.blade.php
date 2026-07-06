@extends('backend.layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $headerparam['headertag'] }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a
                                href="{{ $headerparam['parentlink'] }}">{{ $headerparam['parentname'] }}</a></li>
                        <li class="breadcrumb-item active">{{ $headerparam['headername'] }}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <div class="container-fluid">
            <form id="dataform">
                @csrf
                <div class="card card-solid">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between">
                                            <h3 class="card-title">Data Module</h3>
                                        </div>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="modulename">Nama</label>
                                                    <input type="text" id="modulename" name="modulename"
                                                        class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="moduledescription">Deskripsi</label>
                                                    <textarea rows="6" id="moduledescription" name="moduledescription" class="form-control"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->

                                </div>
                                <!-- /.card -->
                            </div>
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between">
                                            <h3 class="card-title">Pengaturan Routes</h3>
                                        </div>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label for="routeview" class="col-sm-2 col-form-label">View</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="routeview" name="routeview"
                                                    placeholder="Routes View">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="routeadd" class="col-sm-2 col-form-label">Add</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="routeadd" name="routeadd"
                                                    placeholder="Routes Add">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="routeedit" class="col-sm-2 col-form-label">Edit</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="routeedit" name="routeedit"
                                                    placeholder="Routes Edit">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="routedelete" class="col-sm-2 col-form-label">Delete</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="routedelete"
                                                    name="routedelete" placeholder="Routes Delete">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="routedetail" class="col-sm-2 col-form-label">Detail</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="routedetail"
                                                    name="routedetail" placeholder="Routes Detail">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.card -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between">
                                            <h3 class="card-title">Approval Routes</h3>
                                        </div>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <button id="addapproval" class="btn btn-sm btn-success float-right mb-2"><i
                                                class="fas fa-plus"></i>Tambah</button>
                                        <table class="table table-striped table-bordered" id="approvaltable"
                                            name="approvaltable">
                                            <thead>
                                                <tr>
                                                    <th style="display:none">id</th>
                                                    <th style="display:none">idstat</th>
                                                    <th>Status</th>
                                                    <th>Tipe</th>
                                                    <th>Kategori</th>
                                                    <th style="display:none">idrole</th>
                                                    <th>Role</th>
                                                    <th style="display:none">idnextstat</th>
                                                    <th>Status Selanjutnya</th>
                                                    <th>Catatan</th>
                                                    <th style="width: 100px"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.card-body -->
                                    <!-- /.card-footer -->
                                </div>
                                <!-- /.card -->
                            </div>
                        </div>
                    </div>
                    <!-- /.card-footer -->
                    <div class="card-footer">
                        <button id="cancel" class="btn btn-default">Cancel</button>
                        <button id="save" class="btn btn-info float-right">Submit</button>
                    </div>
                    <!-- /.card-footer -->
                </div>
                <!-- /.col -->
            </form>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->

        <!-- /.modal -->
        <div class="modal fade" id="modal-data" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Data Approval Routes</h4>
                        <button type="button" class="close" onclick=" $('#modal-data').modal('hide');"
                            aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="approvaldata" name="approvaldata">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select id="status" name="status" class="form-control select2"
                                            style="width: 100%;">
                                            @foreach ($datanextstatus as $item)
                                                <option value='{{ Crypt::encrypt($item->pk_projectmaster_id) }}'>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="type">Tipe</label>
                                        <select id="type" name="type" class="form-control select2"
                                            style="width: 100%;">
                                            <option value="Draft"> Draft</option>
                                            <option value="Waiting"> Waiting</option>
                                            <option value="Rejected"> Rejected</option>
                                            <option value="Closed"> Closed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="category">Kategori</label>
                                        <select id="category" name="category" class="form-control select2"
                                            style="width: 100%;">
                                            <option value="Locked"> Locked</option>
                                            <option value="Unlocked"> Unlocked</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="roles">Roles</label>
                                        <select id='roles' name="roles" multiple='multiple'>
                                            @foreach ($datarole as $item)
                                                <option value='{{ Crypt::encrypt($item->pk_role_id) }}'>
                                                    {{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="nextstatus">Status Selanjutnya</label>
                                        <select id='nextstatus' name="nextstatus" multiple='multiple'>
                                            @foreach ($datanextstatus as $item)
                                                <option value='{{ Crypt::encrypt($item->pk_projectmaster_id) }}'>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="moduledescription">Catatan</label>
                                        <textarea id="notes" rows="5" class="form-control"></textarea>
                                        {{-- <textarea rows="3" id="moduledescription" name="moduledescription" class="form-control"></textarea> --}}
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default"
                            onclick=" $('#modal-data').modal('hide');">Close</button>
                        <button id="saveapprovaldata" class="btn btn-info float-right">Submit</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
    </section>
    <!-- Main content -->
@endsection

@push('scripts')
    <script>
        var $appno = 1;
        $(document).ready(function() {
            $('.select2').select2();
            $('#roles').multiSelect({
                selectableHeader: "<div class='bg-secondary color-palette text-center'>Selectable items</div>",
                selectionHeader: "<div class='bg-secondary color-palette text-center'>Selection items</div>",
            });
            $('#nextstatus').multiSelect({
                selectableHeader: "<div class='bg-secondary color-palette text-center'>Selectable items</div>",
                selectionHeader: "<div class='bg-secondary color-palette text-center'>Selection items</div>",
            });
            $('#addapproval').click(function(e) {
                e.preventDefault();

                $("#status").val("");
                $('#status').trigger('change');
                $("#type").val("");
                $('#type').trigger('change');
                $("#category").val("");
                $('#category').trigger('change');
                $('#notes').val('');
                $('#modal-data').modal('show');
            });
            $('#saveapprovaldata').click(function(e) {
                e.preventDefault();

                if ($('#roles').val() == '') {
                    toastr.error('Roles Harus diisi');
                    return;
                }

                if ($('#nextstatus').val() == '') {
                    toastr.error('Status Selanjutnya Harus diisi');
                    return;
                }

                var roletext = '';
                $('#roles option:selected').each(function(e) {
                    roletext += $(this).text() + ",";
                });
                var found = false;
                $('#approvaltable tbody tr').each(function() {
                    if ($(this).attr('id') == $('#approvalid').val()) {
                        $(this).find("td:eq(1)").text($('#status').val());
                        $(this).find("td:eq(2)").text($('#status option:selected').text());
                        $(this).find("td:eq(3)").text($('#type').val());
                        $(this).find("td:eq(4)").text($('#category').val());
                        $(this).find("td:eq(5)").text($('#roles').val());
                        $(this).find("td:eq(6)").text(roletext);
                        $(this).find("td:eq(7)").text($('#nextstatus').val());
                        $(this).find("td:eq(8)").text(nextstatustext);
                        $(this).find("td:eq(9)").text($('#notes').val());
                        found = true;
                    }
                });

                var nextstatustext = '';
                $('#nextstatus option:selected').each(function(e) {
                    nextstatustext += $(this).text() + ",";
                });
                if (found == false) {
                    var htmldata = '<tr id="' + $appno + '">' +
                        '<td style="display:none">' + $appno + '</td>' +
                        '<td style="display:none">' + $('#status').val() + '</td>' +
                        '<td>' + $('#status option:selected').text() + '</td>' +
                        '<td>' + $('#type').val() + '</td>' +
                        '<td>' + $('#category').val() + '</td>' +
                        '<td style="display:none">' + $('#roles').val() + '</td>' +
                        '<td>' + roletext + '</td>' +
                        '<td style="display:none">' + $('#nextstatus').val() + '</td>' +
                        '<td>' + nextstatustext + '</td>' +
                        '<td>' + $('#notes').val() + '</td>' +
                        '<td><button class="btn btn-sm btn-success edit-approval"><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-danger ml-2 delete-approval"><i class="fa fa-trash"></i></button></td>' +
                        '</tr>';
                    $("#approvaltable tbody").append(htmldata);

                }
                $appno++;

                $('#modal-data').modal('hide');
            });
            $("#approvaltable").on('click', '.delete-approval', function() {
                $(this).closest('tr').remove();
            });
        });

        $("#approvaltable").on('click', '.edit-approval', function(e) {
            e.preventDefault();
            var $row = $(this).closest("tr"),
                $tds = $row.find("td"),
                $data = [];
            $.each($tds, function() { // Visits every single <td> element
                $data.push($(this).text()); // Prints out the text within the <td>
            });

            $('#approvalid').val($data[0]);
            $('#status').val($data[1]);
            $('#status').trigger('change');
            $('#type').val($data[3]);
            $('#type').trigger('change');
            $('#category').val($data[4]);
            $('#category').trigger('change');
            $('#notes').val($data[9]);

            var $roles = $data[5].split(',');
            $('#roles').multiSelect('deselect_all');
            $('#roles').multiSelect('select', $roles);

            var $status = $data[7].split(',');
            $('#nextstatus').multiSelect('deselect_all');
            $('#nextstatus').multiSelect('select', $status);

            $('#modal-data').modal('show');
        });
        $("#cancel").click(function(e) {
            e.preventDefault();
            window.location = "{!! route('admin.modules.index') !!}";
        });
        $('#save').click(function(e) {
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

                    var dataform = JSON.stringify($("#dataform").serializeJSON());
                    var approvaldata = $('#approvaltable').JsonFromTable();
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.modules.store') !!}",
                        data: {
                            dataform: dataform,
                            approvaldata: approvaldata
                        },
                        success: function(response) {
                            $.LoadingOverlay("hide");
                            if (response.status == "success") {
                                window.location = "{!! route('admin.modules.index') !!}";
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    });
                }
            })


        });
    </script>
@endpush
