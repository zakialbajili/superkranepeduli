<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PreventiveMaintenance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //dapatkan hm saat ini untuk masing-masing unit
        try {

            $rawunit = DB::table('vunitmin')
                ->select(
                    'pk_unit_id',
                    'unit_no',
                    'fk_unitmodel_id',
                    'current_hm_atas',
                    'current_hm_bawah',
                    'current_pm_id',
                    'current_pm_step_name',
                    'current_pm_hm_atas',
                    'current_pm_hm_bawah',
                    'next_pm_step_name',
                    'next_pm_scheduled_hm_atas'
                )
                ->where('status_type', "<>", "SOLD")
                ->where('current_hm_atas', ">", 225)
                ->whereIn('unittype_type', [
                    "Crane",
                    "Dozer",
                    "Transportation",
                    "Excavator",
                    "Forklift",
                    "Telehandler",
                    "Light Vehicle",
                    "Man Lift",
                    "Reachstacker",
                    "Scissor Lift",
                    "Trailer",
                    "Vibro",
                    "Mobile"
                ])->get();


            foreach ($rawunit as $itemunit) {
                //check dan skip jika next pm hm atas sudah > current hm atas

                if ($itemunit->next_pm_scheduled_hm_atas - 25 > $itemunit->current_hm_atas) {
                    continue;
                }
                $unitmodel_id = $itemunit->fk_unitmodel_id;
                $unit_id = $itemunit->pk_unit_id;
                $unit_no = $itemunit->unit_no;
                $current_hm_atas = $itemunit->current_hm_atas;
                $current_hm_bawah = $itemunit->current_hm_bawah;
                $current_pm_id = $itemunit->current_pm_id;
                $current_pm_step_name = $itemunit->current_pm_step_name;
                $current_pm_hm_atas = $itemunit->current_pm_hm_atas;
                $current_pm_hm_bawah = $itemunit->current_pm_hm_bawah;
                $next_pm_step_name = $itemunit->next_pm_step_name;
                $next_pm_scheduled_hm_atas = $itemunit->next_pm_scheduled_hm_atas;

                //****** sudah masuk dalam proses PM Schedule */ 

                // ambil data pm schedule sesuai dengan next_pm_step_name dan model unit
                // data yg didapatkan untuk $next_pm_step_name adalah format S01h250
                // check apakah h250 ada dalam inputan

                $countpmtemplatestep = DB::table('tpmtemplatestepops')
                    ->where('fk_unitmodel_id', $unitmodel_id)
                    ->count();
                // $countpmtemplatestep = count($rawpmtemplatestep);

                $pmtemplatestep = DB::table('tpmtemplatestepops')
                    ->where('fk_unitmodel_id', $unitmodel_id)
                    ->where('current_step_name', 'like', '%' . substr($next_pm_step_name, 3, strlen($next_pm_step_name) - 3))
                    ->first();
                // if ($pmtemplatestep) {
                //     //check apakah step ini hanya ada 1 record
                //     //jika lebih dari  1 record maka simpan next pm S01h250
                //     if ($countpmtemplatestep > 1) {
                //         $pmtemplatestep = DB::table('tpmtemplatestepops')
                //             ->where('fk_unitmodel_id', $unitmodel_id)
                //             ->where('current_step_name', 'h250')
                //             ->first();
                //         $next_pm_step_name = 'S01h250';
                //     }
                // } else {
                //     goto nextitteration;
                // }

                if (!$pmtemplatestep) {
                    goto nextitteration;
                }

                //next schedule flow
                $cr = substr($pmtemplatestep->current_step_name, 1, strlen($pmtemplatestep->current_step_name) - 1) * 1;
                $nx = substr($pmtemplatestep->next_step_name, 1, strlen($pmtemplatestep->next_step_name) - 1) * 1;



                $nextschedule = 'S01h250';
                $nexthmschedule = $current_hm_atas + 25 + 250;
                // $nexthmschedule = (floor(($current_hm_atas + 25) / 250) * 250) + 250;


                //check apakah step ini hanya ada 1 record
                //jika 1 record maka simpan next pm sama dengan next pm sebelumnya
                if ($countpmtemplatestep == 1) {
                    $valnexthm = substr($next_pm_step_name, 4, strlen($next_pm_step_name) - 4) * 1;
                    $nextschedule = $next_pm_step_name;
                    $nexthmschedule = $current_hm_atas + 25 + $valnexthm;
                    // $nexthmschedule = (floor(($current_hm_atas + 25) / $valnexthm) * $valnexthm) + $valnexthm;
                } else {
                    $pmtranslationstep = DB::table('tpmtemplatesteptranslation')
                        ->where('step_name', $next_pm_step_name)
                        ->first();
                    if ($pmtranslationstep) {
                        $nextschedule = ($cr > $nx ? 'S01h250' : $pmtranslationstep->next_step);
                    } else {
                        goto nextitteration;
                    }
                }
                $templateheader_id = $pmtemplatestep->fk_pmtemplateheader_id;

                // create MO
                $noreg = DB::select("Select maintenanceorderno('PRM') noreg");
                $noreg = $noreg[0]->noreg;
                $maintactivity = substr($next_pm_step_name, 4, strlen($next_pm_step_name) - 4);
                $moheaderdata = [
                    "reg_no" => $noreg,
                    "start_date" => now(),
                    "finish_date" => now(),
                    "release_date" => now(),
                    "priority" => "P1",
                    "ordertype" => 'PRM',
                    "maint_typename" => 'PMS',
                    "maint_activity" => "PMS SERVICE",
                    "maint_stepname" => $next_pm_step_name,
                    "maint_template_id" => $templateheader_id,
                    "hm_reading" => $current_hm_atas,
                    "planner_group" => "H.O JAKARTA",
                    "planner_group_id" => 526,
                    "unit_id" => $unit_id,
                    "mostatus_name" => 'OPEN',
                    "unit_no" => $unit_no,
                    "created_date" => now(),
                    "created_by" => 'SYSTEM',
                    "updated_date" => now(),
                    "updated_by" => 'SYSTEM',
                    "fk_status_id" => defaultDraftStatusId()
                ];

                $moheader_id = DB::table('tmoheader')
                    ->insertGetId(
                        $moheaderdata
                    );

                $rawdetailtemplate = DB::table('tpmtemplateoperationdetail')
                    ->select('no', 'description')
                    ->where("fk_pmtemplateheader_id", $templateheader_id)
                    ->where(substr($next_pm_step_name, 3, strlen($next_pm_step_name) - 3), ">", 0)
                    ->get();
                foreach ($rawdetailtemplate as $detailtemplate) {
                    DB::table('tmoactualops')
                        ->insert([
                            "fk_moheader_id" => $moheader_id,
                            "ops_no" => $detailtemplate->no,
                            "description" => $detailtemplate->description,
                        ]);
                }

                $datastoreunit = [
                    'current_pm_id' => $moheader_id,
                    'current_pm_step_name' => $next_pm_step_name,
                    'current_pm_hm_atas' => $current_hm_atas,
                    'current_pm_hm_bawah' => $current_hm_bawah,
                    'next_pm_step_name' => $nextschedule,
                    'next_pm_scheduled_hm_atas' => $nexthmschedule,
                ];

                DB::table('tunit')
                    ->where('pk_unit_id', $unit_id)
                    ->update($datastoreunit);

                nextitteration:
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }
}
