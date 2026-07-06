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

class ExportExcelHistoryInternalJournal implements ShouldQueue
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
            $data = DB::table("tjournal")
                ->leftJoin('tunit', 'tjournal.fk_unit_id', '=', 'tunit.pk_unit_id')
                ->select(
                    'journal_date AS Tanggal Journal',
                    'tjournal.account_no AS No Akun',
                    'tjournal.account_name AS Nama Akun',
                    'tjournal.account_position AS Akun Posisi',
                    'tjournal.no_ref AS No Referensi',
                    'tunit.unit_no AS Kode Unit',
                    'tjournal.description AS Deskripsi',
                    'debt',
                    'credit',
                    'rate AS Nilai Tukar',
                    'foreignvalue AS Nilai'
                )
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('tjournal.journal_date', ">=", $datadate[0]);
                            $query->Where('tjournal.journal_date', "<=", $datadate[1]);
                        }
                    }
                    if ($datafilter["no_akun"] != "") {
                        $query->Where('tjournal.account_no', ($datafilter["no_akun"]));
                    }
                    if ($datafilter["no_referensi"] != "") {
                        $query->Where('tjournal.no_ref', ($datafilter["no_referensi"]));
                    }

                })
                ->get();
            $dataHeader = array_keys(json_decode(json_encode($data[0]), true));
            $now = Carbon::now()->getTimestamp();
            $filename = 'HISTORY INTERNAL JOURNAL  ' . $this->user->employees->employee_no . '_' . $now . '.xlsx';

            Excel::store(new ExcelExport($data, $dataHeader), $filename, 'public');

            $this->sendNotificationFileCreated($this->user->pk_user_id, 'Export ' . $filename . ' Berhasil Dibuat', $filename);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
