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
                        <li class="breadcrumb-item"><a href="{{ $headerparam['parentlink'] }}"><i
                                    class="fas fa-home mr-1"></i>{{ $headerparam['parentname'] }}</a></li>
                        <li class="breadcrumb-item active">{{ $headerparam['headername'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards --}}
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $totalLaporan }}</h3>
                            <p>Total Laporan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <a href="{{ route('admin.reports.index') }}" class="small-box-footer">
                            Lihat Semua <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $openCount }}</h3>
                            <p>Open</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-flag"></i>
                        </div>
                        <a href="{{ route('admin.reports.index', ['filter_status' => encryptId('5')]) }}" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $progressCount }}</h3>
                            <p>On Progress</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <a href="{{ route('admin.reports.index', ['filter_status' => encryptId('6')]) }}" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $closedCount }}</h3>
                            <p>Closed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="{{ route('admin.reports.index', ['filter_status' => encryptId('7')]) }}" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Status Laporan</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartStatus" style="height:250px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-th-list mr-2"></i>Kategori Bahaya</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartKategori" style="height:250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts: Jenis Bahaya --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Jenis Kondisi Tidak Aman</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartJenisKondisi" style="height:250px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-times mr-2"></i>Jenis Tindakan Tidak Aman</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartJenisTindakan" style="height:250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="row">
                <div class="col-12">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Laporan Per Bulan</h3>
                            <div class="card-tools">
                                <div class="input-group input-group-sm" style="width:180px;">
                                    <input type="month" id="filter-tahun" class="form-control form-control-sm" value="{{ $tahunIni }}-01">
                                    <div class="input-group-append">
                                        <button type="button" id="btn-reload-chart" class="btn btn-info btn-sm">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="chartBulan" style="height:300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Due Date DataTable --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-hourglass-half mr-2"></i>Laporan Mendekati Due Date (30 Hari)</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="dueDateTable" class="table table-striped display" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 75px;">Due Date</th>
                                            <th style="min-width: 100px;">Sisa Hari</th>
                                            <th style="min-width: 100px;">Pelapor</th>
                                            <th style="min-width: 100px;">No. Karyawan</th>
                                            <th style="min-width: 100px;">Lokasi</th>
                                            <th style="min-width: 200px;">Temuan</th>
                                            <th style="min-width: 100px;">Kategori</th>
                                            <th style="min-width: 100px;">Status</th>
                                            <th style="min-width: 50px;">Aksi</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
@include('js.admin.dashboard.index')
@endpush