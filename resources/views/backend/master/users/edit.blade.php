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
            <form method="POST" action="{{ route('admin.users.update', Crypt::encrypt($datauser->pk_user_id)) }}"
                id="form">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between">
                                    <h3 class="card-title">Data Pengguna</h3>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="emp_no">Employee No</label>
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control rounded-0" id="emp_no"
                                                    name="emp_no" value="{{ $datauser->employee_no }}">
                                                <!-- <span class="input-group-append">
                                                    <button type="button" id="emp_search" name="emp_search"
                                                        class="btn btn-info btn-flat">Cari</button>
                                                </span> -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="form-group">
                                            <label for="emp_name">Nama</label>
                                            <input type="text" id="emp_name" name="emp_name" class="form-control"
                                                value="{{ $datauser->name }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" id="password" name="password" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="password_confirmation">Ulangi Password</label>
                                            <input type="password" id="password_confirmation" name="password_confirmation"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Halaman Utama</label>
                                    <div class="input-group mb-3">
                                        <select id="default_page" name="default_page" class="form-control select2">
                                            <option value="">Pilih Halaman Utama</option>
                                            @foreach ($datamodule as $itemmodule)
                                                <option value="{{ encryptId($itemmodule->pk_module_id) }}"
                                                    {{ $itemmodule->pk_module_id == $datauser->fk_module_id ? 'Selected' : '' }}>
                                                    {{ $itemmodule->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" class="form-control custom-select">
                                        <option value="1" {{ $datauser->active == 1 ? 'Selected' : '' }}>Aktif
                                        </option>
                                        <option value="0" {{ $datauser->active == 0 ? 'Selected' : '' }}>Non Aktif
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- /.card-body -->

                            <!-- /.card-footer -->
                            <div class="card-footer">
                                <a class="btn btn-default" href="{{ URL::previous() }}">Cancel</a>
                                <button type="submit" class="btn btn-info float-right">Submit</button>
                            </div>
                            <!-- /.card-footer -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between">
                                    <h3 class="card-title">Role</h3>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <input type="hidden" class="form-control rounded-0" id="roledata" name="roledata">
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <div class="input-group mb-3">
                                        <select id="role" name="role" class="form-control custom-select">
                                            <option value="">Select</option>
                                            @foreach ($datarole as $item)
                                                <option value="{{ Crypt::encrypt($item->pk_role_id) }}">
                                                    {{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-success ml-2" id="addrole"><i
                                                class="fa fa-plus"></i></button>
                                    </div>
                                </div>

                                <table class="table table-striped" id="roletable" name="roletable">
                                    <thead>
                                        <tr>
                                            <th style="display:none">unique</th>
                                            <th style="display:none">id</th>
                                            <th>Role Name</th>
                                            <th style="width: 40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($datauserrole as $item)
                                            <tr>
                                                <td style="display:none">{{ Crypt::encrypt($item->pk_userrole_id) }}</td>
                                                <td style="display:none">{{ Crypt::encrypt($item->fk_role_id) }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td><button class="btn btn-sm btn-success ml-2 delete-role"><i
                                                            class="fa fa-trash"></i></button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.card -->

                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between">
                                    <h3 class="card-title">Group Menu</h3>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <input type="hidden" class="form-control rounded-0" id="groupdata" name="groupdata">
                                <div class="form-group">
                                    <label for="groupmenu">Group Menu</label>
                                    <div class="input-group mb-3">
                                        <select id="groupmenu" name="groupmenu" class="form-control custom-select">
                                            <option value="">Select</option>
                                            @foreach ($datagroupmenu as $item)
                                                <option value="{{ Crypt::encrypt($item->pk_groupmenu_id) }}">
                                                    {{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-success ml-2" id="addgroup"><i
                                                class="fa fa-plus"></i></button>
                                    </div>
                                </div>

                                <table class="table table-striped" id="grouptable" name="grouptable">
                                    <thead>
                                        <tr>
                                            <th style="display:none">unique</th>
                                            <th style="display:none">id</th>
                                            <th>Group Name</th>
                                            <th style="width: 40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($datausergroupmenu as $item)
                                            <tr>
                                                <td style="display:none">{{ Crypt::encrypt($item->pk_usergroupmenu_id) }}
                                                </td>
                                                <td style="display:none">{{ Crypt::encrypt($item->fk_groupmenu_id) }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td><button class="btn btn-sm btn-success ml-2 delete-group"><i
                                                            class="fa fa-trash"></i></button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                            <!-- /.card-footer -->
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
                <!-- /.col -->
            </form>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->

        <!-- /.modal -->
        <!-- <div class="modal fade" id="modal-data" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Daftar Karyawan</h4>
                        <button type="button" class="close" onclick=" $('#modal-data').modal('hide');"
                            aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table id="dataTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No Karyawan</th>
                                    <th>Nama</th>
                                    <th>Posisi</th>
                                    <th>Divisi</th>
                                    <th>Tindakan</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default"
                            onclick=" $('#modal-data').modal('hide');">Close</button>
                    </div>
                </div> 
            </div>
        </div> -->
    </section>
    <!-- Main content -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // var obj_table = $("#dataTable").DataTable({
            //     "responsive": true,
            //     "lengthChange": false,
            //     "processing": true,
            //     "serverSide": true,
            //     "autoWidth": false,
            //     "ajax": {
            //         url: "{!! route('admin.users.employeedatatables') !!}",
            //         type: "POST",
            //         data: {
            //             "_token": "{{ csrf_token() }}",
            //         }
            //     },
            //     "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            // });

            $('#emp_search').click(function(e) {
                e.preventDefault();
                $('#modal-data').modal('show');
            });

            /*$('#dataTable tbody').on('click', '.selected-item', function() {
                var row = $(this).closest('tr');
                var data = obj_table.row(row).data();
                var id = $(this).attr("data-id");
                $('#emp_id').val(id);
                $('#emp_no').val(data[0]);
                $('#emp_name').val(data[1]);

                $('#modal-data').modal('hide');
            });*/

            $("#addgroup").click(function(e) {
                e.preventDefault();
                var keys = $("#keys").val();
                if ($("#groupmenu").val() == "") {
                    return;
                }

                var groupname = $("#groupmenu option:selected").text();
                var id = $("#groupmenu").val();

                //check jika sudah diinput
                var tblbody = $('#grouptable').find('tbody')
                var found = 'false';
                $.each($(tblbody).find('tr'), function(key, value) {
                    if ($(this).find('td').eq(2).text() == groupname) {
                        found = 'true';
                        return;
                    }
                });

                if (found == 'true') {
                    return;
                }
                $('#grouptable tbody').append(
                    '<tr><td style="display:none"></td><td style="display:none">' + id + '</td><td>' +
                    groupname +
                    '</td><td><button class="btn btn-sm btn-success ml-2 delete-group"><i class="fa fa-trash"></i></button></td></tr>'
                );

            });

            $("#grouptable").on('click', '.delete-group', function() {
                $(this).closest('tr').remove();
            });

            $("#addrole").click(function(e) {
                e.preventDefault();
                var keys = $("#keys").val();
                if ($("#role").val() == "") {
                    return;
                }

                var rolename = $("#role option:selected").text();

                var id = $("#role").val();

                //check jika sudah diinput
                var tblbody = $('#roletable').find('tbody')
                var found = 'false';
                $.each($(tblbody).find('tr'), function(key, value) {
                    console.log($(this).find('td').eq(2).text());
                    console.log(rolename);
                    if ($(this).find('td').eq(2).text() == rolename) {
                        found = 'true';
                        return;
                    }
                });

                if (found == 'true') {
                    return;
                }

                $('#roletable tbody').append('<tr><td style="display:none"></td><td style="display:none">' +
                    id + '</td><td>' + rolename +
                    '</td><td><button class="btn btn-sm btn-success ml-2 delete-role"><i class="fa fa-trash"></i></button></td></tr>'
                );
            });

            $("#roletable").on('click', '.delete-role', function() {
                $(this).closest('tr').remove();
            });

            $("#form").submit(function() {
                $('#roledata').val(JSON.stringify($('#roletable').JsonFromTable()));
                $('#groupdata').val(JSON.stringify($('#grouptable').JsonFromTable()));
            });
        });
    </script>
@endpush
