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

class ExportExcelMonitorContractInvoice implements ShouldQueue
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
            $data = DB::table("tcontractinvoice")
                ->leftJoin('tcontractdetail', 'tcontractinvoice.fk_contractdetail_id', '=', 'tcontractdetail.pk_contractdetail_id')
                ->leftJoin('tcontract', 'tcontract.pk_contract_id', '=', 'tcontractdetail.fk_contract_id')
                ->leftJoin('tunit', 'tunit.pk_unit_id', '=', 'tcontractdetail.fk_unit_id')
                ->leftJoin('tcontracttimesheet', function ($join) {
                    $join->on('tcontracttimesheet.fk_contractdetail_id', '=', 'tcontractinvoice.fk_contractdetail_id')
                        ->on('tcontracttimesheet.period', '=', 'tcontractinvoice.period');
                })
                ->leftJoin('tprojectmaster', 'tprojectmaster.pk_projectmaster_id', '=', 'tcontracttimesheet.fk_status_id')
                ->select(
                    'tcontractinvoice.period AS PERIODE',
                    'tprojectmaster.name AS TIMESHEET STATUS',
                    'tcontractinvoice.no_invoice AS NO INVOICE',
                    'tcontract.client_name AS CUSTOMER',
                    'tcontract.contract_no AS NOMOR KONTRAK',
                    'tcontract.location AS LOKASI',
                    'tunit.unit_no AS KODE UNIT',
                    'tcontractdetail.unit_rental_price AS HARGA RENTAL UNIT',
                    'tcontractdetail.mobdemob_price AS HARGA MOB DEMOB',
                    'tcontractdetail.opr_rental_price AS HARGA OPERATOR',
                    'tcontractdetail.other_price AS HARGA LAINNYA',
                    'tcontractdetail.ot_opr_rental_price AS HARGA OT OPERATOR',
                    'tcontractdetail.ot_unit_rental_price AS HARGA OT UNIT',
                    'tcontractdetail.opr_qty AS JUMLAH OPERATOR',
                    'tcontractdetail.rigger_qty AS JUMLAH RIGGER',
                    'tcontractdetail.helper_qty AS JUMLAH HELPER',
                    'tcontractdetail.hse_qty AS JUMLAH HSE',
                    'tcontractdetail.planner_qty AS JUMLAH PLANNER',
                    'tcontractdetail.spv_qty AS JUMLAH SPV',
                    'tcontractdetail.other_qty AS JUMLAH LAINNYA',
                    'tcontractdetail.other_qty_notes AS CATATAN'
                )
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('tcontractinvoice.period', ">=", $datadate[0]);
                            $query->Where('tcontractinvoice.period', "<=", $datadate[1]);
                        }
                    }
                    if ($datafilter["status"] != "") {
                        $query->Where('tcontracttimesheet.fk_status_id', decryptForNumber($datafilter["status"]));
                    }
                    if ($datafilter["nama_pelanggan"] != "") {
                        $query->where('tcontract.client_name', 'LIKE', '%' . $datafilter["nama_pelanggan"] . '%');
                    }
                    if ($datafilter["no_invoice"] != "") {
                        $query->where('tcontractinvoice.no_invoice', 'LIKE', '%' . $datafilter["no_invoice"] . '%');
                    }
                    if ($datafilter["kontrak_id"] != "") {
                        $query->Where('tcontract.pk_contract_id', decryptForNumber($datafilter["kontrak_id"]));
                    } else {
                        if ($datafilter["no_kontrak"] != "") {
                            $query->where('tcontract.contract_no', 'LIKE', '%' . $datafilter["no_kontrak"] . '%');
                        }
                    }

                })
                ->get();

            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'MONITORING KONTRAK INVOICE ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
