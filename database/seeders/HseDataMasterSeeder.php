<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HseDataMasterSeeder extends Seeder
{
    /**
     * Seed the thsedata_master table.
     *
     * Data referensi untuk modul HSE:
     *   Type Shift       → shift
     *   Data Pelaporan   → data_pelaporan
     *   Kategori Bahaya  → kategori_bahaya
     *   Status Laporan   → status_pelaporan
     *
     * ID dibuat fixed (bukan auto-increment) agar sesuai dengan
     * foreign key references dari thsepelaporanbahaya.
     */
    public function run(): void
    {
        $data = [
            // ---- Type Shift ----
            [
                'pk_hsedatamaster_id' => 1,
                'name'       => 'Pagi',
                'type'       => 'Type Shift',
                'param_1'    => null,
                'param_2'    => null,
                'created_date' => '2026-07-07 14:33:31',
                'created_by'   => '002809',
            ],
            [
                'pk_hsedatamaster_id' => 2,
                'name'       => 'Malam',
                'type'       => 'Type Shift',
                'param_1'    => null,
                'param_2'    => null,
                'created_date' => '2026-07-07 14:33:49',
                'created_by'   => '002809',
            ],

            // ---- Kategori Bahaya ----
            [
                'pk_hsedatamaster_id' => 3,
                'name'       => 'Kondisi Tidak Aman',
                'type'       => 'Kategori Bahaya',
                'param_1'    => null,
                'param_2'    => null,
                'created_date' => '2026-07-07 14:34:38',
                'created_by'   => '002809',
            ],
            [
                'pk_hsedatamaster_id' => 4,
                'name'       => 'Tindakan Tidak Aman',
                'type'       => 'Kategori Bahaya',
                'param_1'    => null,
                'param_2'    => null,
                'created_date' => '2026-07-07 14:48:01',
                'created_by'   => '002809',
            ],

            // ---- Status Laporan ----
            [
                'pk_hsedatamaster_id' => 5,
                'name'       => 'Open',
                'type'       => 'Status Laporan',
                'param_1'    => null,
                'param_2'    => null,
                'created_date' => '2026-07-07 14:49:44',
                'created_by'   => '002809',
            ],
            [
                'pk_hsedatamaster_id' => 6,
                'name'       => 'On Progress',
                'type'       => 'Status Laporan',
                'param_1'    => null,
                'param_2'    => null,
                'created_date' => '2026-07-07 14:50:01',
                'created_by'   => '002809',
            ],
            [
                'pk_hsedatamaster_id' => 7,
                'name'       => 'Closed',
                'type'       => 'Status Laporan',
                'param_1'    => null,
                'param_2'    => null,
                'created_date' => '2026-07-07 14:50:17',
                'created_by'   => '002809',
            ],

            // ---- Data Pelaporan ----
            [
                'pk_hsedatamaster_id' => 8,
                'name'       => 'Inspection',
                'type'       => 'Data Pelaporan',
                'param_1'    => null,
                'param_2'    => null,
                'created_date' => '2026-07-07 16:39:39',
                'created_by'   => '002809',
            ],
            [
                'pk_hsedatamaster_id' => 9,
                'name'       => 'Hazard report',
                'type'       => 'Data Pelaporan',
                'param_1'    => null,
                'param_2'    => null,
                'created_date' => '2026-07-08 08:24:45',
                'created_by'   => '002809',
            ],
        ];

        // Insert or skip — jika sudah ada data, jangan di-insert ulang
        // supaya tidak conflict. Hanya insert jika tabel kosong.
        if (DB::table('thsedata_master')->count() === 0) {
            DB::table('thsedata_master')->insert($data);
        }
    }
}
