<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Pelaporan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --warning: #f59e0b;
            --danger: #dc2626;
            --success: #16a34a;
            --bg: #f5f7fb;
            --text: #1f2937;
            --muted: #64748b;
        }

        body {
            background: var(--bg);
            font-family: 'Segoe UI', sans-serif;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg,
                    var(--primary),
                    #0f172a);
            color: white;
            padding: 30px 20px;
            border-bottom: 5px solid var(--warning);
            border-radius: 0 0 25px 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .12);
        }

        .page-header h3 {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .page-header p {
            opacity: .85;
            margin-bottom: 18px;
        }

        .btn-report {
            border-radius: 30px;
            padding: 10px 22px;
            font-weight: 600;
        }

        /* Card Laporan */
        .report-card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            background: white;
            margin-bottom: 22px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
            transition: .25s;
        }

        .report-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 35px rgba(0, 0, 0, .12);
        }

        /* Foto Laporan */
        .image-wrapper {
            position: relative;
            width: 100%;
            height: 240px;
            background: #111827;
            overflow: hidden;
        }

        .image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-empty {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-direction: column;
        }

        /* Status Badge */
        .status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: .75rem;
            font-weight: 700;
            color: white;
            backdrop-filter: blur(10px);
        }

        .status-open {
            background: #16a34acc;
        }

        .status-progress {
            background: #f59e0bcc;
            color: #111827;
        }

        .status-close {
            background: #dc2626cc;
        }

        /* Body Card */
        .report-body {
            padding: 20px;
        }

        .report-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 18px;
        }

        /* Info Box */
        .info-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .info-box i {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dcfce7;
            color: var(--primary);
        }

        .info-box small {
            color: var(--muted);
            display: block;
        }

        .info-box span {
            font-weight: 600;
            color: var(--text);
        }

        /* Section */
        .section {
            margin-top: 22px;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text);
        }

        .section-content {
            color: #475569;
            line-height: 1.7;
        }

        /* Image Preview Modal */
        .preview-image {
            cursor: pointer;
            transition: .35s;
        }

        .preview-image:hover {
            transform: scale(1.03);
        }

        .modal-content {
            border: none;
            border-radius: 18px;
            overflow: hidden;
        }

        .modal-body {
            padding: 0;
            background: #111827;
        }

        .modal-body img {
            width: 100%;
            height: auto;
        }

        /* Responsive */
        @media(max-width:576px) {
            .page-header {
                padding: 25px 18px;
            }

            .image-wrapper {
                height: 220px;
            }

            .report-body {
                padding: 18px;
            }

            .report-title {
                font-size: 1.05rem;
            }
        }
    </style>

</head>

<body>

    <div class="page-header">
        <div class="container">
            <h3>
                <i class="fa-solid fa-clipboard-list me-2"></i>
                Riwayat Laporan Saya
            </h3>
            <p>
                Pantau perkembangan laporan bahaya yang telah Anda kirimkan.
            </p>
            <a href="/formreport" class="btn btn-outline-light btn-report">
                <i class="fa-solid fa-plus me-1"></i>
                Buat Laporan
            </a>
        </div>
    </div>

    <div class="container pb-5">

        @forelse($riwayatLaporan as $item)
            @php
                $status = strtolower($item->nama_status ?? 'open');
                $statusInfo = match ($status) {
                    'progress', 'on progress' => [
                        'class' => 'status-progress',
                        'icon' => 'fa-spinner',
                        'text' => 'ON PROGRESS',
                        'badge_class' => 'bg-warning text-dark',
                    ],
                    'closed', 'close' => [
                        'class' => 'status-close',
                        'icon' => 'fa-circle-check',
                        'text' => 'CLOSED',
                        'badge_class' => 'bg-danger',
                    ],
                    default => [
                        // open
                        'class' => 'status-open',
                        'icon' => 'fa-circle-exclamation',
                        'text' => 'OPEN',
                        'badge_class' => 'bg-success',
                    ],
                };
            @endphp

            <div class="report-card">
                <!-- FOTO -->
                <div class="image-wrapper">
                    @if (!empty($item->document))
                        <img src="{{ asset('storage/' . $item->document) }}" alt="Foto Bukti" class="preview-image"
                            data-bs-toggle="modal" data-bs-target="#imageModal"
                            data-image="{{ asset('storage/' . $item->document) }}">
                    @else
                        <div class="image-empty">
                            <i class="fa-regular fa-image fa-3x mb-3"></i>
                            <small>Tidak Ada Foto</small>
                        </div>
                    @endif

                    <!-- STATUS -->
                    <div class="status {{ $statusInfo['class'] }}">
                        <i class="fa-solid {{ $statusInfo['icon'] }} me-1"></i>
                        {{ $statusInfo['text'] }}
                    </div>
                </div>

                <!-- ISI -->
                <div class="report-body">
                    <div class="report-title">
                        {{ $item->nama_kategori ?? 'Kategori Tidak Diketahui' }}
                    </div>

                    <!-- INFORMASI -->
                    <div class="info-box">
                        <i class="fa-regular fa-calendar"></i>
                        <div>
                            <small>Tanggal Laporan</small>
                            <span>
                                {{ \Carbon\Carbon::parse($item->created_date)->format('d M Y') }}
                            </span>
                        </div>
                    </div>

                    <div class="info-box">
                        <i class="fa-solid fa-location-dot"></i>
                        <div>
                            <small>Lokasi</small>
                            <span>{{ $item->lokasi_bahaya }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <i class="fa-solid fa-tag"></i>
                        <div>
                            <small>Kategori Bahaya</small>
                            <span>{{ $item->desc_kategori_bahaya }}</span>
                        </div>
                    </div>

                    <!-- TEMUAN BAHAYA -->
                    <div class="section">
                        <div class="section-title text-danger">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Temuan Bahaya
                        </div>
                        <div class="alert alert-danger border-0 rounded-4 mb-0" style="background:#fef2f2;">
                            {{ $item->desc_temuan_bahaya }}
                        </div>
                    </div>

                    <!-- REKOMENDASI -->
                    <div class="section">
                        <div class="section-title text-success">
                            <i class="fa-solid fa-screwdriver-wrench me-2"></i>
                            Rekomendasi Perbaikan
                        </div>
                        <div class="alert alert-success border-0 rounded-4 mb-0" style="background:#ecfdf5;">
                            {{ $item->rekomendasi_perbaikan }}
                        </div>
                    </div>

                    <!-- FOOTER CARD -->
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fa-solid fa-shield-halved me-1 text-success"></i>
                            Status Laporan
                        </div>
                        <div>
                            <span class="badge rounded-pill {{ $statusInfo['badge_class'] }} px-3 py-2">
                                <i class="fa-solid {{ $statusInfo['icon'] }} me-1"></i>
                                {{ $statusInfo['text'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fa-regular fa-folder-open fa-5x text-secondary mb-4"></i>
                <h5 class="text-muted">
                    Belum Ada Riwayat Pelaporan
                </h5>
                <p class="text-muted">
                    Laporan yang Anda kirim akan muncul di sini.
                </p>
            </div>
        @endforelse
    </div>
    <!-- IMAGE MODAL -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        Bukti Foto
                    </h6>
                    <button class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" src="" alt="Preview">
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const images = document.querySelectorAll(".preview-image");
            const modalImage = document.getElementById("modalImage");
            images.forEach(function (img) {
                img.addEventListener("click", function () {
                    modalImage.src = this.dataset.image;
                });
            });
        });
    </script>
</body>

</html>