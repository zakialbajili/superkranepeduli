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

class ExportExcelWhPromptMaster implements ShouldQueue
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

        $data = DB::table('twhpromptmaster')
            ->select(
                'twhpromptmaster.prompt_code as Prompt Code',
                'twhpromptmaster.prompt_name as Prompt Full Title',
                'twhpromptmaster.prompt_short as Prompt Short title',
                'twhpromptmaster.description as Description',
                'twhpromptcategory.prompt_cat_fulltitle as Category',
                'tprojectmaster.name as Status',
                'twhpromptmaster.created_date as Dibuat Tanggal ',
                'twhpromptmaster.created_by as Dibuat oleh',
                'twhpromptmaster.updated_date as Diubah Tanggal',
                'twhpromptmaster.updated_by as Diubah Oleh '
            )
            ->leftJoin('twhpromptcategory', 'twhpromptmaster.fk_whpromptcategory_id', '=', 'twhpromptcategory.pk_whpromptcategory_id')
            ->leftJoin('tprojectmaster', 'twhpromptmaster.fk_status_id', '=', 'tprojectmaster.pk_projectmaster_id')
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["prompt_code"] != "") {
                    $query->where('twhpromptmaster.prompt_code', 'LIKE', '%' . $datafilter["prompt_code"] . '%');
                }
                if ($datafilter["prompt_name"] != "") {
                    $query->where('twhpromptmaster.prompt_name', 'LIKE', '%' . $datafilter["prompt_name"] . '%');
                }
                if ($datafilter["category"] != "") {
                    $query->where('twhpromptcategory.prompt_cat_fulltitle', 'LIKE', '%' . $datafilter["category"] . '%');
                }
                if ($datafilter["status"] != "") {
                    $query->where('twhpromptmaster.fk_status_id', 'LIKE', '%' . $datafilter["status"] . '%');
                }
            })
            ->orderBy('twhpromptmaster.created_date', 'asc')
            ->get();

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $filename = 'WhPromptMaster ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
