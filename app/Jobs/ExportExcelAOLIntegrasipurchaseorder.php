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

class ExportExcelAOLIntegrasipurchaseorder implements ShouldQueue
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


        $data = DB::table('taolpurchaseorderdetail')
            ->select(
                'taolpurchaseorderheader.number as NO PO',
                'taolpurchaseorderheader.vendorName as NAMA SUPLIER',
                'taolpurchaseorderdetail.projectName as NAMA PROJECT',
                'taolpurchaseorderdetail.departmentName as NAMA DEPARTMENT',
                'taolpurchaseorderdetail.charField2 as UNIT NO',
                'taolpurchaseorderdetail.itemNo as AOL ITEM NO',
                'taolpurchaseorderdetail.itemName as AOL NAMA ITEM',
                'taolpurchaseorderdetail.detailName as AOL DESKRIPSI ITEM',
                'taolpurchaseorderdetail.quantity as QTY',
                'taolpurchaseorderdetail.charField1 as SATUAN',
                DB::raw('unitPrice * IFNULL(taolpurchaseorderdetail.rate, 1) as HARGA'),
                'taolpurchaseorderdetail.currencyCode as MATA UANG',
                'taolpurchaseorderdetail.rate as RATE',
                'taolpurchaseorderdetail.created_date as TANGGAL DIBUAT',
            )
            ->leftJoin('taolpurchaseorderheader', 'taolpurchaseorderdetail.purchaseOrderId', '=', 'taolpurchaseorderheader.id')
 
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('taolpurchaseorderdetail.created_date', ">=", $datadate[0]);
                        $query->Where('taolpurchaseorderdetail.created_date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["no-po"] != "") {
                    $query->where('taolpurchaseorderheader.number', 'LIKE', '%' . $datafilter["no-po"] . '%');
                }
                if ($datafilter["nama-supplier"] != "") {
                    $query->where('taolpurchaseorderheader.vendorName', 'LIKE', '%' . $datafilter["nama-supplier"] . '%');
                }
                if ($datafilter["nama-project"] != "") {
                    $query->where('taolpurchaseorderdetail.projectName', 'LIKE', '%' . $datafilter["nama-project"] . '%');
                }
                if ($datafilter["nama-department"] != "") {
                    $query->where('taolpurchaseorderdetail.departmentName', 'LIKE', '%' . $datafilter["nama-department"] . '%');
                }
                if ($datafilter["unit-no"] != "") {
                    $query->where('taolpurchaseorderdetail.charField2', 'LIKE', '%' . $datafilter["unit-no"] . '%');
                }
                if ($datafilter["aol-itemno"] != "") {
                    $query->where('taolpurchaseorderdetail.itemNo', 'LIKE', '%' . $datafilter["aol-itemno"] . '%');
                }

            })
            ->orderBy('taolpurchaseorderdetail.created_date', 'asc')
            ->get();

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $filename = 'AOLIntegrasiPurchaseOrder ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
