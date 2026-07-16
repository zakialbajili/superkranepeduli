<?php

namespace App\Http\Controllers\backend\master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function index()
    {
        $headerparam = [
            'headertag' => 'Master Data',
            'headername' => 'Master Data',
            'headerlink' => '#',
            'parentname' => 'Halaman Utama',
            'parentlink' => '#',
        ];

        // Ambil daftar tipe unik
        $types = DB::table('thsedata_master')
            ->select('type')
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type', 'asc')
            ->pluck('type');

        // Tipe pertama yang akan aktif saat halaman dimuat
        $firstType = $types->first();

        return view('backend.master.admin.masterdata.index', compact('headerparam', 'types', 'firstType'));
    }

    public function datatable(Request $request)
    {
        // Base query
        $queryBuilder = DB::table('thsedata_master AS t')
            ->select('t.pk_hsedatamaster_id', 't.name', 't.type', 't.param_2');

        $alldata = (clone $queryBuilder)->count();

        // Pencarian (Search)
        if (!empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $queryBuilder->where('t.name', 'like', "%{$searchValue}%");
        }

        // Filter berdasarkan Tab
        if (!empty($request['filter_type'])) {
            $queryBuilder->where('t.type', $request['filter_type']);
        }

        $filteredrecordcount = (clone $queryBuilder)->count();

        // Sorting
        $dir = $request['order']['0']['dir'] ?? 'asc';
        $datas = $queryBuilder->orderByRaw("COALESCE(t.param_2, '0') DESC")->orderBy('t.name', $dir)
            ->skip($request['start'])
            ->take($request['length'])
            ->get();

        $dataresult = [];

        foreach ($datas as $data) {
            $subdata = [];
            $id = encrypt($data->pk_hsedatamaster_id);

            // Kolom 0: Nama
            $subdata[] = $data->name;
            // Kolom 1: Toggle Aktif
            // Cek apakah param_2 bernilai 1. Jika ya, tambahkan atribut 'checked'
            $isChecked = ($data->param_2 == '1' || $data->param_2 === 1) ? 'checked' : '';
            $subdata[] = '
                <div class="d-flex justify-content-center">
                    <label class="switch mb-0">
                        <input type="checkbox" class="toggle-active" data-id="' . $id . '" ' . $isChecked . '>
                        <span class="slider round"></span>
                    </label>
                </div>
            ';
            // Kolom 2: Aksi (Tombol Edit)
            $subdata[] = '<button class="btn btn-light text-primary btn-action-icon btn-edit shadow-sm border" data-id="' . $id . '" title="Edit Data"><i class="fas fa-pen text-sm"></i></button>';
            $dataresult[] = $subdata;
        }

        return response()->json([
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string'
        ], [
            'name.required' => 'Nama data baru wajib diisi',
            'type.required' => 'Tipe data wajib dipilih',
        ]);

        try {
            DB::beginTransaction();

            $param1Value = null;
            if ($request->type === 'Jenis Kondisi Tidak Aman') {
                $param1Value = DB::table('thsedata_master')
                    ->where('name', 'Kondisi Tidak Aman')
                    ->where('type', 'Kategori Bahaya')
                    ->value('pk_hsedatamaster_id');
            } elseif ($request->type === 'Jenis Tindakan Tidak Aman') {
                $param1Value = DB::table('thsedata_master')
                    ->where('name', 'Tindakan Tidak Aman')
                    ->where('type', 'Kategori Bahaya')
                    ->value('pk_hsedatamaster_id');
            }

            $insertData = [
                'name' => $request->name,
                'type' => $request->type,
                'param_1' => $param1Value,
                'param_2' => 1,
                'created_date' => now(),
                'created_by' => Auth::user()->employee_no,
            ];

            DB::table('thsedata_master')->insert($insertData);

            activity()
                ->causedBy(Auth::user()->pk_user_id ?? 1)
                ->withProperties(['INSERT' => $insertData])
                ->log('Create - ' . Route::currentRouteName());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Ditambahkan'
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Silahkan Hubungi Administrator. (' . $th->getMessage() . ')'
            ]);
        }
    }

    // 2. FUNGSI EDIT (Mengambil Data)
    public function edit($id)
    {
        try {
            $pk_id = decrypt($id);
            $data = DB::table('thsedata_master')->where('pk_hsedatamaster_id', $pk_id)->first();

            // PROTEKSI: Enkripsi ID sebelum dikirimkan ke frontend (browser)
            if ($data) {
                $data->pk_hsedatamaster_id = encrypt($data->pk_hsedatamaster_id);
            }

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan!']);
        }
    }

    // 3. FUNGSI UPDATE (Menyimpan Perubahan)
    public function update(Request $request, $id)
    {
        try {
            $pk_id = decrypt($id);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'Data Tidak Sesuai!']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama data wajib diisi',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $request->name,
                'updated_date' => now(),
                'updated_by' => Auth::user()->employee_no,
            ];

            DB::table('thsedata_master')
                ->where('pk_hsedatamaster_id', $pk_id)
                ->update($updateData);

            // Activity Log
            activity()
                ->causedBy(Auth::user()->pk_user_id ?? 1)
                ->withProperties(['UPDATE' => $updateData])
                ->log('Update - ' . Route::currentRouteName());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Diperbarui'
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Silahkan Hubungi Administrator. (' . $th->getMessage() . ')'
            ]);
        }
    }

    public function toggleActive(Request $request)
    {
        try {
            $pk_id = decrypt($request->id);
            $status = $request->status; // Menerima angka '1' atau '0'

            DB::table('thsedata_master')
                ->where('pk_hsedatamaster_id', $pk_id)
                ->update([
                    'param_2' => $status,
                    'updated_date' => now(),
                    'updated_by' => Auth::user()->employee_no,
                ]);

            return response()->json(['status' => 'success', 'message' => 'Status berhasil diubah']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem.']);
        }
    }
}
