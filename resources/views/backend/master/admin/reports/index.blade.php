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
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Filter</h5>
                        </div>
                        <div class="card-body">
                            <form action="" id="filter-data">
                                <div class="form-group">
                                    <label for="tgl_pelaporan">Tanggal Pelaporan</label>
                                    <input type="text" name="tgl_pelaporan" id="tgl_pelaporan" class="form-control"
                                        placeholder="Pilih rentang tanggal">
                                </div>
                                <div class="form-group">
                                    <label for="due_date">Due Date</label>
                                    <input type="text" name="due_date" id="due_date" class="form-control"
                                        placeholder="Pilih rentang tanggal">
                                </div>
                                <div class="form-group">
                                    <label for="shift">Shift</label>
                                    <select name="shift" id="shift" class="form-control custom-select">
                                        <option value="">Pilih Shift</option>
                                        {!! $dataShift !!}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="data_pelaporan">Data Pelaporan</label>
                                    <select name="data_pelaporan" id="data_pelaporan" class="form-control custom-select">
                                        <option value="">Pilih Data Pelaporan</option>
                                        {!! $dataDataPelaporan !!}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="lokasi_bahaya">Lokasi</label>
                                    <select name="lokasi_bahaya" id="lokasi_bahaya" class="form-control custom-select">
                                        <option value="">Pilih Lokasi</option>
                                        {!! $dataLokasi !!}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="kategori_bahaya">Kategori Bahaya</label>
                                    <select name="kategori_bahaya" id="kategori_bahaya" class="form-control custom-select">
                                        <option value="">Pilih Kategori</option>
                                        {!! $dataKategori !!}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="desc_kategori_bahaya">Jenis Bahaya</label>
                                    <select name="desc_kategori_bahaya" id="desc_kategori_bahaya"
                                        class="form-control custom-select">
                                        <option value="">Pilih Jenis Bahaya</option>
                                        {!! $dataJenisBahaya !!}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status_pelaporan">Status Laporan</label>
                                    <select name="status_pelaporan" id="status_pelaporan"
                                        class="form-control custom-select">
                                        <option value="">Pilih Status</option>
                                        {!! $dataStatus !!}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="dept_penanggungjwb">Dept. Penanggung Jawab</label>
                                    <select name="dept_penanggungjwb" id="dept_penanggungjwb"
                                        class="form-control custom-select">
                                        <option value="">Pilih Departemen</option>
                                        {!! $dataDepartemen !!}
                                    </select>
                                </div>
                                <div class="d-flex gap-1">
                                    <button type="button" id="btn-filter" class="btn btn-sm btn-success">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <button type="button" id="btn-reset" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button type="button" id="btn-export" class="btn btn-sm btn-danger">
                                        <i class="fas fa-file-alt"></i> Export
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-9">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Daftar Pelaporan Bahaya</h3>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="dataTable" class="table table-striped display" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 100px;">No. Karyawan</th>
                                            <th style="min-width: 100px;">Nama Pelapor</th>
                                            <th style="min-width: 100px;">Posisi</th>
                                            <th style="min-width: 100px;">Tanggal</th>
                                            <th style="min-width: 100px;">Lokasi</th>
                                            <th style="min-width: 70px;">Shift</th>
                                            <th style="min-width: 100px;">Data Pelaporan</th>
                                            <th style="min-width: 120px;">Kategori Bahaya</th>
                                            <th style="min-width: 150px;">Jenis Bahaya</th>
                                            <th style="min-width: 200px;">Deskripsi Temuan</th>
                                            <th style="min-width: 200px;">Rekomendasi</th>
                                            <th style="min-width: 150px;">Dept. Penanggung Jawab</th>
                                            <th style="min-width: 100px;">Pengawas</th>
                                            <th style="min-width: 100px;">Due Date</th>
                                            <th style="min-width: 100px;">Status</th>
                                            <th style="min-width: 100px;">Aksi</th>
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
    @include('js.admin.reports.index')
@endpush