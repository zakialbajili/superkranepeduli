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

class ExportExcelReportsHSE implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ModuleTraits;
    private $filteredData;
    private $user;
    private $data;
    private $dataHeader;

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

        // Jika tidak ada datafilter yang valid, ambil semua data
        if (!$datafilter || !is_array($datafilter) || empty(array_filter($datafilter))) {
            $datafilter = [];
        }

        $table = 'thsepelaporanbahaya';
        $selectColumn = [
            't.employee_no AS Employee No',
            't.full_name as Full Name',
            't.posisi as Posisi',
            't.tgl_pelaporan as Tanggal Pelaporan',
            'lokasi_m.name AS Lokasi Bahaya',
            'shift_m.name AS Shift',
            'data_m.name AS Data Pelaporan',
            'kat_m.name AS Kategori Bahaya',
            'status_m.name AS Status Pelaporan',
            'dept_m.name AS Departemen Penanggungjawab',
        ];
        $data = DB::table("$table AS t")
            ->select($selectColumn)
            ->leftJoin('thsedata_master AS shift_m', 't.shift', '=', 'shift_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS data_m', 't.data_pelaporan', '=', 'data_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS kat_m', 't.kategori_bahaya', '=', 'kat_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS status_m', 't.status_pelaporan', '=', 'status_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS lokasi_m', 't.lokasi_bahaya', '=', 'lokasi_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS dept_m', 't.dept_penanggungjwb', '=', 'dept_m.pk_hsedatamaster_id')
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
                    $query->where('t.lokasi_bahaya', decryptId($datafilter["lokasi_bahaya"]));
                }
                if (!empty($datafilter["dept_penanggungjwb"])) {
                    $query->where('t.dept_penanggungjwb', decryptId($datafilter["dept_penanggungjwb"]));
                }
            })
            ->orderBy("t.tgl_pelaporan", 'desc')
            ->get();

        if ($data->isEmpty()) {
            Log::warning('ExportExcelReportsHSE: Data kosong, tidak ada yang diexport.', [
                'filter' => $datafilter,
            ]);
            return;
        }

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $employeeNo = $this->user->employee_no ?? $this->user->name ?? 'user';
        $filename = 'HSEReports ' . $employeeNo . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
