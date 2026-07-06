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

class ExportExcelDashboardMarketing implements ShouldQueue
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
            $data = DB::table('tcontract')
                ->select(
                    'tcontract.contract_no as No Kontrak',
                    'tproject.project_name as Project',
                    'tcontract.client_name as Customer',
                    'tunit.unit_no as Kode Unit',
                    'tcontract.location as Lokasi',
                    'tcontracttimesheet.period as Periode',
                    'stattimesheet.name as Status Timesheet',
                    'statinvoice.name as Status Invoice',
                )
                ->leftJoin('tproject', 'pk_project_id', "=", 'tcontract.fk_project_id')
                ->leftJoin('tprojectmaster', 'pk_projectmaster_id', "=", 'tcontract.fk_status_id')
                ->leftJoin('tcontractdetail', 'tcontract.pk_contract_id', "=", 'tcontractdetail.fk_contract_id')
                ->leftJoin('tunit', 'tunit.pk_unit_id', "=", 'tcontractdetail.fk_unit_id')
                ->leftJoin('tcontracttimesheet', 'tcontracttimesheet.fk_contractdetail_id', "=", 'tcontractdetail.pk_contractdetail_id')
                ->leftJoin('tprojectmaster as stattimesheet', 'stattimesheet.pk_projectmaster_id', "=", 'tcontracttimesheet.fk_status_id')
                ->leftJoin('tcontractinvoice', function ($join) {
                    $join->on('tcontractinvoice.fk_contractdetail_id', "=", 'tcontracttimesheet.fk_contractdetail_id');
                    $join->on('tcontractinvoice.period', "=", 'tcontracttimesheet.period');
                })
                ->leftJoin('tprojectmaster as statinvoice', 'statinvoice.pk_projectmaster_id', "=", 'tcontractinvoice.fk_status_id')
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["contractdate"] != "") {
                        $datadate = explode(' - ', $datafilter["contractdate"]);
                        if (count($datadate) > 0) {
                            $query->Where('tcontracttimesheet.period', ">=", $datadate[0]);
                            $query->Where('tcontracttimesheet.period', "<=", $datadate[1]);
                        }
                    }
                })
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["status"] != "") {
                        $query->orWhere('tcontracttimesheet.fk_status_id', decryptForNumber($datafilter["status"]));
                        $query->orWhere('tcontractinvoice.fk_status_id', decryptForNumber($datafilter["status"]));
                    }
                })
                ->get();

            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'DashboardMarketing' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
