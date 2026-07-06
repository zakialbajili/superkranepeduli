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

class ExportExcelCostSubmission implements ShouldQueue
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
        try {
            $datafilter = $this->filteredData;
            $data = DB::table('vcostsubmission')
                ->select(
                    'no_reg as No Reg',
                    'date as Tanggal Transaksi',
                    'wo_name as Nama WO',
                    'project_code as Kode Project',
                    'project_name as Nama Project',
                    'customer as Customer',
                    'location as Lokasi Project',
                    'employee_no as NIK',
                    'full_name as Nama',
                    'total as Jumlah',
                    DB::RAW("if(payment_type=1,'TRANSFER','CASH') as Pembayaran"),
                    'status_name as Status',
                    'submitted_name as Dibuat',
                    'acknowledge_name as Diserahkan',
                    'received_name as Diterima',
                    'approved_name as Disetujui'
                )
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('date', ">=", $datadate[0]);
                            $query->Where('date', "<=", $datadate[1]);
                        }
                    }
                    if ($datafilter["status"] != "") {
                        $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                    }
                    if ($datafilter["payment"] != "") {
                        $query->Where('payment_type', $datafilter["payment"]);
                    }
                })
                ->get();

            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'CostSubmission_' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
