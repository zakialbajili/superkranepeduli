<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superkrane Peduli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --hse-primary: #059669;
            /* Hijau Safety Emerald */
            --hse-primary-hover: #047857;
            --hse-warning: #d97706;
            /* Amber/Oranye Peringatan */
            --hse-bg: #f4f7f6;
        }

        body {
            background-color: var(--hse-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Banner Atas Bernuansa HSE */
        .hse-header {
            background: linear-gradient(135deg, var(--hse-primary) 0%, #111827 100%);
            color: white;
            padding: 30px 20px;
            border-bottom: 5px solid var(--hse-warning);
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .safety-badge {
            background-color: var(--hse-warning);
            color: white;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Styling Form Card */
        .card-hse {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            background: white;
            overflow: hidden;
        }

        .card-hse .card-header-hse {
            background-color: #fff;
            border-bottom: 2px solid #f0f0f0;
            padding: 15px 20px;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-hse .card-header-hse i {
            color: var(--hse-primary);
            font-size: 1.25rem;
        }

        .form-label {
            font-weight: 500;
            color: #4b5563;
            font-size: 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--hse-primary);
            box-shadow: 0 0 0 0.25rem rgba(5, 150, 105, 0.15);
        }

        /* Drag & Drop Zone untuk Foto Gambar */
        .upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            background-color: #f8fafc;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .upload-zone:hover {
            border-color: var(--hse-primary);
            background-color: rgba(5, 150, 105, 0.02);
        }

        .upload-zone i {
            color: #64748b;
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }

        .upload-zone:hover i {
            color: var(--hse-primary);
        }

        #preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 100px));
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }

        .preview-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
        }

        /* Tombol Submit HSE */
        .btn-hse-submit {
            background-color: var(--hse-primary);
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 10px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
        }

        .btn-hse-submit:hover {
            background-color: var(--hse-primary-hover);
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="hse-header mb-4">
        <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="safety-badge mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Safety
                    First</span>
                <h1 class="h3 mb-1 fw-bold">SUPERKRANE PEDULI</h1>
                {{-- <h5>(Pekerja Dukung Lingkungan Aman)</h5> --}}
                <p class="mb-0 opacity-75 text-sm">Pelaporan Terhadap Bahaya K3.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="/riwayat-pelaporan" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Riwayat
                </a>

                <button id="btnLogout" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </button>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <form id="formPelaporan" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-lg-8">

                    <div class="card card-hse">
                        <div class="card-header-hse">
                            <i class="fa-solid fa-address-card"></i> Informasi Umum Pelapor
                        </div>
                        <div class="card-body p-4 row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap<span class="text-danger">*</span></label>
                                <input type="text" name="nama_pelapor" class="form-control"
                                    value="{{ session('full_name') }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIK<span class="text-danger">*</span></label>
                                <input type="text" name="nik_pelapor" class="form-control"
                                    value="{{ session('employee_no') }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Posisi<span class="text-danger">*</span></label>
                                <input type="text" name="posisi" class="form-control" value="{{ session('position') }}"
                                    readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Pelaporan Bahaya<span
                                        class="text-danger">*</span></label>
                                <input type="date" name="tgl_pelaporan" id="tgl_pelaporan" class="form-control"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="card card-hse">
                        <div class="card-header-hse">
                            <i class="fa-solid fa-triangle-exclamation"></i> Detail Temuan Bahaya
                        </div>
                        <div class="card-body p-4 row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shift<span class="text-danger">*</span></label>
                                <select name="shift" class="form-select" required>
                                    <option value="">Pilih Shift</option>
                                    {!! $optionShift !!}
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Data Pelaporan<span class="text-danger">*</span></label>
                                <select name="data_pelaporan" class="form-select" required>
                                    <option value="">Pilih Data Pelaporan</option>
                                    {!! $optionDataPelaporan !!}
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Lokasi Terjadinya Bahaya<span
                                        class="text-danger">*</span></label>
                                <select name="lokasi_bahaya_select" id="lokasi_bahaya_select" class="form-select"
                                    required>
                                    <option value="">Pilih Lokasi</option>
                                    {!! $optionLokasi !!}
                                    <option value="Other">Lainnya (Other)</option>
                                </select>
                                <input type="text" name="lokasi_bahaya_other" id="lokasi_bahaya_other"
                                    class="form-control mt-2" placeholder="Sebutkan lokasi lainnya..."
                                    style="display: none;">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Kategori Bahaya<span class="text-danger">*</span></label>
                                <select name="kategori_bahaya" id="kategori_bahaya" class="form-select" required>
                                    <option value="">Pilih Kategori</option>
                                    {!! $optionKategoriBahaya !!}
                                </select>
                            </div>

                            <div class="col-12 mb-3" id="wrapper-tindakan" style="display: none; margin-top: -8px;">
                                <label class="form-label text-danger">Pilih Tindakan Tidak Aman<span
                                        class="text-danger">*</span></label>
                                <select name="desc_kategori_tindakan" id="select-tindakan" class="form-select">
                                    <option value="">Pilih Detail Tindakan...</option>
                                    {!! $optionTindakanTidakAman !!}
                                    <option value="Other">Lainnya...</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3" id="wrapper-kondisi" style="display: none; margin-top: -8px;">
                                <label class="form-label text-warning">Pilih Kondisi Tidak Aman<span
                                        class="text-danger">*</span></label>
                                <select name="desc_kategori_kondisi" id="select-kondisi" class="form-select">
                                    <option value="">Pilih Detail Kondisi...</option>
                                    {!! $optionKondisiTidakAman !!}
                                    <option value="Other">Lainnya...</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3" id="wrapper-detail-other" style="display: none; margin-top: -8px;">
                                <input type="text" name="desc_kategori_bahaya_other" id="input-detail-other"
                                    class="form-control" placeholder="Tuliskan secara singkat...">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Deskripsikan Temuan Bahaya secara spesifik<span
                                        class="text-danger">*</span></label>
                                <textarea name="desc_temuan_bahaya" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label">Rekomendasi Perbaikan<span
                                        class="text-danger">*</span></label>
                                <textarea name="rekomendasi_perbaikan" class="form-control" rows="3"
                                    required></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">

                    <div class="card card-hse">
                        <div class="card-header-hse">
                            <i class="fa-solid fa-camera"></i> Bukti Foto
                        </div>
                        <div class="card-body p-4">
                            <div class="upload-zone" onclick="document.getElementById('foto_input').click()">
                                <i class="fa-solid fa-cloud-arrow-up fa-2x"></i>
                                <p class="mb-1 fw-semibold text-sm mt-2">Ambil Foto / Pilih Gambar</p>
                                <input type="file" id="foto_input" name="document" class="d-none">
                            </div>

                            <button type="button" class="btn btn-outline-secondary w-100 mt-3 btn-sm d-none d-md-block"
                                data-bs-toggle="modal" data-bs-target="#kameraModal" style="margin-bottom: -15px;">
                                <i class="fa-solid fa-camera"></i> Ambil Foto
                            </button>

                            <div id="preview-container"></div>
                            <div class="modal fade" id="kameraModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fs-5"><i class="fa-solid fa-camera"></i> Camera On
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div
                                            class="modal-body text-center bg-dark rounded-bottom position-relative p-0">
                                            <video id="video-webcam" class="w-100" autoplay playsinline
                                                style="display:none;"></video>

                                            <canvas id="canvas-webcam" class="w-100" style="display:none;"></canvas>

                                            <div id="webcam-fallback" class="p-5 text-white">
                                                <i class="fa-solid fa-circle-notch fa-spin fa-2x mb-3"></i><br>
                                                Meminta akses kamera...
                                            </div>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-danger" id="btn-capture">
                                                <i class="fa-solid fa-circle-dot"></i> Ambil Foto
                                            </button>
                                            <button type="button" class="btn btn-warning d-none" id="btn-retake">
                                                <i class="fa-solid fa-rotate-right"></i> Foto Ulang
                                            </button>
                                            <button type="button" class="btn btn-success d-none" id="btn-use-photo">
                                                <i class="fa-solid fa-check"></i> Gunakan Foto Ini
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-hse">
                        <div class="card-header-hse">
                            <i class="fa-solid fa-user-shield"></i> Penanggung Jawab
                        </div>
                        <div class="card-body p-4 row g-3">
                            <div class="col-12 mb-3">
                                <label class="form-label">Departemen Penanggung Jawab<span
                                        class="text-danger">*</span></label>
                                <select name="dept_penanggungjwb" class="form-select" required>
                                    <option value="">Pilih Departemen</option>
                                    {!! $optionDepartment !!}
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Nama Pengawas Bertanggung Jawab<span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_pengawas" class="form-control" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Due Date<span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>
                            <input type="hidden" name="status_pelaporan" value="">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-hse-submit" id="btnSubmitReport">
                            <i class="fa-solid fa-paper-plane me-2"></i> Submit
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('js.user.formreport')

</body>

</html>