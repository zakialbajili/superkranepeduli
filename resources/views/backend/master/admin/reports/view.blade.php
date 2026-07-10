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
                        <li class="breadcrumb-item"><a href="{{ $headerparam['parentlink'] }}"><i class="fas fa-home mr-1"></i>{{ $headerparam['parentname'] }}</a></li>
                        <li class="breadcrumb-item active">{{ $headerparam['headername'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            {{-- Wizard Progress --}}
            @php
                $statusId = $report->status_pelaporan ?? '5';
                $steps = [
                    ['id' => 5, 'label' => 'Open', 'icon' => 'fa-flag', 'date' => $report->created_date, 'desc' => 'Laporan bahaya telah dibuat'],
                    ['id' => 6, 'label' => 'On Progress',   'icon' => 'fa-spinner', 'date' => ($statusId == 6 ? $report->updated_date : null), 'desc' => 'Sedang dalam tindak lanjut'],
                    ['id' => 7, 'label' => 'Closed',    'icon' => 'fa-check-circle', 'date' => ($statusId >= 7 ? $report->updated_date : null), 'desc' => 'Laporan telah diselesaikan'],
                ];
                $currentStep = (int) $statusId;
            @endphp

            <div class="card card-solid mb-4">
                <div class="card-body py-4">
                    <div class="row wizard-steps">
                        @foreach ($steps as $i => $step)
                            @php
                                $done = $currentStep >= $step['id'];
                                $active = $currentStep == $step['id'];
                                $last = $i === count($steps) - 1;
                            @endphp
                            <div class="col-4 text-center position-relative">
                                @if (!$last)
                                    <div class="wizard-line {{ $done ? 'done' : '' }}" style="position:absolute; top:24px; right:-50%; width:100%; height:3px; background:#e9ecef; z-index:0;"></div>
                                @endif
                                <div class="wizard-icon mx-auto {{ $done ? 'done' : '' }} {{ $active ? 'active' : '' }}" style="width:50px; height:50px; border-radius:50%; background:{{ $done ? '#28a745' : '#e9ecef' }}; display:flex; align-items:center; justify-content:center; position:relative; z-index:1; border:3px solid {{ $active ? '#ffc107' : ($done ? '#28a745' : '#dee2e6') }}; transition:all 0.3s;">
                                    <i class="fas {{ $step['icon'] }} text-white" style="font-size:20px;"></i>
                                </div>
                                <div class="mt-2">
                                    <strong class="{{ $active ? 'text-warning' : ($done ? 'text-success' : 'text-muted') }}" style="font-size:14px;">{{ $step['label'] }}</strong>
                                    <p class="text-muted mb-0" style="font-size:11px;">{{ $step['desc'] }}</p>
                                    @if ($step['date'])
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($step['date'])->format('d-m-Y H:i') }}</small>
                                    @elseif ($done)
                                        <small class="text-muted">—</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Konten Detail --}}
            <div class="row">
                {{-- Kiri: Data Pelapor + Informasi --}}
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-circle mr-2"></i>Data Pelapor</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><i class="fas fa-id-badge text-primary mr-1"></i> No. Karyawan</label>
                                        <p class="form-control-static">{{ $report->employee_no ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-sm-8">
                                    <div class="form-group">
                                        <label><i class="fas fa-user text-primary mr-1"></i> Nama Lengkap</label>
                                        <p class="form-control-static">{{ $report->full_name ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-briefcase text-primary mr-1"></i> Posisi</label>
                                <p class="form-control-static">{{ $report->posisi ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i>Informasi Bahaya</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt text-danger mr-1"></i> Tanggal Pelaporan</label>
                                <p class="form-control-static">{{ $report->tgl_pelaporan ? \Carbon\Carbon::parse($report->tgl_pelaporan)->format('d-m-Y') : '-' }}</p>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-clock text-danger mr-1"></i> Shift</label>
                                        <p class="form-control-static">{{ $report->shift_name ?? $report->shift ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-clipboard-list text-danger mr-1"></i> Data Pelaporan</label>
                                        <p class="form-control-static">{{ $report->data_pelaporan_name ?? $report->data_pelaporan ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-building text-danger mr-1"></i> Lokasi Bahaya</label>
                                <p class="form-control-static">{{ $report->lokasi_bahaya_name ?? $report->lokasi_bahaya ?? '-' }}</p>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-tag text-danger mr-1"></i> Kategori Bahaya</label>
                                <p class="form-control-static">{{ $report->kategori_bahaya_name ?? $report->kategori_bahaya ?? '-' }}</p>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-list-ul text-danger mr-1"></i> Jenis Bahaya</label>
                                <p class="form-control-static">{{ $report->desc_kategori_bahaya ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kanan: Detail Temuan + Dokumen + Status --}}
                <div class="col-md-6">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-pen-alt mr-2"></i>Detail Temuan</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label><i class="fas fa-exclamation-circle text-warning mr-1"></i> Deskripsi Temuan Bahaya</label>
                                <p class="form-control-static">{{ $report->desc_temuan_bahaya ?? '-' }}</p>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-tools text-warning mr-1"></i> Rekomendasi Perbaikan</label>
                                <p class="form-control-static">{{ $report->rekomendasi_perbaikan ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-file-upload mr-2"></i>Dokumen</h3>
                        </div>
                        <div class="card-body">
                            @if ($report->document)
                                <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded border">
                                    <div>
                                        <i class="fas fa-paperclip text-info mr-2 fa-lg"></i>
                                        <span>Dokumen / Foto Terlampir</span>
                                    </div>
                                    <div>
                                        <a href="{{ asset($report->document) }}" target="_blank" class="btn btn-sm btn-info mr-1">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                        <a href="{{ asset($report->document) }}" download class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> Tidak ada dokumen terlampir</p>
                            @endif
                        </div>
                    </div>

                    <div class="card card-secondary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-tasks mr-2"></i>Status & Tindak Lanjut</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-flag text-secondary mr-1"></i> Status Laporan</label>
                                        <p class="form-control-static">
                                            @php
                                                $statusClass = match($statusId) {
                                                    '5' => 'badge badge-warning',
                                                    '6' => 'badge badge-info',
                                                    '7' => 'badge badge-success',
                                                    default => 'badge badge-secondary'
                                                };
                                            @endphp
                                            <span class="{{ $statusClass }}" style="font-size:14px;">
                                                {{ $report->status_pelaporan_name ?? $report->status_pelaporan ?? '-' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-hourglass-end text-secondary mr-1"></i> Due Date</label>
                                        <p class="form-control-static">{{ $report->due_date ? \Carbon\Carbon::parse($report->due_date)->format('d-m-Y') : '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-building text-secondary mr-1"></i> Dept. Penanggung Jawab</label>
                                        <p class="form-control-static">{{ $report->dept_penanggungjwb_name ?? $report->dept_penanggungjwb_name ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-hard-hat text-secondary mr-1"></i> Nama Pengawas</label>
                                        <p class="form-control-static">{{ $report->nama_pengawas ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-default mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <a href="{{ route('admin.reports.edit', encrypt($report->pk_hsepelaporanbahaya_id)) }}" class="btn btn-info">
                        <i class="fas fa-external-link-square-alt mr-1"></i> Edit Laporan
                    </a>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
<style>
    .wizard-steps .wizard-icon {
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .wizard-steps .wizard-icon.done {
        box-shadow: 0 2px 8px rgba(40,167,69,0.3);
    }
    .wizard-steps .wizard-icon.active {
        box-shadow: 0 2px 10px rgba(255,193,7,0.4);
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .wizard-line.done {
        background: #28a745 !important;
    }
    .form-control-static {
        margin-bottom: 0;
        padding-top: 2px;
        font-size: 1rem;
    }
    .card-outline .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
</style>
@endpush
