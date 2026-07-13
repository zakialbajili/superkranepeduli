<?php

namespace App\Http\Controllers\backend\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HseReportController extends Controller
{

    public function index()
    {

        $optionShift = '';
        $listShift = DB::table('thsedata_master')->select('*')->where('type', '=', 'Type Shift')->get();
        foreach ($listShift as $item) {
            $optionShift .= "<option value='" . encryptId($item->pk_hsedatamaster_id) . "'>$item->name</option>";
        }

        $optionDataPelaporan = '';
        $listDataPelaporan = DB::table('thsedata_master')->select('*')->where('type', '=', 'Data Pelaporan')->get();
        foreach ($listDataPelaporan as $item) {
            $optionDataPelaporan .= "<option value='" . encryptId($item->pk_hsedatamaster_id) . "'>$item->name</option>";
        }

        $optionLokasi = '';
        $listLokasi = DB::table('thsedata_master')->select('*')->where('type', '=', 'Lokasi')->get();
        foreach ($listLokasi as $item) {
            $optionLokasi .= "<option value='" . encryptId($item->pk_hsedatamaster_id) . "'>$item->name</option>";
        }

        $optionKategoriBahaya = '';
        $listKategoriBahaya = DB::table('thsedata_master')->select('*')->where('type', '=', 'Kategori Bahaya')->get();
        foreach ($listKategoriBahaya as $item) {
            $optionKategoriBahaya .= "<option value='" . encryptId($item->pk_hsedatamaster_id) . "'>$item->name</option>";
        }

        $optionDepartment = '';
        $listDepartment = DB::table('thsedata_master')->select('*')->where('type', '=', 'Departemen')->get();
        foreach ($listDepartment as $item) {
            $optionDepartment .= "<option value='" . encryptId($item->pk_hsedatamaster_id) . "'>$item->name</option>";
        }

        $optionTindakanTidakAman = '';
        $listTindakanTidakAman = DB::table('thsedata_master')->select('*')->where('type', '=', 'Jenis Tindakan Tidak Aman')->get();
        foreach ($listTindakanTidakAman as $item) {
            $optionTindakanTidakAman .= "<option value='" . encryptId($item->pk_hsedatamaster_id) . "'>$item->name</option>";
        }

        $optionKondisiTidakAman = '';
        $listKondisiTidakAman = DB::table('thsedata_master')->select('*')->where('type', '=', 'Jenis Kondisi Tidak Aman')->get();
        foreach ($listKondisiTidakAman as $item) {
            $optionKondisiTidakAman .= "<option value='" . encryptId($item->pk_hsedatamaster_id) . "'>$item->name</option>";
        }

        return view('backend.user.formreport', [
            'optionShift' => $optionShift,
            'optionDataPelaporan' => $optionDataPelaporan,
            'optionLokasi' => $optionLokasi,
            'optionKategoriBahaya' => $optionKategoriBahaya,
            'optionDepartment' => $optionDepartment,
            'optionTindakanTidakAman' => $optionTindakanTidakAman,
            'optionKondisiTidakAman' => $optionKondisiTidakAman
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input dari Request
        $request->validate([
            'posisi' => 'required|string|max:50',
            'tgl_pelaporan' => 'required|date',
            'shift' => 'required|string',
            'data_pelaporan' => 'required|string',
            'lokasi_bahaya_select' => 'required|string',
            'kategori_bahaya' => 'required|string',
            'desc_temuan_bahaya' => 'required|string',
            'rekomendasi_perbaikan' => 'required|string',
            'dept_penanggungjwb' => 'required|string|max:30',
            'nama_pengawas' => 'required|string|max:100',
            'due_date' => 'required|date',
            'document' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        try {
            // 2. Setup Data Identitas dari Session API
            $employeeNo = session('employee_no');
            $fullName = session('full_name');
            $now = Carbon::now('Asia/Jakarta');

            // 3. Handle Upload File / Foto
            $documentPath = null;
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $documentPath = $file->storeAs('hse-documents', $filename, 'public');
            }



            // ================================================================
            // 4. Logika Penentuan Lokasi Bahaya
            // ================================================================
            $rawLokasi = $request->lokasi_bahaya_select;

            if (blank($rawLokasi) || stripos($rawLokasi, 'other') !== false || stripos($rawLokasi, 'lainnya') !== false) {
                // JIKA OTHER: Ambil langsung dari ketikan teks manual
                $lokasiFinal = $request->lokasi_bahaya_other;
            } else {
                // JIKA PILIH DARI DATABASE: Dekripsi dan jadikan ID sebagai nilai final
                $lokasiFinal = decryptId($rawLokasi);
            }

            // $rawLokasi = $request->lokasi_bahaya_select;

            // if (blank($rawLokasi) || stripos($rawLokasi, 'other') !== false || stripos($rawLokasi, 'lainnya') !== false) {
            //     // JIKA OTHER: Ambil langsung dari ketikan manual
            //     $lokasiFinal = $request->lokasi_bahaya_other;
            // }
            // else {
            //     // JIKA PILIH DARI DATABASE: Dekripsi ID-nya, lalu ambil teks 'name' aslinya
            //     $lokasiId = decryptId($rawLokasi);
            //     $lokasiFinal = DB::table('thsedata_master')
            //         ->where('pk_hsedatamaster_id', $lokasiId)
            //         ->value('name');
            // }

            // ================================================================
            // 5. Logika Penentuan Detail Kategori Bahaya (Tindakan / Kondisi)
            // ================================================================
            $rawDetailBahaya = $request->desc_kategori_tindakan ?: $request->desc_kategori_kondisi;

            if (blank($rawDetailBahaya) || stripos($rawDetailBahaya, 'other') !== false || stripos($rawDetailBahaya, 'lainnya') !== false) {
                // JIKA OTHER: Ambil langsung dari ketikan teks manual
                $descKategoriFinal = $request->desc_kategori_bahaya_other;
            } else {
                // JIKA PILIH DARI DATABASE: Dekripsi dan jadikan ID sebagai nilai final
                $descKategoriFinal = decryptId($rawDetailBahaya);
            }

            // $rawDetailBahaya = $request->desc_kategori_tindakan ?: $request->desc_kategori_kondisi;

            // if (blank($rawDetailBahaya) || stripos($rawDetailBahaya, 'other') !== false || stripos($rawDetailBahaya, 'lainnya') !== false) {
            //     // JIKA OTHER: Ambil langsung dari ketikan manual
            //     $descKategoriFinal = $request->desc_kategori_bahaya_other;
            // }
            // else {
            //     // JIKA PILIH DARI DATABASE: Dekripsi ID-nya, lalu ambil teks 'name' aslinya
            //     $descId = decryptId($rawDetailBahaya);
            //     $descKategoriFinal = DB::table('thsedata_master')
            //         ->where('pk_hsedatamaster_id', $descId)
            //         ->value('name');
            // }

            // 6. Dekripsi untuk kolom-kolom yang menggunakan ID terenkripsi
            $shiftFinal = decryptId($request->shift);
            $dataPelaporanFinal = decryptId($request->data_pelaporan);
            $kategoriBahayaFinal = decryptId($request->kategori_bahaya);
            $deptPenanggungJwbFinal = decryptId($request->dept_penanggungjwb);

            // 7. Eksekusi Insert ke Database
            DB::table('thsepelaporanbahaya')->insert([
                'tgl_pelaporan' => $request->tgl_pelaporan,
                'lokasi_bahaya' => $lokasiFinal,
                'desc_kategori_bahaya' => $descKategoriFinal,

                // Nilai ID Master hasil dekripsi
                'shift' => $shiftFinal,
                'data_pelaporan' => $dataPelaporanFinal,
                'kategori_bahaya' => $kategoriBahayaFinal,
                'dept_penanggungjwb' => $deptPenanggungJwbFinal,

                'desc_temuan_bahaya' => $request->desc_temuan_bahaya,
                'rekomendasi_perbaikan' => $request->rekomendasi_perbaikan,
                'nama_pengawas' => $request->nama_pengawas,
                'due_date' => $request->due_date,
                'status_pelaporan' => '5', // Status Open

                'created_date' => $now->format('Y-m-d H:i:s'),
                'created_by' => $employeeNo,
                'updated_date' => null,
                'updated_by' => null,
                'document' => $documentPath,

                // Identitas Pelapor
                'employee_no' => $employeeNo,
                'full_name' => $fullName,
                'posisi' => $request->posisi,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Laporan bahaya berhasil disubmit!',
                'type' => 'success'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menyimpan laporan. Silakan hubungi Administrator.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function history()
    {
        $employeeNo = session('employee_no');

        // Ambil data dengan Join dan Alias
        $riwayatLaporan = DB::table('thsepelaporanbahaya as a')
            // Join untuk Kategori dan Status (Ini pasti berupa ID)
            ->leftJoin('thsedata_master as master_kategori', 'a.kategori_bahaya', '=', 'master_kategori.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master as master_status', 'a.status_pelaporan', '=', 'master_status.pk_hsedatamaster_id')

            // Join untuk Lokasi dan Detail (Bisa berupa ID, bisa berupa Teks)
            ->leftJoin('thsedata_master as master_lokasi', 'a.lokasi_bahaya', '=', 'master_lokasi.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master as master_desc', 'a.desc_kategori_bahaya', '=', 'master_desc.pk_hsedatamaster_id')

            ->select(
                'a.*',
                'master_kategori.name as nama_kategori',
                'master_status.name as nama_status',

                // KUNCI UTAMA: COALESCE
                // Jika master_lokasi.name NULL (karena input manual), maka ambil a.lokasi_bahaya
                DB::raw('COALESCE(master_lokasi.name, a.lokasi_bahaya) as lokasi_final'),
                DB::raw('COALESCE(master_desc.name, a.desc_kategori_bahaya) as desc_final')
            )
            ->where('a.employee_no', $employeeNo)
            ->orderBy('a.created_date', 'desc')
            ->get();

        return view('backend.user.history', [
            'riwayatLaporan' => $riwayatLaporan
        ]);
    }
}