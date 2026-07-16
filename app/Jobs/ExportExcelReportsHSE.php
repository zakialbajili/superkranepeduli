<?php

namespace App\Jobs;

use App\Exports\ExcelExport;
use App\Models\UserModel;
use DB;
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
use ZipArchive;

class ExportExcelReportsHSE implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ModuleTraits;
    private $filteredData;
    private $user;
    private $data;
    private $dataHeader;
    private const CHUNK_SIZE = 1000;

    /**
     * Create a new job instance.
     */
    public function __construct($fiteredData = [], UserModel $user)
    {
        $this->filteredData = $fiteredData;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $datafilter = $this->filteredData;

        if (!$datafilter || !is_array($datafilter) || empty(array_filter($datafilter))) {
            $datafilter = [];
        }

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

        $queryBuilder = DB::table("$table AS t")
            ->select($selectColumn)
            ->leftJoin('thsedata_master AS shift_m', 't.shift', '=', 'shift_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS data_m', 't.data_pelaporan', '=', 'data_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS kat_m', 't.kategori_bahaya', '=', 'kat_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS status_m', 't.status_pelaporan', '=', 'status_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS lokasi_m', 't.lokasi_bahaya', '=', 'lokasi_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS dept_m', 't.dept_penanggungjwb', '=', 'dept_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS jenis_m', 't.desc_kategori_bahaya', '=', 'jenis_m.pk_hsedatamaster_id')
            ->where(function ($query) use ($datafilter) {
                if (!empty($datafilter["tgl_pelaporan"])) {
                    $datadate = explode(' - ', $datafilter["tgl_pelaporan"]);
                    if (count($datadate) > 1) {
                        $query->where('t.tgl_pelaporan', ">=", $datadate[0]);
                        $query->where('t.tgl_pelaporan', "<=", $datadate[1]);
                    }
                }
                if (!empty($datafilter["shift"])) {
                    $query->where('t.shift', decryptId($datafilter["shift"]));
                }
                if (!empty($datafilter["kategori_bahaya"])) {
                    $query->where('t.kategori_bahaya', decryptId($datafilter["kategori_bahaya"]));
                }
                if (!empty($datafilter["status_pelaporan"])) {
                    $query->where('t.status_pelaporan', decryptId($datafilter["status_pelaporan"]));
                }
                if (!empty($datafilter["data_pelaporan"])) {
                    $query->where('t.data_pelaporan', decryptId($datafilter["data_pelaporan"]));
                }
                if (!empty($datafilter["lokasi_bahaya"])) {
                    $lokasiVal = $datafilter["lokasi_bahaya"];
                    $query->where(function ($q) use ($lokasiVal) {
                        $q->where('t.lokasi_bahaya', $lokasiVal)
                          ->orWhere('lokasi_m.name', $lokasiVal);
                    });
                }
                if (!empty($datafilter["dept_penanggungjwb"])) {
                    $query->where('t.dept_penanggungjwb', decryptId($datafilter["dept_penanggungjwb"]));
                }
                if (!empty($datafilter["desc_kategori_bahaya"])) {
                    $jenisVal = $datafilter["desc_kategori_bahaya"];
                    $query->where(function ($q) use ($jenisVal) {
                        $q->where('t.desc_kategori_bahaya', $jenisVal)
                          ->orWhere('jenis_m.name', $jenisVal);
                    });
                }
                if (!empty($datafilter["due_date"])) {
                    $datadate = explode(' - ', $datafilter["due_date"]);
                    if (count($datadate) > 1) {
                        $query->where('t.due_date', ">=", $datadate[0]);
                        $query->where('t.due_date', "<=", $datadate[1]);
                    }
                }
            })
            ->orderBy("t.tgl_pelaporan", 'desc');

        $totalCount = (clone $queryBuilder)->count();

        if ($totalCount == 0) {
            Log::warning('ExportExcelReportsHSE: Data kosong, tidak ada yang diexport.', [
                'filter' => $datafilter,
            ]);
            return;
        }

        $now = Carbon::now()->getTimestamp();
        $employeeNo = $this->user->employee_no ?? $this->user->name ?? 'user';
        $totalChunks = ceil($totalCount / self::CHUNK_SIZE);

        // Ambil header dari row pertama
        $firstRow = (clone $queryBuilder)->take(1)->get();
        $dataHeader = array_keys(json_decode(json_encode($firstRow->first()), true));

        // Multi-chunk → export per chunk, lalu zip
        $tempDir = storage_path('app/tmp_export_' . $now);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $chunkFiles = [];
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkData = (clone $queryBuilder)
                ->skip($i * self::CHUNK_SIZE)
                ->take(self::CHUNK_SIZE)
                ->get();

            if ($chunkData->isEmpty()) {
                continue;
            }

            $chunkFile = $tempDir . "/Part_" . ($i + 1) . ".xlsx";
            Excel::store(new ExcelExport($chunkData, $dataHeader, "Part " . ($i + 1)), "tmp_export_{$now}/Part_" . ($i + 1) . ".xlsx", 'local');
            $chunkFiles[] = $chunkFile;
        }

        // Zip semua chunk
        $zipFilename = 'HSEReports_' . $employeeNo . '_' . $now . '.zip';
        $zipPath = public_path('storage/' . $zipFilename);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($chunkFiles as $idx => $file) {
                $zip->addFile($file, 'Part_' . ($idx + 1) . '.xlsx');
            }
            $zip->close();
        }

        // Cleanup temp files
        foreach ($chunkFiles as $file) {
            @unlink($file);
        }
        @rmdir($tempDir);

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $zipFilename . ' Berhasil Dibuat', $zipFilename);
    }
}
