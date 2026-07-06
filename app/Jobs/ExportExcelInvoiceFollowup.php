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

class ExportExcelInvoiceFollowup implements ShouldQueue
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
            $data = DB::table('tcontractinvoice')
                ->select(
                    'period as Periode',
                    'unit_no as Kode Unit',
                    'tcontractinvoice.no_invoice as No Invoice',
                    'tprojectmaster.name as Status',
                    'tcontractinvoice.updated_by as Diubah Oleh',
                    'tcontractinvoice.updated_date as Diubah Date',
                    'contract_no as No Kontrak',
                    'project_name as Nama Project',
                    'client_name as Customer',
                    'contract_date as Tanggal Kontrak',
                    'location as Lokasi',
                    'project_start as Project Dimulai',
                    'project_end as Project Selesai',
                )
                ->leftJoin('tcontractdetail', 'tcontractdetail.pk_contractdetail_id', "=", 'tcontractinvoice.fk_contractdetail_id')
                ->leftJoin('tcontract', 'tcontract.pk_contract_id', "=", 'tcontractinvoice.fk_contract_id')
                ->leftJoin('tprojectmaster', 'pk_projectmaster_id', "=", 'tcontractinvoice.fk_status_id')
                ->leftJoin('tproject', 'pk_project_id', "=", 'tcontract.fk_project_id')
                ->leftJoin('tunit', 'tunit.pk_unit_id', "=", 'tcontractdetail.fk_unit_id')
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('period', ">=", $datadate[0]);
                            $query->Where('period', "<=", $datadate[1]);
                        }
                    }
                    if ($datafilter["status"] != "") {
                        $query->Where('tcontractinvoice.fk_status_id', decryptForNumber($datafilter["status"]));
                    }
                })
                ->get();

            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'InvoiceFollowup' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
