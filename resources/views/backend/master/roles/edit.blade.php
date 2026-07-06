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
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between">
                                            <h3 class="card-title">Data Role</h3>
                                        </div>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="row">
                                            <input type="hidden" id="roleid" name="roleid" class="form-control"
                                            value="{{ Crypt::encrypt($rawRoleHeader->pk_role_id) }}">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="rolename">Nama</label>
                                                    <input type="text" id="rolename" name="rolename"
                                                        class="form-control" value="{{ $rawRoleHeader->name }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="roledesc">Deskripsi</label>
                                                    <textarea rows="3" id="roledesc" name="roledesc" class="form-control"> {{ $rawRoleHeader->description }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                </div>
                                <!-- /.card -->
                            </div>
                            <div class="col-md-6">
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
                                <!-- /.card-body -->
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
                $.LoadingOverlay("show");
                var dataform = $("#dataform").serializeArrayFormJSON();
                $.ajax({
                    url: "{!! route('admin.roles.update', encrypt($rawRoleHeader->pk_role_id)) !!}",
                    type: "put",
                    data: {
                        "data": {
                            dataform: dataform,
                        }
                    },
                    success: function(response) {
                        $.LoadingOverlay("hide");
                        if (response.status == "success") {
                            window.location = response.url;
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        $.LoadingOverlay("hide");
                        toastr.error(xhr.responseJSON.message);
                    }
                });
            }
        })

        });
    </script>
@endpush
