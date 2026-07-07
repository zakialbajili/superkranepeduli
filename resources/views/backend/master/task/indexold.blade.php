@extends('backend.layouts.master')
@section('content')
    <style>
        /* Optional: Ensure the iframe takes up the full screen */
        body,
        html {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }
    </style>
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
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-body">
                            <form id="data-filter">
                                <div class="form-group">
                                    <label>Tanggal Transaksi</label>
                                    <input type="text" class="form-control daterange" id="transactiondate"
                                        name="transactiondate" />
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="input-group mb-3">
                                        <select id="status" name="status" class="form-control custom-select">
                                            <option value="">Silahkan Pilih Status</option>
                                            {{-- @foreach ($rawStatus as $itemStatus)
                                                <option value="{{ encryptId($itemStatus->pk_projectmaster_id) }}">
                                                    {{ $itemStatus->name }}</option>
                                            @endforeach --}}
                                        </select>
                                    </div>
                                </div>
                                <button type="button" id="filter-data" class="btn btn-sm btn-success ml-2"><i
                                        class="fas fa-search"></i>
                                    Filter</button>
                                {{-- <button type="button" id="excel-data" class="btn btn-sm btn-danger ml-2"><i
                                        class="fas fa-file-excel"></i>
                                    Export</button> --}}
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-9">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Task Data</h3>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="dataTable" class="table table-striped display" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Tanggal Transaksi</th>
                                            <th>Kategori</th>
                                            <th>Deskripsi</th>
                                            <th>Status</th>
                                            <th>Tindakan</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
@endsection
@push('scripts')
    @include('js.task.index')
@endpush
