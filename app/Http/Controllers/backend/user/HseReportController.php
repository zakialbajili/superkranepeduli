<?php

namespace App\Http\Controllers\backend\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HseReportController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Cek session login dari API
            if (!session('is_logged_in_api')) {
                // Jika kosong, kembalikan ke halaman login
                return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
            }
            return $next($request);
        });
    }
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
            $optionLokasi .= "<option value='$item->name'>$item->name</option>";
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
            $optionTindakanTidakAman .= "<option value='$item->name'>$item->name</option>";
            // $optionTindakanTidakAman .= "<option value='" . encryptId($item->pk_hsedatamaster_id) . "'>$item->name</option>";
        }
        $optionKondisiTidakAman = '';
        $listKondisiTidakAman = DB::table('thsedata_master')->select('*')->where('type', '=', 'Jenis Kondisi Tidak Aman')->get();
        foreach ($listKondisiTidakAman as $item) {
            $optionKondisiTidakAman .= "<option value='$item->name'>$item->name</option>";
            // $optionKondisiTidakAman .= "<option value='" . encryptId($item->pk_hsedatamaster_id) . "'>$item->name</option>";
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
            'document' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        // dd([
        //     'desc_kategori_tindakan' => $request->desc_kategori_tindakan,
        //     'desc_kategori_kondisi'  => $request->desc_kategori_kondisi,
        //     'desc_kategori_bahaya_other' => $request->desc_kategori_bahaya_other,
        //     'all_request' => $request->all()
        // ]);

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

            // 4. Logika Penentuan Lokasi Bahaya (Teks Murni dari HTML)
            $lokasiFinal = $request->lokasi_bahaya_select;
            if (blank($lokasiFinal) || stripos($lokasiFinal, 'other') !== false || stripos($lokasiFinal, 'lainnya') !== false) {
                $lokasiFinal = $request->lokasi_bahaya_other;
            }

            // 5. Logika Penentuan Detail Kategori Bahaya (Tindakan / Kondisi)
            // Mengambil nilai dari dropdown yang tidak disabled di HTML
            $rawDetailBahaya = $request->desc_kategori_tindakan ?: $request->desc_kategori_kondisi;

            if (blank($rawDetailBahaya) || stripos($rawDetailBahaya, 'other') !== false || stripos($rawDetailBahaya, 'lainnya') !== false) {
                // Jika memilih "Other", ambil dari input text manual
                $descKategoriFinal = $request->desc_kategori_bahaya_other;
            } else {
                // Jika memilih opsi bawaan, langsung ambil teks nilainya (karena sudah berupa teks murni)
                $descKategoriFinal = $rawDetailBahaya;
            }

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
        // Ambil NIK dari session
        $nikUser = session('employee_no');

        // Tarik data menggunakan Query Builder + JOIN ke tabel master
        $riwayatLaporan = DB::table('thsepelaporanbahaya as a')
            ->select(
                'a.*',
                'kat.name as nama_kategori', // Ambil nama kategori
                'stat.name as nama_status'   // Ambil nama status
            )
            // Relasi untuk Kategori Bahaya
            ->leftJoin('thsedata_master as kat', 'a.kategori_bahaya', '=', 'kat.pk_hsedatamaster_id')
            // Relasi untuk Status Pelaporan
            ->leftJoin('thsedata_master as stat', 'a.status_pelaporan', '=', 'stat.pk_hsedatamaster_id')

            ->where('a.created_by', $nikUser)
            ->orderBy('a.created_date', 'desc')
            ->get();

        // Lempar data ke view
        return view('backend.user.history', [
            'riwayatLaporan' => $riwayatLaporan
        ]);
    }
}