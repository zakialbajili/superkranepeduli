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
     *   Type Shift              → shift
     *   Data Pelaporan          → data_pelaporan
     *   Kategori Bahaya         → kategori_bahaya
     *   Status Laporan          → status_pelaporan
     *   Jenis Kondisi Tidak Aman → desc_kategori_bahaya (detail)
     *   Jenis Tindakan Tidak Aman → desc_kategori_bahaya (detail)
     *
     * ID dibuat fixed (bukan auto-increment) agar sesuai dengan
     * foreign key references dari thsepelaporanbahaya.
     */
    public function run(): void
    {
        $data = [
            // ---- Type Shift ----
            ['pk_hsedatamaster_id' => 1,  'name' => 'Pagi',                           'type' => 'Type Shift',                   'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-07 14:33:31', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 2,  'name' => 'Malam',                          'type' => 'Type Shift',                   'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-07 14:33:49', 'created_by' => '002809'],

            // ---- Kategori Bahaya ----
            ['pk_hsedatamaster_id' => 3,  'name' => 'Kondisi Tidak Aman',             'type' => 'Kategori Bahaya',              'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-07 14:34:38', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 4,  'name' => 'Tindakan Tidak Aman',            'type' => 'Kategori Bahaya',              'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-07 14:48:01', 'created_by' => '002809'],

            // ---- Status Laporan ----
            ['pk_hsedatamaster_id' => 5,  'name' => 'Open',                           'type' => 'Status Laporan',               'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-07 14:49:44', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 6,  'name' => 'On Progress',                    'type' => 'Status Laporan',               'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-07 14:50:01', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 7,  'name' => 'Closed',                         'type' => 'Status Laporan',               'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-07 14:50:17', 'created_by' => '002809'],

            // ---- Data Pelaporan ----
            ['pk_hsedatamaster_id' => 8,  'name' => 'Inspection',                     'type' => 'Data Pelaporan',               'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-07 16:39:39', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 9,  'name' => 'Hazard report',                  'type' => 'Data Pelaporan',               'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 08:24:45', 'created_by' => '002809'],

            // ---- Jenis Kondisi Tidak Aman (Detail, param_1=3) ----
            ['pk_hsedatamaster_id' => 10, 'name' => 'Ruang Kerja yang Sempit',                                       'type' => 'Jenis Kondisi Tidak Aman',  'param_1' => '3', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 11, 'name' => 'Lingkungan Kerja yang Kotor (Tidak 5R)',                        'type' => 'Jenis Kondisi Tidak Aman',  'param_1' => '3', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 12, 'name' => 'Penerangan yang Kurang atau Lebih',                              'type' => 'Jenis Kondisi Tidak Aman',  'param_1' => '3', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 13, 'name' => 'Terpapar Sinar Radiasi',                                         'type' => 'Jenis Kondisi Tidak Aman',  'param_1' => '3', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 14, 'name' => 'Ventilasi Udara yang Tidak Baik',                                 'type' => 'Jenis Kondisi Tidak Aman',  'param_1' => '3', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 15, 'name' => 'Terpapar Suara Bising',                                          'type' => 'Jenis Kondisi Tidak Aman',  'param_1' => '3', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 16, 'name' => 'Tidak Terdapat Sistem Peringatan yang Memadai',                  'type' => 'Jenis Kondisi Tidak Aman',  'param_1' => '3', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 17, 'name' => 'Tidak Tersedia Pelindung Keselamatan',                           'type' => 'Jenis Kondisi Tidak Aman',  'param_1' => '3', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 18, 'name' => 'Terpapar Suhu yang Rendah atau Tinggi',                          'type' => 'Jenis Kondisi Tidak Aman',  'param_1' => '3', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 19, 'name' => 'Jalan Licin dan Bergelombang',                                   'type' => 'Jenis Kondisi Tidak Aman',  'param_1' => '3', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],

            // ---- Jenis Tindakan Tidak Aman (Detail, param_1=4) ----
            ['pk_hsedatamaster_id' => 20, 'name' => 'Tidak Menggunakan APD Lengkap',                                  'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 21, 'name' => 'Tidak Menggunakan Body Harness',                                 'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 22, 'name' => 'Merokok Tidak Pada Tempatnya',                                   'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 23, 'name' => 'Mengoperasikan Unit Tanpa Kompetensi',                           'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 24, 'name' => 'Mengangkat Beban yang Tidak Sesuai Standar Keselamatan',         'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 25, 'name' => 'Bekerja dalam Pengaruh Obat atau Alkohol',                       'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 26, 'name' => 'Cara mengangkat yang salah',                                     'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 27, 'name' => 'Bergerak pada kecepatan yang tidak sesuai',                      'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 28, 'name' => 'Memakai alat atau perlengkapan yang rusak',                      'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 29, 'name' => 'Bersenda gurau saat bekerja',                                    'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 30, 'name' => 'Memperbaiki Mesin Yang Sedang Hidup',                            'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 31, 'name' => 'Posisi Tubuh yang Salah Saat Bekerja',                           'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 32, 'name' => 'Tidak Melakukan Pre Use Inspection Sebelum Mengoperasikan Unit', 'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 33, 'name' => 'Tidak Memasang LOTO Sebelum Melakukan Perbaikan Pada Unit',      'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 34, 'name' => 'Tidak Mengikuti TBM sebelum Bekerja',                            'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],
            ['pk_hsedatamaster_id' => 35, 'name' => 'Tidak Membuat JSA Terhadap Pekerjaan Berisiko',                  'type' => 'Jenis Tindakan Tidak Aman', 'param_1' => '4', 'param_2' => null, 'created_date' => '2026-07-08 09:00:00', 'created_by' => '002809'],

            // ---- Lokasi ----
            ['pk_hsedatamaster_id' => 36, 'name' => 'UCC Tangguh',           'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 37, 'name' => 'Comp 2',                'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 38, 'name' => 'Comp 3',                'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 39, 'name' => 'Neptum',                'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 40, 'name' => 'Marine Base Stored',    'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 41, 'name' => 'Mess K2',               'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 42, 'name' => 'Mess Adiarta',          'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 43, 'name' => 'Mess K3',               'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 44, 'name' => 'Mess Simpang',          'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 45, 'name' => 'Pool 2-Yard',           'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 46, 'name' => 'Pool 2-Workshop',       'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 47, 'name' => 'Pool 3-Yard',           'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 48, 'name' => 'Pool 3-Warehouse',      'type' => 'Lokasi',      'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],

            // ---- Departemen ----
            ['pk_hsedatamaster_id' => 49, 'name' => 'Operasional',           'type' => 'Departemen',  'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 50, 'name' => 'Maintenance',           'type' => 'Departemen',  'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 51, 'name' => 'Logistik',              'type' => 'Departemen',  'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 52, 'name' => 'Purchasing',            'type' => 'Departemen',  'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 53, 'name' => 'HSE',                   'type' => 'Departemen',  'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 54, 'name' => 'Warehouse',             'type' => 'Departemen',  'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 55, 'name' => 'IT',                    'type' => 'Departemen',  'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
            ['pk_hsedatamaster_id' => 56, 'name' => 'GA',                    'type' => 'Departemen',  'param_1' => null, 'param_2' => null, 'created_date' => '2026-07-08 12:00:00', 'created_by' => 'seeder'],
        ];

        DB::table('thsedata_master')->insertOrIgnore($data);
    }
}
