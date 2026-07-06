<?php

namespace App\Jobs;

use App\Exports\ExcelExport;
use App\Models\UserModel;
use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Str;
use App\Traits\ModuleTraits;

class ExportExcelCostSubmissionReportData implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ModuleTraits;
    private $filteredData;
    private $user;
    private $data;
    private $dataHeader;
    /**
     * Create a new job instance.
     */
    public function __construct($fiteredData = [], UserModel $user) {
        $this->filteredData = $fiteredData;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        try {
            $datafilter = $this->filteredData;
            $data = DB::table('vcost_submission_report')
                ->select([
                    'no_reg as No Reg',
                    'reg_date as Tanggal Transaksi',
                    'employee_no as NIK',
                    'full_name as Nama',
                    'employee_position as Posisi Karyawan',
                    'submission_total as Total Pengajuan',
                    'reported_total as Total Pemakaian',
                    'remaining_total as Sisa',
                    'status_name as Status',
                    'created_date as Dibuat Tanggal',
                    'created_by as Dibuat Oleh',
                    'updated_date as Diubah Tanggal',
                    'updated_by as Diubah Oleh'
                ])
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('reg_date', ">=", $datadate[0]);
                            $query->Where('reg_date', "<=", $datadate[1]);
                        }
                    }
                    if ($datafilter["status"] != "") {
                        $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                    }
                })
                ->get();
            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'CostSubmissionReportData_'.$this->user->employees->employee_no.'_'.$now.'.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export '.$filename.' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
