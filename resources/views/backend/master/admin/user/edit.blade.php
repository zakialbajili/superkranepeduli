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
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ route('admin.user-mobile.update', encrypt($datauser->pk_user_id)) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Data User Mobile</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="employee_no">Employee No</label>
                                            <input type="text" id="employee_no" name="employee_no" class="form-control"
                                                value="{{ old('employee_no', $datauser->employee_no) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="form-group">
                                            <label for="full_name">Nama</label>
                                            <input type="text" id="full_name" name="full_name" class="form-control"
                                                value="{{ old('full_name', $datauser->full_name) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="posisi">Posisi</label>
                                            <input type="text" id="posisi" name="posisi" class="form-control"
                                                value="{{ old('posisi', $datauser->posisi) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="birth_date">Tanggal Lahir</label>
                                            <input type="date" id="birth_date" name="birth_date" class="form-control"
                                                value="{{ old('birth_date', $datauser->birth_date) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="active">Status</label>
                                    <select id="active" name="active" class="form-control custom-select">
                                        <option value="1" {{ $datauser->active == 1 ? 'Selected' : '' }}>Aktif</option>
                                        <option value="0" {{ $datauser->active == 0 ? 'Selected' : '' }}>Non Aktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a class="btn btn-default" href="{{ route('admin.user-mobile.index') }}">Cancel</a>
                                <button type="submit" class="btn btn-info float-right">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
