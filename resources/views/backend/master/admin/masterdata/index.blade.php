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
            <div class="row">
                <div class="col-sm-12">

                    <div class="card shadow-none border">

                        <div class="card-header p-2 bg-white">
                            <ul class="nav nav-pills" id="masterDataTab" role="tablist">
                                @foreach($types as $index => $t)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $index == 0 ? 'active' : '' }}" id="tab-{{ Str::slug($t) }}"
                                            data-toggle="pill" href="#tabs-content-area" role="tab" data-type="{{ $t }}">
                                            {{ $t }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="card-body p-3">
                            <div class="tab-content" id="masterDataTabContent">
                                <div class="tab-pane fade show active" id="tabs-content-area" role="tabpanel">

                                    <div class="card shadow-none border mb-0">
                                        <div class="card-body p-3">

                                            <div class="mb-3 d-flex justify-content-end">
                                                <button type="button" id="btnBukaModal" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus font-weight-bold"></i> Tambah Data
                                                </button>
                                            </div>

                                            <div class="table-responsive">
                                                <table id="tableMasterData" class="table table-bordered table-hover w-100">
                                                    <thead>
                                                        <tr class="bg-light">
                                                            <th>Nama Data</th>
                                                            <th width="10%" class="text-center">Aktif</th>
                                                            <th width="10%" class="text-center">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modalTambahMaster" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-elegant">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title font-weight-bold">Tambah Master Data</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formMasterData" action="{{ route('admin.masterdata.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        <div class="form-group mb-3">
                            <label for="type" class="text-muted small font-weight-bold text-uppercase">Tipe Data</label>
                            <input type="text" class="form-control form-control-modern bg-light border-0" id="type"
                                name="type" readonly style="pointer-events: none;">
                        </div>

                        <div class="form-group mb-0">
                            <label for="name" class="text-muted small font-weight-bold text-uppercase">Nama Data Baru <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" id="name" name="name"
                                placeholder="Masukkan nama..." required autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light btn-modern text-muted" data-dismiss="modal"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info btn-modern shadow-sm"><i class="fas fa-save mr-1"></i>
                            Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditMaster" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-elegant">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title font-weight-bold">Edit Master Data</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditMasterData" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body px-4 py-3">
                        <div class="form-group mb-3">
                            <label for="edit_type" class="text-muted small font-weight-bold text-uppercase">Tipe
                                Data</label>
                            <input type="text" class="form-control form-control-modern bg-light border-0" id="edit_type"
                                name="edit_type" readonly style="pointer-events: none;">
                        </div>

                        <div class="form-group mb-0">
                            <label for="edit_name" class="text-muted small font-weight-bold text-uppercase">Nama Data <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" id="edit_name" name="name" required
                                autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light btn-modern text-muted" data-dismiss="modal"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info btn-modern text-white shadow-sm"><i
                                class="fas fa-save mr-1"></i> Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* 3. Minimalist Table Styling */
        .table-minimalist {
            border-collapse: separate;
            border-spacing: 0 8px;
            /* Memberikan jarak antar baris */
        }

        .table-minimalist thead th {
            border-bottom: none;
            color: #a0aec0;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            padding-bottom: 10px;
        }

        .table-minimalist tbody tr {
            background-color: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .table-minimalist tbody tr:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            border-radius: 8px;
        }

        .table-minimalist tbody td {
            border-top: 1px solid #f1f3f5;
            border-bottom: 1px solid #f1f3f5;
            vertical-align: middle;
            padding: 12px 16px;
            color: #495057;
        }

        /* Membulatkan ujung baris tabel */
        .table-minimalist tbody td:first-child {
            border-left: 1px solid #f1f3f5;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .table-minimalist tbody td:last-child {
            border-right: 1px solid #f1f3f5;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* 4. Elegant Modals & Inputs */
        .modal-content-elegant {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-control-modern {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 15px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control-modern:focus {
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
        }

        /* 5. Button Enhancements */
        .btn-modern {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-action-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        /* ================= CSS UNTUK TOGGLE AKTIF ================= */
        .switch {
            position: relative;
            display: inline-block;
            width: 42px;
            height: 22px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            /* Warna saat nonaktif (abu-abu) */
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #1de9b6;
            /* Warna Cyan/Mint seperti di gambar Anda */
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        .slider.round {
            border-radius: 22px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
@endpush

@push('scripts')
    @include('js.admin.masterdata.index')
@endpush