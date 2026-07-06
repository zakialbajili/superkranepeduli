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

class ExportExcelUnitHm implements ShouldQueue
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
            $data = DB::table('tunitloghm')
                ->join('tunit', 'tunitloghm.fk_unit_id', '=', 'tunit.pk_unit_id')
                ->join('tunitmanufacture', 'tunit.fk_unitmanufacture_id', "=", 'tunitmanufacture.pk_unitmanufacture_id')
                ->join('tunitmodel', 'tunit.fk_unitmodel_id', "=", 'tunitmodel.pk_unitmodel_id')
                ->select([
                    'tunitloghm.reg_date as Tanggal',
                    'tunit.unit_no as Kode Unit',
                    'tunit.unit_no_alternate_1 as Alt Kode Unit 1',
                    'tunit.unit_no_alternate_2 as Alt Kode Unit 1',
                    'tunitmanufacture.name as Manuf',
                    'tunitmodel.name as Model',
                    'tunitloghm.hm_crane as HM Utama / HM Atas',
                    'tunitloghm.hm_cabin as HM Tambahan / HM Bawah',
                    'tunitloghm.created_date as Tanggal Disubmit',
                    'tunitloghm.created_by as NIK Disubmit Oleh',
                    'tunitloghm.created_by_name as Nama Disubmit Oleh',
                    'tunitloghm.created_by_position as Posisi Disubmit Oleh',
                ])
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('tunitloghm.reg_date', ">=", $datadate[0]);
                            $query->Where('tunitloghm.reg_date', "<=", $datadate[1]);
                        }
                    }
                })
                ->get();
            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'UnitHMTelegram_' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
