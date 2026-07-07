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
                                            <h3 class="card-title">Data Group Menu</h3>
                                        </div>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="groupname">Nama</label>
                                                    <input type="text" id="groupname" name="groupname"
                                                        class="form-control" value="{{ $rawGroupMenu->name }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="groupdesc">Deskripsi</label>
                                                    <textarea rows="6" id="groupdesc" name="groupdesc" class="form-control">{{ $rawGroupMenu->description }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->

                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between">
                                            <h3 class="card-title">Daftar Pengguna</h3>
                                        </div>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <table id="user_table" class="table table-hover display">
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {!! $pengguna !!}
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.card-body -->

                                </div>
                                <!-- /.card -->
                            </div>
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between">
                                            <h3 class="card-title">Data Akses Module</h3>
                                        </div>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <table id="access_table" class="table table-hover display">
                                            <thead>
                                                <tr>
                                                    <th style="display: none">groid</th>
                                                    <th style="display: none">id</th>
                                                    <th>Module</th>
                                                    <th>View</th>
                                                    <th>Add</th>
                                                    <th>Update</th>
                                                    <th>Delete</th>
                                                    <th>Detail</th>
                                                    <th>Approval</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {!! $html !!}
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.card-body -->

                                </div>
                                <!-- /.card -->
                            </div>
                        </div>
                    </div>
                    <!-- /.card-footer -->
                    <div class="card-footer">
                        <a href="{{ route('admin.groupmenumodules.index') }}" class="btn btn-default">Cancel</a>
                        <button id="save" class="btn btn-info float-right">Submit</button>
                    </div>
                    <!-- /.card-footer -->
                </div>
                <!-- /.col -->
            </form>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
@endsection

@push('scripts')
    <script>
        $("#save").click(function(e) {
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
                    // $.LoadingOverlay("show");
                    var accessdata = $("#access_table").serializeFormJSON();

                    $.ajax({
                        type: "PUT",
                        url: "{!! route('admin.groupmenumodules.update', encrypt($rawGroupMenu->pk_groupmenu_id)) !!}",
                        data: {
                            "data": {
                                accessdata: accessdata,
                                groupname: $('#groupname').val(),
                                groupdesc: $('#groupdesc').val(),
                            }
                        },
                        success: function(response) {
                            // $.LoadingOverlay("hide");
                            if (response.status == "success") {
                                window.location = "{!! route('admin.groupmenumodules.index') !!}";
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
