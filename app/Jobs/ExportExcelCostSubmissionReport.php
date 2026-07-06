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

class ExportExcelCostSubmissionReport implements ShouldQueue {
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
            $data = DB::table('vcostsubmission_compare')
                ->select([
                    'no_reg as No Reg',
                    'date as Tanggal Transaksi',
                    'wo_name as Nama WO',
                    'customer as Customer',
                    'location as Lokasi Project',
                    'payment_type as Tipe Pembayaran',
                    'fk_employee_no as NIK',
                    'full_name as Nama',
                    'typename as Tipe Biaya',
                    'unit_no as Unit No',
                    'description as Deskripsi',
                    'value as Jumlah',
                    'ackBy_name as Diserahkan',
                    'appBy_name as Disetujui',
                    'recBy_name as Diterima',
                    'subby_name as Dibuat',
                    'status_name as Status'
                ])
                ->where(function ($query) use ($datafilter) {
                    if($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('date', ">=", $datadate[0]);
                            $query->Where('date', "<=", $datadate[1]);
                        }
                    }
                    if($datafilter["status"] != "") {
                        $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                    }
                    if($datafilter["costtype"] != "") {
                        $query->Where('fk_cost_type_id', decryptForNumber($datafilter["costtype"]));
                    }
                    if($datafilter["payment"] != "") {
                        $query->Where('payment_method', ($datafilter["payment"] == 0 ? "Cash" : "Transfer"));
                    }
                })
                ->get();
            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'CostSubmissionReport_'.$this->user->employees->employee_no.'_'.$now.'.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export '.$filename.' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
