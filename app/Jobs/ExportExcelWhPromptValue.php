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

class ExportExcelWhPromptValue implements ShouldQueue
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

        $data = DB::table('twhpromptvalue')
            ->select(
                'twhpromptvalue.value_code As Value Code',
                'twhpromptvalue.value_fulltitle as Value Full Title',
                'twhpromptvalue.value_shorttitle as Value Short Title',
                'twhpromptvalcat.value_cat_fulltitle as Category',
                'tprojectmaster.name as Status',
                'twhpromptvalue.created_date as Dibuat Tanggal',
                'twhpromptvalue.created_by as Dibuat Oleh',
                'twhpromptvalue.updated_date as Diubah Tanggal',
                'twhpromptvalue.updated_by as Diubah Oleh'
            )
            ->leftJoin('twhpromptvalcat', 'twhpromptvalue.fk_whpromptvalcat_id', '=', 'twhpromptvalcat.pk_whpromptvalcat_id')
            ->leftJoin('tprojectmaster', 'twhpromptvalue.fk_status_id', '=', 'tprojectmaster.pk_projectmaster_id')

            ->where(function ($query) use ($datafilter) {
                if ($datafilter["code"] != "") {
                    $query->where('twhpromptvalue.value_code', 'LIKE', '%' . $datafilter["code"] . '%');
                }
                if ($datafilter["value"] != "") {
                    $query->where('twhpromptvalue.value_fulltitle', 'LIKE', '%' . $datafilter["value"] . '%');
                }
                if ($datafilter["category"] != "") {
                    $query->where('twhpromptvalcat.value_cat_fulltitle', 'LIKE', '%' . $datafilter["category"] . '%');
                }
                if ($datafilter["status"] != "") {
                    $query->where('twhpromptvalue.fk_status_id', 'LIKE', '%' . $datafilter["status"] . '%');
                }
            })
            ->orderBy('twhpromptvalue.created_date', 'asc')
            ->get();

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $filename = 'WhPromptValue ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
