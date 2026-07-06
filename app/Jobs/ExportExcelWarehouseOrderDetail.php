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

class ExportExcelWarehouseOrderDetail implements ShouldQueue
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
            'twhorderheader.order_date as Tanggal Pemesanan',
            'twhorderheader.order_no as No Pemesanan',
            'twhorderheader.reference_no as No Referensi',
            'stat.name as Status Pemesanan',
            'temployeedivision.name as Divisi',
            'twhorderheader.ship_to as Alamat Pengiriman',
            'tunit.unit_no as Kode Unit',
            'twhpartnumber.part_no as Part No',
            'twhpartnumber.part_no_alternate1 as Part No Alt 1',
            'twhpartnumber.part_no_alternate2 as Part No Alt 2',
            'twhorderitem.description as Deskripsi',
            'twhorderitem.required_date as Tanggal Dibutuhkan',
            'twhorderitem.qty as Jumlah Pemesanan',
            'uom.name as Satuan',
            'received.qty as Jumlah Diterima',
            'twhreqitem.qty as Jumlah Permintaan',
            'trans.qty as Jumlah Dikirim',
            'stat_detail.name as Status Item'
        ];
        $data = DB::table('twhorderheader')
            ->leftJoin('temployeedivision', 'temployeedivision.pk_employeedivision_id', '=', 'twhorderheader.fk_division_id')
            ->join('twhorderitem', 'twhorderitem.fk_whorderheader_id', '=', 'twhorderheader.pk_whorderheader_id')
            ->join('twhpartnumber', 'twhpartnumber.pk_whpartnumber_id', '=', 'twhorderitem.fk_whpartnumber_id')
            ->leftJoin('tprojectmaster as stat', 'stat.pk_projectmaster_id', '=', 'twhorderheader.fk_status_id')
            ->leftJoin('tprojectmaster as uom', 'uom.pk_projectmaster_id', '=', 'twhorderitem.fk_uom_id')
            ->leftJoin('tprojectmaster as stat_detail', 'stat_detail.pk_projectmaster_id', '=', 'twhorderitem.fk_status_id')
            ->leftJoin('tunit', 'tunit.pk_unit_id', '=', 'twhorderitem.fk_unit_id')
            ->leftJoin('twhreqitem', 'twhreqitem.pk_whreqitem_id', '=', 'twhorderitem.fk_whreqitem_id')
            ->leftJoin(
                DB::raw('(SELECT fk_source_id, SUM(qty) AS qty FROM twhtransmitalitem WHERE fk_sourcetype_id = 2 GROUP BY fk_source_id) as trans'),
                'trans.fk_source_id',
                '=',
                'twhreqitem.pk_whreqitem_id'
            )
            ->leftJoin(
                DB::raw('(SELECT fk_whorderitem_id, SUM(qty) AS qty FROM twhreceiveitem WHERE fk_whorderitem_id IS NOT NULL GROUP BY fk_whorderitem_id) as received'),
                'received.fk_whorderitem_id',
                '=',
                'twhorderitem.pk_whorderitem_id'
            )
            ->select($selectColumn)

            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('twhorderheader.order_date', ">=", $datadate[0]);
                        $query->Where('twhorderheader.order_date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('twhorderheader.fk_status_id', decryptForNumber($datafilter["status"]));
                }
                if ($datafilter["divisi"] != "") {
                    $query->Where('twhorderheader.fk_division_id', decryptForNumber($datafilter["divisi"]));
                }
                if ($datafilter["kode-unit"] != "") {
                    $query->where('tunit.unit_no', 'LIKE', '%' . $datafilter["kode-unit"] . '%');
                }
                if ($datafilter["part-no"] != "") {
                    $query->where('twhpartnumber.part_no', 'LIKE', '%' . $datafilter["part-no"] . '%');
                }
                if ($datafilter["no-pemesanan"] != "") {
                    $query->where('twhorderheader.order_no', 'LIKE', '%' . $datafilter["no-pemesanan"] . '%');
                }
            })
            ->orderBy('twhorderheader.order_date', 'asc')
            ->get();

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $filename = 'WarehouseOrderDetail ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
