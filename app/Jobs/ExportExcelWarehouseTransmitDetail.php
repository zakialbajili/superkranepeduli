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

class ExportExcelWarehouseTransmitDetail implements ShouldQueue
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


        $data = DB::table('twhtransmitalheader')
        ->select([
            'twhtransmitalheader.transmital_date as Tanggal Pengiriman',
            'twhtransmitalheader.transmital_no as No Pengiriman',
            'twhtransmitalheader.ship_to as Dikirim Ke',
            'stat.name as Status Pengiriman',
            'twhtransmitalheader.reference_no as No Referensi',
            'twhtransmitalheader.pic_name as PIC',
            'twhtransmitalheader.vehicle_no as No Kendaraan',
            'temployee.employee_no as NIK Penerima',
            'temployee.full_name as Nama Penerima',
            'tunit.unit_no as Kode Unit',
            'twhpartnumber.part_no as Part No Warehouse',
            'twhtransmitalitem.part_no as Part No Pengiriman',
            'twhtransmitalitem.part_no_description as Deskripsi',
            'twhlocation.name as Lokasi Warehouse',
            'twhtransmitalitem.qty as Jumlah Pengiriman',
            'uom.name as Satuan',
            'twhreqitem.qty as Jumlah Permintaan',
            'trans.qty as Jumlah Terkirim',
            'orderqty.qty as Jumlah Pemesanan',
            'recqty.qty as Jumlah Penerimaan'
        ])
        ->leftJoin('twhtransmitalitem', 'twhtransmitalheader.pk_whtransmitalheader_id', '=', 'twhtransmitalitem.fk_whtransmitalheader_id')
        ->leftJoin('tprojectmaster as stat', 'stat.pk_projectmaster_id', '=', 'twhtransmitalheader.fk_status_id')
        ->leftJoin('temployee', 'temployee.pk_employee_id', '=', 'twhtransmitalitem.fk_employee_id')
        ->leftJoin('tprojectmaster as uom', 'uom.pk_projectmaster_id', '=', 'twhtransmitalitem.fk_uom_id')
        ->leftJoin('tunit', 'tunit.pk_unit_id', '=', 'twhtransmitalitem.fk_unit_id')
        ->leftJoin('twhlocation', 'twhlocation.pk_whlocation_id', '=', 'twhtransmitalitem.fk_whlocation_id')
        ->leftJoin('twhpartnumber', 'twhpartnumber.pk_whpartnumber_id', '=', 'twhtransmitalitem.fk_whpartnumber_id')
        ->leftJoin('twhreqitem', function ($join) {
            $join->on('twhreqitem.pk_whreqitem_id', '=', 'twhtransmitalitem.fk_source_id')
                ->where('twhtransmitalitem.fk_sourcetype_id', '=', 2);
        })
        ->leftJoin(DB::raw('(
            SELECT 
                fk_source_id, 
                SUM(qty) AS qty
            FROM 
                twhtransmitalitem 
            WHERE 
                fk_sourcetype_id = 2
            GROUP BY 
                fk_source_id
        ) as trans'), 'trans.fk_source_id', '=', 'twhreqitem.pk_whreqitem_id')
        ->leftJoin(DB::raw('(
            SELECT 
                fk_whreqitem_id, 
                SUM(qty) AS qty 
            FROM 
                twhorderitem 
            GROUP BY 
                fk_whreqitem_id
        ) as orderqty'), 'orderqty.fk_whreqitem_id', '=', 'twhreqitem.pk_whreqitem_id')
        ->leftJoin(DB::raw('(
            SELECT 
                fk_whreqitem_id, 
                SUM(qty) AS qty 
            FROM 
                twhreceiveitem 
            GROUP BY 
                fk_whreqitem_id
        ) as recqty'), 'recqty.fk_whreqitem_id', '=', 'twhreqitem.pk_whreqitem_id')
  
        ->where(function ($query) use ($datafilter) {
            if ($datafilter["transactiondate"] != "") {
                $datadate = explode(' - ', $datafilter["transactiondate"]);
                if (count($datadate) > 0) {
                    $query->Where('twhtransmitalheader.transmital_date', ">=", $datadate[0]);
                    $query->Where('twhtransmitalheader.transmital_date', "<=", $datadate[1]);
                }
            }
            if ($datafilter["status"] != "") {
                $query->Where('twhtransmitalheader.fk_status_id', decryptForNumber($datafilter["status"]));
            }

            if ($datafilter["kode-unit"] != "") {
                $query->where('tunit.unit_no', 'LIKE', '%' . $datafilter["kode-unit"] . '%');
            }
            if ($datafilter["part-nowh"] != "") {
                $query->where('twhpartnumber.part_no', 'LIKE', '%' . $datafilter["part-nowh"] . '%');
            }
            if ($datafilter["nama-penerima"] != "") {
                $query->where('temployee.full_name', 'LIKE', '%' . $datafilter["nama-penerima"] . '%');
            }
            if ($datafilter["deskripsi"] != "") {
                $query->where('twhtransmitalitem.part_no_description', 'LIKE', '%' . $datafilter["deskripsi"] . '%');
            }
            if ($datafilter["no-pengiriman"] != "") {
                $query->where('twhtransmitalheader.transmital_no', 'LIKE', '%' . $datafilter["no-pengiriman"] . '%');
            }
        })
            ->orderBy('twhtransmitalheader.transmital_date', 'asc')
            ->get();

        $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
        $now = Carbon::now()->getTimestamp();
        $filename = 'WarehouseTransmitDetail ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

        Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

        $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
    }
}
