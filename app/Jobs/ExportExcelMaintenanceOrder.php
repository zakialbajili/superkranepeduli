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

class ExportExcelMaintenanceOrder implements ShouldQueue
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

            $selectColumn = [
                'tmoheader.unit_no as Equipment Number',
                'tunit.unit_no_alternate_1 as Equipment Number Alt #1',
                'tunit.unit_no_alternate_2 as Equipment Number Alt #2',
                'tunit.description as Equipment Description',
                'tmoheader.reg_no as Maintenance Order',
                'tmoheader.category as Category',
                'tmoheader.description as MO Description',
                'ordertype as Order Type',
                'tmoheader.mostatus_name as MO Status',
                'release_date as Release Date',
                'start_date as Based Start Date',
                'finish_date as Based Finish Date',
                'act_start_date as Act Start Date',
                'act_finish_date as Act Finish Date',
                'work_center as Work Center',
                'tprojectmaster.name as User Status',
                'tmoheader.created_by as Entered By',
                'maint_activity as Maint Activity',
                'vmosignMechanic.employee_names as Mechanic',
                'vmosignElectrician.employee_names as Electrician',
                'vmosignWelder.employee_names as Welder',
                'vmosignSupervisor.employee_names as Supervisor'
            ];
            $data = DB::table('tmoheader')
                ->leftjoin('tunit', 'tunit.pk_unit_id', '=', 'tmoheader.unit_id')
                ->leftJoin('tprojectmaster', 'tmoheader.fk_status_id', '=', 'tprojectmaster.pk_projectmaster_id')
                ->leftJoin('vmosignElectrician', 'tmoheader.pk_moheader_id', '=', 'vmosignElectrician.fk_moheader_id')
                ->leftJoin('vmosignMechanic', 'tmoheader.pk_moheader_id', '=', 'vmosignMechanic.fk_moheader_id')
                ->leftJoin('vmosignSupervisor', 'tmoheader.pk_moheader_id', '=', 'vmosignSupervisor.fk_moheader_id')
                ->leftJoin('vmosignWelder', 'tmoheader.pk_moheader_id', '=', 'vmosignWelder.fk_moheader_id')
                ->select($selectColumn)
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('start_date', ">=", $datadate[0]);
                            $query->Where('start_date', "<=", $datadate[1]);
                        }
                    }
                    if ($datafilter["status"] != "") {
                        $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                    }
                    if ($datafilter["ordertype"] != "") {
                        $query->Where('ordertype', $datafilter["ordertype"]);
                    }
                })
                ->orderBy('tprojectmaster.name', 'asc')
                ->orderBy('tmoheader.reg_no', 'asc')
                ->chunk(10000, function ($data, $i = 1) {
                    $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
                    $now = Carbon::now()->getTimestamp();
                    $filename = 'MaintenanceOrder_' . $this->user->employees->employee_no . '_' . $now + $i . '.xlsx';

                    Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

                    $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
                    $i++;
                });


        } catch (\Throwable $th) {
            log::info($th->getMessage());
            throw $th;

        }
    }
}
