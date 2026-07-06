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

class ExportExcelWarehouseReceiveDetail implements ShouldQueue
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

        $selectColumn = [
            'twhreceiveheader.received_date as Tanggal Penerimaan',
            'twhreceiveheader.receive_no as No Penerimaan',
            'twhreceiveheader.ref_no as Nomer Referensi',
            'tsupplier.name as Supplier',
            'stat.name as Status',
            'twhreceiveitem.part_no as Part No',
            'twhreceiveitem.part_no_description as Deskripsi',
            'tunit.unit_no as Kode Unit',
            'twhreceiveitem.order_no as No Pemesanan AOL',
            'twhlocation.name as Lokasi Warehouse',
            'twhpartnumberlocation.location as Lokasi Rak',
            'twhreceiveitem.qty as Jumlah Diterima',
            'uom.name as Satuan',
            'twhorderitem.qty as Jumlah Pemesanan',
            'twhreqitem.qty as Jumlah Permintaan',
            'trans.qty as Jumlah Dikirim'
        ];
        $data = DB::table('twhreceiveheader')
            ->leftJoin('tsupplier', 'tsupplier.pk_supplier_id', '=', 'twhreceiveheader.fk_supplier_id')
            ->leftJoin('tprojectmaster as stat', 'stat.pk_projectmaster_id', '=', 'twhreceiveheader.fk_status_id')
            ->leftJoin('twhreceiveitem', 'twhreceiveitem.fk_whreceiveheader_id', '=', 'twhreceiveheader.pk_whreceiveheader_id')
            ->leftJoin('twhreqitem', 'twhreqitem.pk_whreqitem_id', '=', 'twhreceiveitem.fk_whreqitem_id')
            ->leftJoin('tunit', 'twhreqitem.fk_unit_id', '=', 'tunit.pk_unit_id')
            ->leftJoin('twhlocation', 'twhlocation.pk_whlocation_id', '=', 'twhreceiveitem.fk_whlocation_id')
            ->leftJoin('tprojectmaster as uom', 'uom.pk_projectmaster_id', '=', 'twhreceiveitem.fk_uom_id')
            ->leftJoin('twhpartnumberlocation', 'twhpartnumberlocation.pk_whpartnumberlocation_id', '=', 'twhreceiveitem.fk_whpartnumberlocation_id')
            ->leftJoin(
                DB::raw('(SELECT fk_source_id, SUM(qty) AS qty FROM twhtransmitalitem WHERE fk_sourcetype_id = 2 GROUP BY fk_source_id) AS trans'),
                'trans.fk_source_id',
                '=',
                'twhreceiveitem.fk_whreqitem_id'
            )
            ->leftJoin('twhorderitem', 'twhorderitem.pk_whorderitem_id', '=', 'twhreceiveitem.fk_whorderitem_id')
            ->select($selectColumn)

            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('twhreceiveheader.received_date', ">=", $datadate[0]);
                        $query->Where('twhreceiveheader.received_date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('twhreceiveheader.fk_status_id', decryptForNumber($datafilter["status"]));
                }
                if ($datafilter["supplier_id"] != "") {
                    $query->Where('twhreceiveheader.fk_supplier_id', decryptForNumber($datafilter["supplier_id"]));
                } else {
                    if ($datafilter["name_supplier"] != "") {
                        $query->where('tsupplier.name', 'LIKE', '%' . $datafilter["name_supplier"] . '%');
                    }
                }

                if ($datafilter["kode-unit"] != "") {
                    $query->where('tunit.unit_no', 'LIKE', '%' . $datafilter["kode-unit"] . '%');
                }
                if ($datafilter["part-no"] != "") {
                    $query->where('twhreceiveitem.part_no', 'LIKE', '%' . $datafilter["part-no"] . '%');
                }
                if ($datafilter["description"] != "") {
                    $query->where('twhreceiveitem.part_no_description', 'LIKE', '%' . $datafilter["description"] . '%');
                }
                if ($datafilter["no-penerimaan"] != "") {
                    $query->where('twhreceiveheader.receive_no', 'LIKE', '%' . $datafilter["no-penerimaan"] . '%');
                }
            })
            ->orderBy('twhreceiveheader.received_date', 'asc')
            ->get();

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $filename = 'WarehouseReceiveDetail ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
