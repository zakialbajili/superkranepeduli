<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateHMUnitProcessPM implements ShouldQueue
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
            $rawDataHM = DB::table('tunitloghm')
                ->whereDate('reg_date', "=", Carbon::yesterday())
                ->get();
            if ($rawDataHM) {
                foreach ($rawDataHM as $itemhm) {
                    DB::table('tunit')
                        ->where('pk_unit_id', $itemhm->fk_unit_id)
                        ->update([
                            "current_hm_atas" => $itemhm->hm_crane,
                            "current_hm_bawah" => $itemhm->hm_cabin,
                        ]);
                }
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }
}
