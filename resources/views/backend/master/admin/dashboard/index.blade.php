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
                        <a href="{{ route('admin.reports.index', ['filter_status' => encryptId('5')]) }}"
                            class="small-box-footer">
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
                        <a href="{{ route('admin.reports.index', ['filter_status' => encryptId('6')]) }}"
                            class="small-box-footer">
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
                        <a href="{{ route('admin.reports.index', ['filter_status' => encryptId('7')]) }}"
                            class="small-box-footer">
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
                            <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Jenis Kondisi Tidak Aman
                            </h3>
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
                                    <input type="month" id="filter-tahun" class="form-control form-control-sm"
                                        value="{{ $tahunIni }}-01">
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
                            <h3 class="card-title"><i class="fas fa-hourglass-half mr-2"></i>Laporan Mendekati Due Date (30
                                Hari)</h3>
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
            {{-- Rank Report DataTable --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-trophy mr-2"></i>Peringkat Pelapor Bahaya</h3>
                            <div class="card-tools">
                                <button type="button" id="btn-filter-rank" class="btn btn-sm btn-outline-primary"
                                    onclick="$('#modalFilterRank').modal('show')">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="rankTable" class="table display rank-table" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 80px;">Peringkat</th>
                                            <th style="min-width: 100px;">No. Pegawai</th>
                                            <th style="min-width: 150px;">Nama Pelapor</th>
                                            <th style="min-width: 120px;">Posisi</th>
                                            <th style="min-width: 120px;">Jumlah Laporan</th>
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

{{-- Modal Filter Peringkat --}}
<div class="modal fade" id="modalFilterRank" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-filter mr-2"></i>Filter Peringkat Pelapor</h5>
                <button type="button" class="close" data-dismiss="modal"
                    onclick="$('#modalFilterRank').modal('hide')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" id="rank-filter-start" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" id="rank-filter-end" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Rentang Waktu</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill filter-badge"
                                    data-range="current-month"><i class="fas fa-calendar-check mr-1"></i>Current
                                    Month</button>
                                <button type="button" class="btn btn-sm btn-light rounded-pill filter-badge"
                                    data-range="last-month">Last
                                    Month</button>
                                <button type="button" class="btn btn-sm btn-light rounded-pill filter-badge"
                                    data-range="last-30">30 Hari</button>
                                <button type="button" class="btn btn-sm btn-light rounded-pill filter-badge"
                                    data-range="last-90">90 Hari</button>
                                <button type="button" class="btn btn-sm btn-light rounded-pill filter-badge"
                                    data-range="ytd">YTD</button>
                                <button type="button" class="btn btn-sm btn-light rounded-pill filter-badge"
                                    data-range="all">Semua</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"
                    onclick="$('#modalFilterRank').modal('hide')">Batal</button>
                <button type="button" id="btn-apply-filter-rank" class="btn btn-primary">
                    <i class="fas fa-check mr-1"></i> Terapkan
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        /* ---- Rank Avatar ---- */
        .rank-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .bg-gold {
            background: linear-gradient(135deg, #f9a825, #ffd54f);
            color: #5d4037;
        }

        .bg-silver {
            background: linear-gradient(135deg, #90a4ae, #cfd8dc);
            color: #37474f;
        }

        .bg-bronze {
            background: linear-gradient(135deg, #a1887f, #d7ccc8);
            color: #3e2723;
        }

        /* ---- Rank Badge (jumlah laporan) ---- */
        .rank-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-gold {
            background: #fff8e1;
            color: #f57f17;
            border: 1px solid #ffcc02;
        }

        .badge-silver {
            background: #f5f5f5;
            color: #616161;
            border: 1px solid #bdbdbd;
        }

        .badge-bronze {
            background: #efebe9;
            color: #5d4037;
            border: 1px solid #bcaaa4;
        }

        .badge-primary {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #90caf9;
        }

        /* ---- Rank Trophy / Medal ---- */
        .rank-trophy {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: 15px;
            padding: 3px 10px;
            border-radius: 6px;
        }

        .rank-trophy.rank-1 {
            color: #f57f17;
        }

        .rank-trophy.rank-1 i {
            font-size: 20px;
            animation: trophy-pulse 1.8s ease-in-out infinite;
        }

        .rank-trophy.rank-2 {
            color: #78909c;
        }

        .rank-trophy.rank-2 i {
            font-size: 19px;
        }

        .rank-trophy.rank-3 {
            color: #a1887f;
        }

        .rank-trophy.rank-3 i {
            font-size: 19px;
        }

        .rank-number {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 6px;
            background: #f5f5f5;
            color: #757575;
            font-weight: 700;
            font-size: 14px;
        }

        @keyframes trophy-pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.15);
            }
        }

        /* ---- Row Highlight (menembus wrapper DataTables) ---- */
        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-gold,
        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-gold td {
            background: #fff9c4 !important;
            border-bottom: 2px solid #ffe082 !important;
        }

        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-silver,
        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-silver td {
            background: #eceff1 !important;
            border-bottom: 2px solid #cfd8dc !important;
        }

        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-bronze,
        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-bronze td {
            background: #fce4d6 !important;
            border-bottom: 2px solid #ffcc80 !important;
        }

        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-gold:hover,
        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-gold:hover td {
            background: #ffecb3 !important;
        }

        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-silver:hover,
        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-silver:hover td {
            background: #cfd8dc !important;
        }

        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-bronze:hover,
        #rankTable_wrapper .dataTables_scrollBody table.dataTable tbody tr.rank-bronze:hover td {
            background: #ffe0b2 !important;
        }

        .fw-bold {
            font-weight: 700 !important;
        }

        .gap-2 {
            gap: 8px;
        }
    </style>
@endpush

@push('scripts')
    @include('js.admin.dashboard.index')
@endpush