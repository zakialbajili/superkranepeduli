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

class ExportExcelMasterAbbreviation implements ShouldQueue
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

        $data = DB::table('twhmasterabreviation')
            ->select(
                'twhmasterabreviation.pk_whmasterabreviation_id',
                'twhmasterabreviation.abb_code',
                'twhmasterabreviation.fulltitle',
                'twhmasterabreviation.shorttitle',
                'twhmasterabreviation.fk_status_id',
                'twhmasterabreviation.created_date',
                'twhmasterabreviation.created_by',
                'twhmasterabreviation.updated_date',
                'twhmasterabreviation.updated_by',
                'tprojectmaster.name'
            )
            ->leftJoin('tprojectmaster', 'twhmasterabreviation.fk_status_id', '=', 'tprojectmaster.pk_projectmaster_id')

            ->where(function ($query) use ($datafilter) {
                if ($datafilter["abb-code"] != "") {
                    $query->where('twhmasterabreviation.abb_code', 'LIKE', '%' . $datafilter["abb-code"] . '%');
                }
                if ($datafilter["full-title"] != "") {
                    $query->where('twhmasterabreviation.full_title', 'LIKE', '%' . $datafilter["full-title"] . '%');
                }
                if ($datafilter["short-title"] != "") {
                    $query->where('twhmasterabreviation.short-title', 'LIKE', '%' . $datafilter["short-title"] . '%');
                }
                if ($datafilter["status"] != "") {
                    $query->where('twhmasterabreviation.fk_status_id', 'LIKE', '%' . $datafilter["status"] . '%');
                }
            })
            ->orderBy('twhmasterabreviation.created_date', 'asc')
            ->get();

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $filename = 'MasterAbbreviation ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
