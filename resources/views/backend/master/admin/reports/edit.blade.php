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
            <form id="dataform">
                @csrf
                @method('PUT')
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
                                            <label for="employee_no"><i class="fas fa-id-badge text-primary mr-1"></i> No.
                                                Karyawan</label>
                                            <input type="text" id="employee_no" name="employee_no" class="form-control"
                                                placeholder="Nomor Karyawan" value="{{ $report->employee_no }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="form-group">
                                            <label for="full_name"><i class="fas fa-user text-primary mr-1"></i> Nama
                                                Lengkap</label>
                                            <input type="text" id="full_name" name="full_name" class="form-control"
                                                placeholder="Nama Lengkap" value="{{ $report->full_name }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="posisi"><i class="fas fa-briefcase text-primary mr-1"></i> Posisi</label>
                                    <input type="text" id="posisi" name="posisi" class="form-control"
                                        placeholder="Posisi / Jabatan" value="{{ $report->posisi }}">
                                </div>
                            </div>
                        </div>

                        <div class="card card-danger card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i>Informasi Bahaya</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="tgl_pelaporan"><i class="fas fa-calendar-alt text-danger mr-1"></i> Tanggal
                                        Pelaporan</label>
                                    <input type="date" id="tgl_pelaporan" name="tgl_pelaporan" class="form-control"
                                        value="{{ $report->tgl_pelaporan }}">
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="shift"><i class="fas fa-clock text-danger mr-1"></i> Shift</label>
                                            <select name="shift" id="shift" class="form-control custom-select">
                                                <option value="">-- Pilih Shift --</option>
                                                {!! $dataShift !!}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="data_pelaporan"><i
                                                    class="fas fa-clipboard-list text-danger mr-1"></i> Data
                                                Pelaporan</label>
                                            <select name="data_pelaporan" id="data_pelaporan"
                                                class="form-control custom-select">
                                                <option value="">-- Pilih --</option>
                                                {!! $dataDataPelaporan !!}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="lokasi_bahaya"><i class="fas fa-building text-danger mr-1"></i> Lokasi
                                        Bahaya</label>
                                    <select name="lokasi_bahaya" id="lokasi_bahaya" class="form-control custom-select">
                                        <option value="">-- Pilih Lokasi --</option>
                                        {!! $dataLokasi !!}
                                        <option value="other" {!! $isLokasiCustom ? 'selected' : '' !!}>Lainnya...</option>
                                    </select>
                                    <input type="text" name="lokasi_bahaya_other" id="lokasi_bahaya_other"
                                        class="form-control mt-2" placeholder="Tulis lokasi lainnya..."
                                        value="{{ $isLokasiCustom ? $report->lokasi_bahaya : '' }}"
                                        style="{{ $isLokasiCustom ? '' : 'display:none;' }}">
                                </div>
                                <div class="form-group">
                                    <label for="kategori_bahaya"><i class="fas fa-tag text-danger mr-1"></i> Kategori
                                        Bahaya</label>
                                    <select name="kategori_bahaya" id="kategori_bahaya" class="form-control custom-select">
                                        <option value="">-- Pilih Kategori --</option>
                                        {!! $dataKategori !!}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="desc_kategori_bahaya"><i class="fas fa-list-ul text-danger mr-1"></i> Jenis
                                        Bahaya</label>
                                    <select name="desc_kategori_bahaya" id="desc_kategori_bahaya"
                                        class="form-control custom-select">
                                        <option value="">-- Pilih Jenis Bahaya --</option>
                                        <optgroup label="☑ Kondisi Tidak Aman" id="opt-kondisi" {!! $report->kategori_bahaya == 3 ? '' : 'style="display:none"' !!}>
                                            {!! $dataJenisKondisi !!}
                                            <option value="other" {!! $isJenisCustom ? 'selected' : '' !!}>Lainnya...</option>
                                        </optgroup>
                                        <optgroup label="☑ Tindakan Tidak Aman" id="opt-tindakan" {!! $report->kategori_bahaya == 4 ? '' : 'style="display:none"' !!}>
                                            {!! $dataJenisTindakan !!}
                                            <option value="other" {!! $isJenisCustom ? 'selected' : '' !!}>Lainnya...</option>
                                        </optgroup>
                                    </select>
                                    <input type="text" name="desc_kategori_bahaya_other" id="desc_kategori_bahaya_other"
                                        class="form-control mt-2" placeholder="Tulis jenis bahaya lainnya..."
                                        value="{{ $isJenisCustom ? $report->desc_kategori_bahaya : '' }}"
                                        style="{{ $isJenisCustom ? '' : 'display:none;' }}">
                                </div>
                            </div>
                        </div>

                        <div class="card card-warning card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-pen-alt mr-2"></i>Detail Temuan</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="desc_temuan_bahaya"><i
                                            class="fas fa-exclamation-circle text-warning mr-1"></i> Deskripsi Temuan
                                        Bahaya</label>
                                    <textarea rows="3" id="desc_temuan_bahaya" name="desc_temuan_bahaya"
                                        class="form-control"
                                        placeholder="Jelaskan temuan bahaya yang terjadi...">{{ $report->desc_temuan_bahaya }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="rekomendasi_perbaikan"><i class="fas fa-tools text-warning mr-1"></i>
                                        Rekomendasi Perbaikan</label>
                                    <textarea rows="3" id="rekomendasi_perbaikan" name="rekomendasi_perbaikan"
                                        class="form-control"
                                        placeholder="Saran perbaikan / tindak lanjut...">{{ $report->rekomendasi_perbaikan }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kanan: Detail Temuan + Dokumen --}}
                    <div class="col-md-6">

                        <div class="card card-success card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-file-upload mr-2"></i>Dokumen</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="document"><i class="fas fa-image text-success mr-1"></i> Dokumen /
                                        Foto</label>
                                    <div id="dropzone-upload" class="upload-area border rounded p-4 text-center bg-light"
                                        style="cursor:pointer; min-height:200px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                        <div id="upload-placeholder">
                                            <i class="fas fa-cloud-upload-alt text-success" style="font-size:48px;"></i>
                                            <h5 class="mt-2 text-muted">Klik atau tarik file ke sini</h5>
                                            <p class="text-muted mb-0 small">Format: JPG, PNG, PDF (Max 5MB)</p>
                                        </div>
                                        <div id="upload-preview" class="w-100" style="display:none;">
                                            <img id="preview-image" src="#" alt="Preview" class="img-fluid rounded mb-2"
                                                style="max-height:200px; object-fit:contain;">
                                            <p id="file-name-display" class="mb-1 small text-muted"></p>
                                            <button type="button" id="btn-remove-file"
                                                class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i>
                                                Hapus</button>
                                        </div>
                                        @if ($report->document)
                                            <div class="mt-3 w-100" id="existing-document">
                                                <hr>
                                                <div
                                                    class="d-flex flex-column flex-sm-row align-items-center justify-content-between bg-white p-2 gap-2 rounded border">
                                                    <div>
                                                        <i class="fas fa-paperclip text-info mr-2"></i>
                                                        <span class="small">Dokumen Saat Ini</span>
                                                    </div>
                                                    <div>
                                                        <a href="{{ asset($report->document) }}" target="_blank"
                                                            class="btn btn-sm btn-info mr-1">
                                                            <i class="fas fa-eye"></i> Lihat
                                                        </a>
                                                        <a href="{{ asset($report->document) }}" download
                                                            class="btn btn-sm btn-success">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <input type="file" id="document" name="document" accept="image/*,.pdf"
                                        style="display:none;">
                                </div>
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
                                            <label for="nama_pengawas"><i class="fas fa-hard-hat text-secondary mr-1"></i>
                                                Nama Pengawas</label>
                                            <input type="text" id="nama_pengawas" name="nama_pengawas" class="form-control"
                                                placeholder="Nama supervisor" value="{{ $report->nama_pengawas }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="dept_penanggungjwb"><i
                                                    class="fas fa-building text-secondary mr-1"></i> Dept. Penanggung
                                                Jawab</label>
                                            <select name="dept_penanggungjwb" id="dept_penanggungjwb"
                                                class="form-control custom-select">
                                                <option value="">Pilih Departemen</option>
                                                {!! $dataDepartemen !!}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="status_pelaporan"><i class="fas fa-flag text-secondary mr-1"></i>
                                                Status Laporan</label>
                                            <select name="status_pelaporan" id="status_pelaporan"
                                                class="form-control custom-select">
                                                <option value="">-- Pilih Status --</option>
                                                {!! $dataStatus !!}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="due_date"><i class="fas fa-hourglass-end text-secondary mr-1"></i>
                                                Due Date</label>
                                            <input type="date" id="due_date" name="due_date" class="form-control"
                                                value="{{ $report->due_date }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between text-right">
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-default mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button id="save" type="button" class="btn btn-info">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    @include('js.admin.reports.edit')

    {{-- Tambahan CSS untuk dropzone --}}
    <style>
        .upload-area.dragover {
            background-color: #d4edda !important;
            border-color: #28a745 !important;
            border-style: dashed !important;
        }

        .upload-area:hover {
            background-color: #f8f9fa !important;
        }
    </style>
@endpush