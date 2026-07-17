<?php

namespace App\Jobs;

use App\Exports\ExcelExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\ModuleTraits;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportExcelReportsHSE implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ModuleTraits;

    private array $filteredData;
    private $user;

    /**
     * Create a new job instance.
     */
    public function __construct(array $filteredData, $user)
    {
        $this->filteredData = $filteredData;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $query = $this->buildQuery($this->filteredData);

            // --- Persiapan Nama File & User Info ---
            $now = Carbon::now()->getTimestamp();
            $empNo = $this->user->employee_no ?? $this->user->name ?? 'unknown';
            $baseFilename = 'HSEReports_' . $empNo . '_' . $now;

            $chunkIndex = 1;
            $generatedFiles = [];

            // --- Chunking & Export Multiple Files ---
            $query->orderBy('t.tgl_pelaporan', 'desc')
                ->chunk(1000, function ($chunkedData) use (&$chunkIndex, &$generatedFiles, $baseFilename) {
                    if ($chunkedData->isEmpty()) {
                        return;
                    }

                    $formattedData = collect();
                    foreach ($chunkedData as $item) {
                        $formattedData->push((array) $item);
                    }

                    $dataHeader = array_keys($formattedData->first());

                    // Format penamaan file sementara (_part_x)
                    $partFilename = $baseFilename . '_part_' . $chunkIndex . '.xlsx';

                    // Simpan file excel sementara ke storage public
                    Excel::store(new ExcelExport($formattedData, $dataHeader), $partFilename, 'public');

                    $generatedFiles[] = $partFilename;
                    Log::info("Export HSE Reports Chunk Success: {$partFilename} (Rows: {$formattedData->count()})");

                    $chunkIndex++;
                });

            // --- Pengecekan Hasil Export & Pembuatan ZIP ---
            $totalFiles = count($generatedFiles);

            if ($totalFiles > 0) {
                // Selalu hasilkan file ZIP
                $zipFileName = $baseFilename . '.zip';
                $zipPath = storage_path('app/public/' . $zipFileName);

                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {

                    // Masukkan file xlsx ke dalam zip
                    foreach ($generatedFiles as $file) {
                        $fullPath = storage_path('app/public/' . $file);

                        // Penentuan nama file di dalam zip
                        if ($totalFiles === 1) {
                            // Jika hanya 1 file, nama di dalam zip tanpa (_part_x)
                            $entryName = $baseFilename . '.xlsx';
                        } else {
                            // Jika > 1 file, nama di dalam zip tetap (_part_x)
                            $entryName = $file;
                        }

                        $zip->addFile($fullPath, $entryName);
                    }

                    $zip->close();

                    // Hapus file excel sementara dari storage server
                    foreach ($generatedFiles as $file) {
                        Storage::disk('public')->delete($file);
                    }

                    // Kirim notifikasi file ZIP berhasil dibuat
                    $this->sendNotificationFileCreated(
                        $this->user->pk_user_id ?? $this->user->id,
                        'Export '.$zipFileName.' Berhasil Dibuat',
                        $zipFileName
                    );

                    Log::info("Export HSE Reports ZIP Success: {$zipFileName}");
                } else {
                    Log::error("Gagal membuat ZIP untuk file export: " . $zipFileName);
                }
            } else {
                // --- Handle Jika Data Kosong ---
                $filename = $baseFilename . '.xlsx';
                $dataHeader = [
                    'Employee No',
                    'Full Name',
                    'Posisi',
                    'Tanggal Pelaporan',
                    'Lokasi Bahaya',
                    'Shift',
                    'Data Pelaporan',
                    'Kategori Bahaya',
                    'Jenis Bahaya',
                    'Deskripsi Temuan',
                    'Rekomendasi',
                    'Dept. Penanggung Jawab',
                    'Pengawas',
                    'Due Date',
                    'Status Pelaporan',
                ];

                // Buat template excel kosong
                Excel::store(new ExcelExport(collect(), $dataHeader), $filename, 'public');

                $this->sendNotificationFileCreated(
                    $this->user->pk_user_id ?? $this->user->id,
                    'Export HSE Reports Berhasil (Data Kosong)',
                    $filename
                );

                Log::info("Job Export Excel: Tidak ada data HSE Reports berdasarkan filter. Template kosong dibuat: {$filename}");
            }
        } catch (\Throwable $th) {
            Log::error("Error Job Export Excel HSE Reports: " . $th->getMessage() . " at " . $th->getFile() . ":" . $th->getLine());
            throw $th;
        }
    }

    private function buildQuery(array $filters)
    {
        $table = 'thsepelaporanbahaya';

        $selectColumn = [
            't.employee_no AS Employee No',
            't.full_name AS Full Name',
            't.posisi AS Posisi',
            't.tgl_pelaporan AS Tanggal Pelaporan',
            DB::raw('COALESCE(lokasi_m.name, t.lokasi_bahaya) AS "Lokasi Bahaya"'),
            'shift_m.name AS Shift',
            'data_m.name AS Data Pelaporan',
            'kat_m.name AS Kategori Bahaya',
            DB::raw('COALESCE(jenis_m.name, t.desc_kategori_bahaya) AS "Jenis Bahaya"'),
            't.desc_temuan_bahaya AS Deskripsi Temuan',
            't.rekomendasi_perbaikan AS Rekomendasi',
            'dept_m.name AS Dept. Penanggung Jawab',
            't.nama_pengawas AS Pengawas',
            't.due_date AS Due Date',
            'status_m.name AS Status Pelaporan',
        ];

        $query = DB::table("$table AS t")
            ->select($selectColumn)
            ->leftJoin('thsedata_master AS shift_m', 't.shift', '=', 'shift_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS data_m', 't.data_pelaporan', '=', 'data_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS kat_m', 't.kategori_bahaya', '=', 'kat_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS status_m', 't.status_pelaporan', '=', 'status_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS lokasi_m', 't.lokasi_bahaya', '=', 'lokasi_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS dept_m', 't.dept_penanggungjwb', '=', 'dept_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS jenis_m', 't.desc_kategori_bahaya', '=', 'jenis_m.pk_hsedatamaster_id');

        $query->where(function ($q) use ($filters) {
            if (!empty($filters["tgl_pelaporan"])) {
                $datadate = explode(' - ', $filters["tgl_pelaporan"]);
                if (count($datadate) == 2) {
                    $q->where('t.tgl_pelaporan', ">=", $datadate[0]);
                    $q->where('t.tgl_pelaporan', "<=", $datadate[1]);
                }
            }

            if (!empty($filters["shift"])) {
                $q->where('t.shift', decryptId($filters["shift"]));
            }

            if (!empty($filters["kategori_bahaya"])) {
                $q->where('t.kategori_bahaya', decryptId($filters["kategori_bahaya"]));
            }

            if (!empty($filters["status_pelaporan"])) {
                $q->where('t.status_pelaporan', decryptId($filters["status_pelaporan"]));
            }

            if (!empty($filters["data_pelaporan"])) {
                $q->where('t.data_pelaporan', decryptId($filters["data_pelaporan"]));
            }

            if (!empty($filters["lokasi_bahaya"])) {
                $lokasiVal = $filters["lokasi_bahaya"];
                $q->where(function ($sub) use ($lokasiVal) {
                    $sub->where('t.lokasi_bahaya', $lokasiVal)
                         ->orWhere('lokasi_m.name', $lokasiVal);
                });
            }

            if (!empty($filters["dept_penanggungjwb"])) {
                $q->where('t.dept_penanggungjwb', decryptId($filters["dept_penanggungjwb"]));
            }

            if (!empty($filters["desc_kategori_bahaya"])) {
                $jenisVal = $filters["desc_kategori_bahaya"];
                $q->where(function ($sub) use ($jenisVal) {
                    $sub->where('t.desc_kategori_bahaya', $jenisVal)
                         ->orWhere('jenis_m.name', $jenisVal);
                });
            }

            if (!empty($filters["due_date"])) {
                $datadate = explode(' - ', $filters["due_date"]);
                if (count($datadate) == 2) {
                    $q->where('t.due_date', ">=", $datadate[0]);
                    $q->where('t.due_date', "<=", $datadate[1]);
                }
            }
        });

        return $query;
    }
}
