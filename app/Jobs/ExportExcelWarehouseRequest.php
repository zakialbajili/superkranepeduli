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

class ExportExcelWarehouseRequest implements ShouldQueue
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

        $selectColumn = [
            'twhreqheader.req_date as Tanggal Request',
            'twhreqheader.req_no as No Request',
            'temployeedivision.name as Divisi',
            'emprequest.full_name as Diminta Oleh',
            'twhreqheader.wo_no as NO WO',
            'twhreqheader.ref_no as No Ref',
            'twhreqheader.wo_name as Nama WO',
            'tproject.project_code as Kode Project',
            'tproject.project_name as Nama Project',
            'tcustomer.cd_company_name as Nama Customer',
            'twhreqheader.ship_to as Dikirim Ke',
            'stat.name as Status',
        ];
        $data = DB::table('twhreqheader')
            ->leftJoin('temployeedivision', 'pk_employeedivision_id', '=', "twhreqheader.fk_division_id")
            ->leftJoin('tprojectmaster as stat', 'stat.pk_projectmaster_id', '=', "twhreqheader.fk_status_id")
            ->leftJoin('tproject', 'tproject.pk_project_id', '=', "twhreqheader.fk_project_id")
            ->leftJoin('tcustomer', 'tproject.fk_customer_id', '=', "tcustomer.pk_customer_id")
            ->leftJoin('temployee as emprequest', 'emprequest.employee_no', '=', "twhreqheader.request_by")
            ->select($selectColumn)
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('twhreqheader.req_date', ">=", $datadate[0]);
                        $query->Where('twhreqheader.req_date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                }
            })
            ->orderBy('twhreqheader.req_date', 'asc')
            ->get();

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $filename = 'WarehouseRequest' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
