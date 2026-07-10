<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HsePelaporanBahayaSeeder extends Seeder
{
    /**
     * Seed the thsepelaporanbahaya table with sample data.
     *
     * Foreign key references (thsedata_master):
     *   shift          : 1 = Pagi, 2 = Malam
     *   kategori_bahaya: 3 = Kondisi Tidak Aman, 4 = Tindakan Tidak Aman
     *   desc_kategori_bahaya: 10-19 = Jenis Kondisi Tidak Aman (detail)
     *                        20-35 = Jenis Tindakan Tidak Aman (detail)
     *   status_pelaporan: 5 = Open, 6 = On Progress, 7 = Closed
     *   data_pelaporan  : 8 = Inspection, 9 = Hazard report
     *   lokasi_bahaya  : 36-48 = Lokasi (master data)
     *   dept_penanggungjwb: 49-56 = Departemen (master data)
     */
    public function run(): void
    {
        $data = [
            [
                'tgl_pelaporan'      => '2026-07-01',
                'lokasi_bahaya'      => 46, // Pool 2-Workshop
                'shift'              => 1,
                'data_pelaporan'     => 8,
                'kategori_bahaya'    => 3,
                'desc_kategori_bahaya' => 19, // Jalan Licin dan Bergelombang
                'full_name'          => 'Budi Santoso',
                'employee_no'        => '002809',
                'posisi'             => 'Operator',
                'desc_temuan_bahaya' => 'Karyawan berpotensi terpeleset saat melintasi area tersebut',
                'rekomendasi_perbaikan' => 'Segera membersihkan tumpahan oli dan memasang rambu peringatan',
                'dept_penanggungjwb' => 50, // Maintenance
                'nama_pengawas'      => 'Budi Santoso',
                'due_date'           => '2026-07-03',
                'status_pelaporan'   => 7,
                'created_date'       => '2026-07-01 07:30:00',
                'created_by'         => '002809',
                'updated_date'       => '2026-07-03 09:00:00',
                'updated_by'         => '002809',
            ],
            [
                'tgl_pelaporan'      => '2026-07-01',
                'lokasi_bahaya'      => 45, // Pool 2-Yard
                'shift'              => 2,
                'data_pelaporan'     => 9,
                'kategori_bahaya'    => 4,
                'desc_kategori_bahaya' => 21, // Tidak Menggunakan Body Harness
                'full_name'          => 'Agus Wijaya',
                'employee_no'        => '001005',
                'posisi'             => 'HSE Officer',
                'desc_temuan_bahaya' => 'Melanggar prosedur K3 dan berisiko jatuh dari ketinggian',
                'rekomendasi_perbaikan' => 'Memberikan peringatan tertulis dan mewajibkan penggunaan safety harness',
                'dept_penanggungjwb' => 53, // HSE
                'nama_pengawas'      => 'Agus Wijaya',
                'due_date'           => '2026-07-02',
                'status_pelaporan'   => 7,
                'created_date'       => '2026-07-01 15:45:00',
                'created_by'         => '001005',
                'updated_date'       => '2026-07-02 10:30:00',
                'updated_by'         => '001005',
            ],
            [
                'tgl_pelaporan'      => '2026-07-03',
                'lokasi_bahaya'      => 36, // UCC Tangguh
                'shift'              => 1,
                'data_pelaporan'     => 8,
                'kategori_bahaya'    => 3,
                'desc_kategori_bahaya' => 17, // Tidak Tersedia Pelindung Keselamatan
                'full_name'          => 'Dewi Sartika',
                'employee_no'        => '003012',
                'posisi'             => 'Staff GA',
                'desc_temuan_bahaya' => 'Risiko tersengat listrik saat karyawan menyentuh area tersebut',
                'rekomendasi_perbaikan' => 'Mengganti kabel dan stop kontak, serta melakukan pengecekan instalasi listrik',
                'dept_penanggungjwb' => 56, // GA
                'nama_pengawas'      => 'Dewi Sartika',
                'due_date'           => '2026-07-05',
                'status_pelaporan'   => 6,
                'created_date'       => '2026-07-03 08:15:00',
                'created_by'         => '003012',
                'updated_date'       => '2026-07-04 11:00:00',
                'updated_by'         => '003012',
            ],
            [
                'tgl_pelaporan'      => '2026-07-04',
                'lokasi_bahaya'      => 46, // Pool 2-Workshop
                'shift'              => 1,
                'data_pelaporan'     => 9,
                'kategori_bahaya'    => 3,
                'desc_kategori_bahaya' => 17, // Tidak Tersedia Pelindung Keselamatan
                'full_name'          => 'Budi Santoso',
                'employee_no'        => '002809',
                'posisi'             => 'HSE Supervisor',
                'desc_temuan_bahaya' => 'Tidak siap digunakan jika terjadi kebakaran darurat',
                'rekomendasi_perbaikan' => 'Melakukan pengisian ulang APAR dan menjadwalkan uji kelayakan rutin',
                'dept_penanggungjwb' => 53, // HSE
                'nama_pengawas'      => 'Budi Santoso',
                'due_date'           => '2026-07-07',
                'status_pelaporan'   => 6,
                'created_date'       => '2026-07-04 09:30:00',
                'created_by'         => '002809',
                'updated_date'       => '2026-07-05 08:00:00',
                'updated_by'         => '002809',
            ],
            [
                'tgl_pelaporan'      => '2026-07-05',
                'lokasi_bahaya'      => 45, // Pool 2-Yard
                'shift'              => 2,
                'data_pelaporan'     => 9,
                'kategori_bahaya'    => 4,
                'desc_kategori_bahaya' => 20, // Tidak Menggunakan APD Lengkap
                'full_name'          => 'Hendra Gunawan',
                'employee_no'        => '004021',
                'posisi'             => 'Operator Crane',
                'desc_temuan_bahaya' => 'Berisiko cedera kepala jika tertimpa material',
                'rekomendasi_perbaikan' => 'Teguran lisan dan pengawasan ketat penggunaan APD oleh supervisor',
                'dept_penanggungjwb' => 49, // Operasional
                'nama_pengawas'      => 'Hendra Gunawan',
                'due_date'           => '2026-07-05',
                'status_pelaporan'   => 7,
                'created_date'       => '2026-07-05 14:00:00',
                'created_by'         => '004021',
                'updated_date'       => '2026-07-05 16:30:00',
                'updated_by'         => '004021',
            ],
            [
                'tgl_pelaporan'      => '2026-07-06',
                'lokasi_bahaya'      => 48, // Pool 3-Warehouse
                'shift'              => 1,
                'data_pelaporan'     => 8,
                'kategori_bahaya'    => 3,
                'desc_kategori_bahaya' => 10, // Ruang Kerja yang Sempit
                'full_name'          => 'Slamet Riyadi',
                'employee_no'        => '005017',
                'posisi'             => 'Warehouse Staff',
                'desc_temuan_bahaya' => 'Sparepart berat berpotensi jatuh dan melukai karyawan',
                'rekomendasi_perbaikan' => 'Memperbaiki rak dan memastikan semua baut terpasang dengan benar',
                'dept_penanggungjwb' => 54, // Warehouse
                'nama_pengawas'      => 'Slamet Riyadi',
                'due_date'           => '2026-07-08',
                'status_pelaporan'   => 5,
                'created_date'       => '2026-07-06 10:00:00',
                'created_by'         => '005017',
                'updated_date'       => null,
                'updated_by'         => null,
            ],
            [
                'tgl_pelaporan'      => '2026-07-06',
                'lokasi_bahaya'      => 39, // Neptum
                'shift'              => 2,
                'data_pelaporan'     => 9,
                'kategori_bahaya'    => 3,
                'desc_kategori_bahaya' => 11, // Lingkungan Kerja yang Kotor (Tidak 5R)
                'full_name'          => 'Agus Wijaya',
                'employee_no'        => '001005',
                'posisi'             => 'HSE Officer',
                'desc_temuan_bahaya' => 'Area licin dan berisiko kebakaran jika terkena percikan api',
                'rekomendasi_perbaikan' => 'Membersihkan ceceran solar dan menyediakan spill kit di area fueling',
                'dept_penanggungjwb' => 53, // HSE
                'nama_pengawas'      => 'Agus Wijaya',
                'due_date'           => '2026-07-07',
                'status_pelaporan'   => 5,
                'created_date'       => '2026-07-06 17:15:00',
                'created_by'         => '002809',
                'updated_date'       => null,
                'updated_by'         => null,
            ],
            [
                'tgl_pelaporan'      => '2026-07-07',
                'lokasi_bahaya'      => 46, // Pool 2-Workshop
                'shift'              => 1,
                'data_pelaporan'     => 9,
                'kategori_bahaya'    => 4,
                'desc_kategori_bahaya' => 20, // Tidak Menggunakan APD Lengkap
                'full_name'          => 'Supriyadi',
                'employee_no'        => '003012',
                'posisi'             => 'Teknisi Las',
                'desc_temuan_bahaya' => 'Paparan sinar las dapat merusak mata dalam jangka pendek maupun panjang',
                'rekomendasi_perbaikan' => 'Menyediakan dan mewajibkan penggunaan welding mask yang layak',
                'dept_penanggungjwb' => 49, // Operasional
                'nama_pengawas'      => 'Supriyadi',
                'due_date'           => '2026-07-08',
                'status_pelaporan'   => 5,
                'created_date'       => '2026-07-07 08:30:00',
                'created_by'         => '003012',
                'updated_date'       => null,
                'updated_by'         => null,
            ],
            [
                'tgl_pelaporan'      => '2026-07-07',
                'lokasi_bahaya'      => 42, // Mess Adiarta
                'shift'              => 1,
                'data_pelaporan'     => 8,
                'kategori_bahaya'    => 3,
                'desc_kategori_bahaya' => 19, // Jalan Licin dan Bergelombang
                'full_name'          => 'Dewi Sartika',
                'employee_no'        => '001005',
                'posisi'             => 'Staff GA',
                'desc_temuan_bahaya' => 'Karyawan berpotensi terpeleset saat jam istirahat',
                'rekomendasi_perbaikan' => 'Memasang rambu peringatan dan membersihkan lantai secara berkala',
                'dept_penanggungjwb' => 56, // GA
                'nama_pengawas'      => 'Dewi Sartika',
                'due_date'           => '2026-07-08',
                'status_pelaporan'   => 5,
                'created_date'       => '2026-07-07 12:00:00',
                'created_by'         => '001005',
                'updated_date'       => null,
                'updated_by'         => null,
            ],
            [
                'tgl_pelaporan'      => '2026-07-08',
                'lokasi_bahaya'      => 45, // Pool 2-Yard
                'shift'              => 2,
                'data_pelaporan'     => 9,
                'kategori_bahaya'    => 4,
                'desc_kategori_bahaya' => 28, // Memakai alat atau perlengkapan yang rusak
                'full_name'          => 'Hendra Gunawan',
                'employee_no'        => '004021',
                'posisi'             => 'Rigger',
                'desc_temuan_bahaya' => 'Risiko sling putus dan beban jatuh yang dapat menyebabkan kecelakaan fatal',
                'rekomendasi_perbaikan' => 'Mengganti wire rope dengan yang baru dan melakukan inspeksi rutin',
                'dept_penanggungjwb' => 49, // Operasional
                'nama_pengawas'      => 'Hendra Gunawan',
                'due_date'           => '2026-07-10',
                'status_pelaporan'   => 5,
                'created_date'       => '2026-07-08 07:00:00',
                'created_by'         => '004021',
                'updated_date'       => null,
                'updated_by'         => null,
            ],
        ];

        // Hapus data existing dulu agar bisa di-re-run dengan aman
        DB::table('thsepelaporanbahaya')->truncate();
        DB::table('thsepelaporanbahaya')->insert($data);
    }
}
