<?php

namespace App\Jobs;

use App\Exports\ExcelExport;
use App\Models\UserModel;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\ModuleTraits;

class ExportExcelDeliverynote implements ShouldQueue
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
            $data = DB::table('vdeliveryorder')
                ->select(
                    "deliveryorder_no AS Nomor Surat Jalan",
                    "register_date AS Tanggal Pendaftaran",
                    "sent_date AS Tanggal Pengiriman",
                    "promissed_delivery_date AS Tanggal Janji Pengiriman",
                    "ship_to AS Dikirim",
                    "customer_name AS Nama Pelanggan",
                    "pic_name AS Nama Penerima",
                    "pic_phone_no AS Telepon Penerima",
                    "reference_no AS Nomor Referensi ",
                    "wo_no AS Wo No",
                    "vehicle_no AS Nomor Kendaraan",
                    "notes AS Catatan",
                    "created_date AS Tanggal Dibuat ",
                    "created_by AS Dibuat Oleh",
                    "updated_date AS Tanggal Diperbarui",
                    "updated_by AS Diperbarui Oleh"
                )
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('register_date', ">=", $datadate[0]);
                            $query->Where('register_date', "<=", $datadate[1]);
                        }
                    }
                    if ($datafilter["customer_id"] != "") {
                        $query->Where('fk_customer_id', decryptForNumber($datafilter["customer_id"]));
                    } else {
                        if ($datafilter["nama_pelanggan"] != "") {
                            $query->where('customer_name', 'LIKE', '%' . $datafilter["nama_pelanggan"] . '%');
                        }
                    }
                })
                ->get();

            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'Surat Jalan ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
