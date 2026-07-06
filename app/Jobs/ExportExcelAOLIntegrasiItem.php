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

class ExportExcelAOLIntegrasiItem implements ShouldQueue
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


        $data = DB::table('taolitem')
            ->select(
                'NO as ITEM NO',
                'itemtypeName as Nama Tipe',
                'itemCategory as Kategori Item',
                'NAME as Item Name',
                'created_date as Tanggal Dibuat',
                'updated_date as Tanggal Diubah'
            )
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('taolitem.created_date', ">=", $datadate[0]);
                        $query->Where('taolitem.created_date', "<=", $datadate[1]);
                    }
                }

                if ($datafilter["item-no"] != "") {
                    $query->where('taolitem.no', 'LIKE', '%' . $datafilter["item-no"] . '%');
                }
                if ($datafilter["nama-tipe"] != "") {
                    $query->where('taolitem.itemtypeName', 'LIKE', '%' . $datafilter["nama-tipe"] . '%');
                }
                if ($datafilter["kategori-item"] != "") {
                    $query->where('taolitem.itemCategory', 'LIKE', '%' . $datafilter["kategori-item"] . '%');
                }
                if ($datafilter["item_name"] != "") {
                    $query->where('taolitem.NAME', 'LIKE', '%' . $datafilter["item_name"] . '%');
                }

            })
            ->orderBy('taolitem.created_date', 'asc')
            ->get();

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $filename = 'AOLIntegrasiItem ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
