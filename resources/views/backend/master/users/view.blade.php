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
                                    <input type="hidden" class="form-control rounded-0" id="emp_id" name="emp_id"
                                        value="{{ $datauser->fk_employee_id }}">
                                    <div class="form-group">
                                        <label for="emp_no">User</label>
                                        <input type="text" class="form-control rounded-0" id="emp_no" name="emp_no"
                                            value="{{ $datauser->employee_no }}" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <label for="emp_name">Nama</label>
                                        <input type="text" id="emp_name" name="emp_name" class="form-control" readonly
                                            value="{{ $datauser->full_name }}"">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <input type="text" class="form-control rounded-0" id="status" name="status"
                                    value="{{ $datauser->active == 1 ? 'Aktif' : 'Non Aktif' }}" readonly>
                            </div>
                        </div>
                        <!-- /.card-body -->

                        <!-- /.card-footer -->
                        <div class="card-footer">
                            <a class="btn btn-default" href="{{ URL::previous() }}">Cancel</a>
                        </div>
                        <!-- /.card-footer -->
                    </div>
                    <!-- /.card -->
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table class="table table-striped" id="roletable" name="roletable">
                                <thead>
                                    <tr>
                                        <th>Role Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($datauserrole as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.card -->

                    <div class="card">
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table class="table table-striped" id="grouptable" name="grouptable">
                                <thead>
                                    <tr>
                                        <th>Group Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($datausergroupmenu as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
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
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
@endsection
