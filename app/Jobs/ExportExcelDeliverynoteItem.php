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

class ExportExcelDeliverynoteItem implements ShouldQueue
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
            $data = DB::table('tdeliveryorder_detail')
                ->select(
                    'tdeliveryorder.deliveryorder_no AS No Surat Jalan',
                    'tdeliveryorder.reference_no AS No Referensi',
                    'tdeliveryorder.customer_name AS Nama Customer',
                    'tdeliveryorder.ship_to AS Dikirim ke',
                    'tdeliveryorder.register_date AS Tanggal Registrasi',
                    'tdeliveryorder.promissed_delivery_date AS Tanggal Janji Kirim',
                    'tdeliveryorder.sent_date AS Tanggal dikirim',
                    'tunit.unit_no AS Kode Unit',
                    'tdeliveryorder.vehicle_no AS NO Kendaraan',
                    'tdeliveryorder.courier_by_name AS Nama Kurir',
                    'tdeliveryorder.courier_phone AS Telp Kurir',
                    'tdeliveryorder_detail.part_no AS Part Number',
                    'tdeliveryorder_detail.detail_description AS Detail Deskripsi',
                    'tdeliveryorder_detail.received_by_name AS Diterima Oleh',
                    'tdeliveryorder_detail.received_date AS Tanggal Diterima',
                    'tdeliveryorder_detail.qty AS Jumlah',
                    'tuom.name AS Uom',
                    'tdeliveryorder_detail.reff_no AS NO Referensi item',
                    'tdeliveryorder_detail.remarks AS Catatan'
                )
                ->leftJoin('tdeliveryorder', 'tdeliveryorder_detail.fk_deliveryorder_id', '=', 'tdeliveryorder.pk_deliveryorder_id')
                ->leftJoin('tunit', 'tdeliveryorder.fk_unit_id', '=', 'tunit.pk_unit_id')
                ->leftJoin('vdeliveryorderitemview', 'tdeliveryorder_detail.item_source_id', '=', 'vdeliveryorderitemview.source_id')
                ->leftJoin('tprojectmaster as tuom', function ($join) {
                    $join->on('tuom.pk_projectmaster_id', '=', 'tdeliveryorder_detail.fk_uom_id')
                        ->where('tuom.master_category', '=', "Warehouse UOM");
                })
                ->leftJoin('tsupplier', 'tsupplier.pk_supplier_id', '=', 'tdeliveryorder.fk_supplier_id')
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('tdeliveryorder.register_date', ">=", $datadate[0]);
                            $query->Where('tdeliveryorder.register_date', "<=", $datadate[1]);
                        }
                    }
                    if ($datafilter["no_unit"] != "") {
                        $query->where('tunit.unit_no', 'LIKE', '%' . $datafilter["no_unit"] . '%');
                    }
                    if ($datafilter["no_kendaraan"] != "") {
                        $query->where('tdeliveryorder.vehicle_no', 'LIKE', '%' . $datafilter["no_kendaraan"] . '%');
                    }
                    if ($datafilter["no_pengiriman"] != "") {
                        $query->where('tdeliveryorder.deliveryorder_no', 'LIKE', '%' . $datafilter["no_pengiriman"] . '%');
                    }
                    if ($datafilter["customer_id"] != "") {
                        $query->Where('tdeliveryorder.fk_customer_id', decryptForNumber($datafilter["customer_id"]));
                    } else {
                        if ($datafilter["nama_pelanggan"] != "") {
                            $query->where('tdeliveryorder.customer_name', 'LIKE', '%' . $datafilter["nama_pelanggan"] . '%');
                        }
                    }
                })
                ->get();

            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'Surat Jalan Item ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
