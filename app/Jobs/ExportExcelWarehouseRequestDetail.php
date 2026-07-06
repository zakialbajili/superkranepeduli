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

class ExportExcelWarehouseRequestDetail implements ShouldQueue
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
            'twhreqheader.req_date AS Tanggal Permintaan',
            'twhreqheader.req_no AS No Permintaan',
            'stat.name AS Status Permintaan',
            'temployeedivision.name AS Divisi',
            'twhreqheader.wo_no AS No SPK/WO',
            'twhreqheader.wo_name AS Nama SPK/WO',
            'twhreqheader.ship_to AS Alamat Pengiriman',
            'twhreqheader.ref_no AS No Referensi',
            'tunit.unit_no AS Kode Unit',
            'twhreqitem.part_number AS Part No',
            'twhreqitem.description AS Deskripsi Item',
            'twhreqitem.qty AS Jumlah Permintaan',
            'trans.qty AS Jumlah Dikirim',
            'ordered.qty AS Jumlah Dipesan',
            'received.qty AS Jumlah Diterima',
            'uom.name AS Satuan',
            'twhreqitem.required_date AS Tanggal Dibutuhkan',
            'twhreqitem.purpose AS Tujuan Permnintaan',
            'twhreqitem.notes AS Catatan Item',
            'stat_detail.name AS Status Item',
            'twhreqitem.reqno_acc AS No Permintaan AOL',
        ];
        $data = DB::table('twhreqheader')
            ->join('tprojectmaster as stat', 'stat.pk_projectmaster_id', '=', 'twhreqheader.fk_status_id')
            ->leftJoin('temployeedivision', 'temployeedivision.pk_employeedivision_id', '=', 'twhreqheader.fk_division_id')
            ->join('twhreqitem', 'twhreqitem.fk_whreqheader_id', '=', 'twhreqheader.pk_whreqheader_id')
            ->leftJoin('tprojectmaster as uom', 'uom.pk_projectmaster_id', '=', 'twhreqitem.fk_uom_id')
            ->leftJoin('tprojectmaster as stat_detail', 'stat_detail.pk_projectmaster_id', '=', 'twhreqitem.fk_status_id')
            ->leftJoin('tunit', 'tunit.pk_unit_id', '=', 'twhreqitem.fk_unit_id')
            ->leftJoin(DB::raw('(SELECT fk_source_id, SUM(qty) AS qty FROM twhtransmitalitem WHERE fk_sourcetype_id=2 GROUP BY fk_source_id) as trans'), 'trans.fk_source_id', '=', 'twhreqitem.pk_whreqitem_id')
            ->leftJoin(DB::raw('(SELECT fk_whreqitem_id, SUM(qty) AS qty FROM twhorderitem INNER JOIN tprojectmaster ON tprojectmaster.pk_projectmaster_id = twhorderitem.fk_status_id WHERE tprojectmaster.master_type IN ("Mengunggu", "Waiting", "Completed", "Closed") AND fk_whreqitem_id IS NOT NULL GROUP BY fk_whreqitem_id) as ordered'), 'ordered.fk_whreqitem_id', '=', 'twhreqitem.pk_whreqitem_id')
            ->leftJoin(DB::raw('(SELECT fk_whreqitem_id, SUM(qty) AS qty FROM twhreceiveitem WHERE fk_whreqitem_id IS NOT NULL GROUP BY fk_whreqitem_id) as received'), 'received.fk_whreqitem_id', '=', 'twhreqitem.pk_whreqitem_id')
            ->select($selectColumn)
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('twhreqheader.req_date', ">=", $datadate[0]);
                        $query->Where('twhreqheader.req_date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('twhreqheader.fk_status_id', decryptForNumber($datafilter["status"]));
                }
                if ($datafilter["divisi"] != "") {
                    $query->Where('twhreqheader.fk_division_id', decryptForNumber($datafilter["divisi"]));
                }
                if ($datafilter["kode-unit"] != "") {
                    $query->where('tunit.unit_no', 'LIKE', '%' . $datafilter["kode-unit"] . '%');
                }
                if ($datafilter["part-no"] != "") {
                    $query->where('twhreqitem.part_number', 'LIKE', '%' . $datafilter["part-no"] . '%');
                }
                if ($datafilter["no-permintaanaol"] != "") {
                    $query->where('twhreqitem.reqno_acc', 'LIKE', '%' . $datafilter["no-permintaanaol"] . '%');
                }
                if ($datafilter["no-permintaan"] != "") {
                    $query->where('twhreqheader.req_no', 'LIKE', '%' . $datafilter["no-permintaan"] . '%');
                }
            })
            ->orderBy('twhreqheader.req_date', 'asc')
            ->get();

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $filename = 'WarehouseRequestDetail ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
