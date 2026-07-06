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

class ExportExcelContract implements ShouldQueue
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
                    'tprojectmaster.name as Status',
                    'contract_no as No Kontrak',
                    'tproject.project_name as Nama Project',
                    'client_name as Customer',
                    'contract_date as Tanggal Kontrak',
                    'location as Lokasi',
                    'project_start as Project Di Mulai',
                    'project_end as Project Selesai',
                    'tcontract.notes as Catatan'
                )
                ->leftJoin('tproject', 'pk_project_id', "=", 'tcontract.fk_project_id')
                ->leftJoin('tprojectmaster', 'pk_projectmaster_id', "=", 'tcontract.fk_status_id')
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["contractdate"] != "") {
                        $datadate = explode(' - ', $datafilter["contractdate"]);
                        if (count($datadate) > 0) {
                            $query->Where('contract_date', ">=", $datadate[0]);
                            $query->Where('contract_date', "<=", $datadate[1]);
                        }
                    }
                    if ($datafilter["status"] != "") {
                        $query->Where('tcontract.fk_status_id', decryptForNumber($datafilter["status"]));
                    }
                })
                ->get();

            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'MarketingKontrak' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
