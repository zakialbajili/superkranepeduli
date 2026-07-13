<?php

namespace App\Http\Controllers\backend\admin;

use App\Http\Controllers\Controller;
use App\Jobs\ExportExcelReportsHSE;
use App\Traits\ModuleTraits;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ReportsAdminController extends Controller
{
    use ModuleTraits, FileUploadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $headertag = 'Pelaporan Bahaya';
        $headername = 'Daftar Pelaporan Bahaya';
        $headerlink = '#';
        $parentname = 'Halaman Utama';
        $parentlink = '#';

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        $dataShift = $this->generateselect('thsedata_master', 'pk_hsedatamaster_id', 'name', ['type' => 'Type Shift']);
        $dataKategori = $this->generateselect('thsedata_master', 'pk_hsedatamaster_id', 'name', ['type' => 'Kategori Bahaya']);
        $dataStatus = $this->generateselect('thsedata_master', 'pk_hsedatamaster_id', 'name', ['type' => 'Status Laporan']);
        $dataDataPelaporan = $this->generateselect('thsedata_master', 'pk_hsedatamaster_id', 'name', ['type' => 'Data Pelaporan']);
        $dataLokasi = $this->generateselect('thsedata_master', 'pk_hsedatamaster_id', 'name', ['type' => 'Lokasi']);
        $dataDepartemen = $this->generateselect('thsedata_master', 'pk_hsedatamaster_id', 'name', ['type' => 'Departemen']);

        $filterStatus = $request->get('filter_status', '');
        $filterKategori = $request->get('filter_kategori', '');
        $filterTanggal = $request->get('filter_tanggal', '');

        return view('backend.master.admin.reports.index', compact(
            'headerparam',
            'dataShift',
            'dataKategori',
            'dataStatus',
            'dataDataPelaporan',
            'dataLokasi',
            'dataDepartemen',
            'filterStatus',
            'filterKategori',
            'filterTanggal'
        ));
    }

    public function datatable(Request $request)
    {
        $datafilter = [];
        if (isset($request['data'][0])) {
            $datafilter = $request['data'][0];
        }

        $columns = [
            'employee_no',
            'full_name',
            'posisi',
            'tgl_pelaporan',
            'lokasi_bahaya',
            'shift',
            'data_pelaporan',
            'kategori_bahaya',
            'status_pelaporan',
        ];
        $table = 'thsepelaporanbahaya';
        $columnkey = 'pk_hsepelaporanbahaya_id';
        $selectColumn = [
            "t.$columnkey",
            't.employee_no',
            't.full_name',
            't.posisi',
            't.tgl_pelaporan',
            DB::raw("COALESCE(lokasi_m.name, t.lokasi_bahaya) AS lokasi_bahaya"),
            'shift_m.name AS shift',
            'data_m.name AS data_pelaporan',
            'kat_m.name AS kategori_bahaya',
            'status_m.name AS status_pelaporan',
        ];
        $searchColumn = [
            't.employee_no',
            't.full_name',
            't.posisi',
            't.tgl_pelaporan',
            'lokasi_m.name',
            'shift_m.name',
            'data_m.name',
            'kat_m.name',
            'status_m.name',
        ];
        $order = [$columns[0], 'desc'];

        if (isset($request["order"])) {
            $order = [$columns[$request['order']['0']['column']], $request['order']['0']['dir']];
        }

        $queryBuilder = DB::table("$table AS t")
            ->select($selectColumn)
            ->leftJoin('thsedata_master AS shift_m','t.shift' ,'=', 'shift_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS data_m','t.data_pelaporan' ,'=', 'data_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS kat_m','t.kategori_bahaya' ,'=', 'kat_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS status_m','t.status_pelaporan' ,'=', 'status_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS lokasi_m','t.lokasi_bahaya' ,'=', 'lokasi_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS dept_m','t.dept_penanggungjwb' ,'=', 'dept_m.pk_hsedatamaster_id');

        $alldata = (clone $queryBuilder)->count();

        if (!empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $queryBuilder->where(function ($query) use ($searchColumn, $request, $searchValue) {
                foreach ($searchColumn as $column) {
                    $query->orWhere($column, 'like', "%" . $searchValue . "%");
                };
            });
        }

        if (!empty($datafilter)) {
            $queryBuilder->where(function ($query) use ($datafilter) {
                if (!empty($datafilter["tgl_pelaporan"])) {
                    $datadate = explode(' - ', $datafilter["tgl_pelaporan"]);
                    if (count($datadate) > 0) {
                        $query->where('t.tgl_pelaporan', ">=", $datadate[0]);
                        $query->where('t.tgl_pelaporan', "<=", $datadate[1]);
                    }
                }
                if (!empty($datafilter["shift"])) {
                    $query->where('t.shift', decryptForNumber($datafilter["shift"]));
                }
                if (!empty($datafilter["kategori_bahaya"])) {
                    $query->where('t.kategori_bahaya', decryptForNumber($datafilter["kategori_bahaya"]));
                }
                if (!empty($datafilter["status_pelaporan"])) {
                    $query->where('t.status_pelaporan', decryptForNumber($datafilter["status_pelaporan"]));
                }
                if (!empty($datafilter["data_pelaporan"])) {
                    $query->where('t.data_pelaporan', decryptForNumber($datafilter["data_pelaporan"]));
                }
                if (!empty($datafilter["lokasi_bahaya"])) {
                    $query->where('t.lokasi_bahaya', decryptForNumber($datafilter["lokasi_bahaya"]));
                }
                if (!empty($datafilter["dept_penanggungjwb"])) {
                    $query->where('t.dept_penanggungjwb', decryptForNumber($datafilter["dept_penanggungjwb"]));
                }
            });
        }

        $filteredrecordcount = (clone $queryBuilder)->count();
        $datas = $queryBuilder->orderBy($order[0], $order[1])
            ->skip($request['start'])
            ->take($request['length'])
            ->get();

        $dataresult = [];
        foreach ($datas as $data) {
            $subdata = [];
            foreach ($columns as $column) {
                $subdata[] = $data->$column;
            }
            $id = encrypt($data->$columnkey);
            $edit = '<a href="' . route('admin.reports.edit', $id) . '" class="btn btn-sm btn-success mr-2"><i class="fas fa-edit"></i></a>';
            $detail = '<a href="' . route('admin.reports.show', $id) . '" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></a>';
            $subdata[] = $edit . $detail;
            $dataresult[] = $subdata;
        }

        $output = array(
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult
        );

        return response()->json($output, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $pk_id = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }

        $headertag = 'Pelaporan Bahaya';
        $headername = 'Edit Pelaporan Bahaya';
        $headerlink = route('admin.reports.index');
        $parentname = 'Daftar Pelaporan Bahaya';
        $parentlink = route('admin.reports.index');

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        $report = DB::table('thsepelaporanbahaya')
            ->where('pk_hsepelaporanbahaya_id', $pk_id)
            ->first();

        if (!$report) {
            toastr('Data tidak ditemukan', 'error');
            return redirect()->back();
        }

        $dataShift = $this->generateselect(
            'thsedata_master', 'pk_hsedatamaster_id', 'name',
            ['type' => 'Type Shift'],
            $report->shift
        );
        $dataDataPelaporan = $this->generateselect(
            'thsedata_master', 'pk_hsedatamaster_id', 'name',
            ['type' => 'Data Pelaporan'],
            $report->data_pelaporan
        );
        $dataKategori = $this->generateselect(
            'thsedata_master', 'pk_hsedatamaster_id', 'name',
            ['type' => 'Kategori Bahaya'],
            $report->kategori_bahaya
        );
        $dataStatus = $this->generateselect(
            'thsedata_master', 'pk_hsedatamaster_id', 'name',
            ['type' => 'Status Laporan'],
            $report->status_pelaporan
        );

        // Untuk desc_kategori_bahaya (jenis detail) — buat semua opsi
        // Filter JS akan menentukan mana yang tampil berdasarkan kategori_bahaya
        $dataJenisKondisi = $this->generateselect(
            'thsedata_master', 'pk_hsedatamaster_id', 'name',
            ['type' => 'Jenis Kondisi Tidak Aman'],
            $report->desc_kategori_bahaya
        );
        $dataJenisTindakan = $this->generateselect(
            'thsedata_master', 'pk_hsedatamaster_id', 'name',
            ['type' => 'Jenis Tindakan Tidak Aman'],
            $report->desc_kategori_bahaya
        );

        $dataLokasi = $this->generateselect(
            'thsedata_master', 'pk_hsedatamaster_id', 'name',
            ['type' => 'Lokasi'],
            $report->lokasi_bahaya
        );
        $dataDepartemen = $this->generateselect(
            'thsedata_master', 'pk_hsedatamaster_id', 'name',
            ['type' => 'Departemen'],
            $report->dept_penanggungjwb
        );

        // Deteksi custom text: jika lokasi_bahaya / desc_kategori_bahaya bukan FK numerik
        // (yaitu teks mentah), flag untuk JS agar select ke "Lainnya..." dan input teks terisi
        $isLokasiCustom = !empty($report->lokasi_bahaya) && !is_numeric($report->lokasi_bahaya);
        $isJenisCustom = !empty($report->desc_kategori_bahaya) && !is_numeric($report->desc_kategori_bahaya);

        return view('backend.master.admin.reports.edit', compact(
            'headerparam',
            'report',
            'dataShift',
            'dataDataPelaporan',
            'dataKategori',
            'dataStatus',
            'dataJenisKondisi',
            'dataJenisTindakan',
            'dataLokasi',
            'dataDepartemen',
            'isLokasiCustom',
            'isJenisCustom'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $pk_id = decrypt($id);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'message' => 'Data Tidak Sesuai!']);
        }

        // Validasi dengan Laravel validate
        $request->validate([
            'data.dataform.0.tgl_pelaporan'         => 'required|date',
            'data.dataform.0.lokasi_bahaya'          => 'required',
            'data.dataform.0.shift'                  => 'required',
            'data.dataform.0.data_pelaporan'         => 'required',
            'data.dataform.0.posisi'                 => 'required',
            'data.dataform.0.full_name'              => 'required',
            'data.dataform.0.employee_no'            => 'required',
            'data.dataform.0.desc_temuan_bahaya'     => 'required',
            'data.dataform.0.rekomendasi_perbaikan'  => 'required',
            'data.dataform.0.dept_penanggungjwb'     => 'required',
            'data.dataform.0.nama_pengawas'          => 'required',
            'data.dataform.0.due_date'               => 'required|date',
            'data.dataform.0.status_pelaporan'       => 'required',
            'data.dataform.0.document'               => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'data.dataform.0.tgl_pelaporan.required'        => 'Tanggal pelaporan wajib diisi',
            'data.dataform.0.tgl_pelaporan.date'            => 'Format tanggal pelaporan tidak valid',
            'data.dataform.0.lokasi_bahaya.required'         => 'Lokasi bahaya wajib dipilih',
            'data.dataform.0.shift.required'                 => 'Shift wajib dipilih',
            'data.dataform.0.data_pelaporan.required'        => 'Data pelaporan wajib dipilih',
            'data.dataform.0.posisi.required'                => 'Posisi / jabatan wajib diisi',
            'data.dataform.0.full_name.required'             => 'Nama lengkap wajib diisi',
            'data.dataform.0.employee_no.required'           => 'Nomor karyawan wajib diisi',
            'data.dataform.0.desc_temuan_bahaya.required'    => 'Deskripsi temuan bahaya wajib diisi',
            'data.dataform.0.rekomendasi_perbaikan.required' => 'Rekomendasi perbaikan wajib diisi',
            'data.dataform.0.dept_penanggungjwb.required'    => 'Departemen penanggung jawab wajib dipilih',
            'data.dataform.0.nama_pengawas.required'         => 'Nama pengawas wajib diisi',
            'data.dataform.0.due_date.required'              => 'Due date wajib diisi',
            'data.dataform.0.due_date.date'                  => 'Format due date tidak valid',
            'data.dataform.0.status_pelaporan.required'      => 'Status laporan wajib dipilih',
            'data.dataform.0.document.file'                  => 'File yang diupload tidak valid',
            'data.dataform.0.document.mimes'                 => 'Format file harus JPG, JPEG, PNG, atau PDF',
            'data.dataform.0.document.max'                   => 'Ukuran file tidak boleh lebih dari 5 MB',
        ]);

        try {
            $dataform = $request->data['dataform'][0];

            DB::beginTransaction();

            $updateData = [
                'tgl_pelaporan'          => $dataform['tgl_pelaporan'] ?? null,
                'lokasi_bahaya'          => ($dataform['lokasi_bahaya'] ?? null) === 'other'
                    ? ($dataform['lokasi_bahaya_other'] ?? null)
                    : (!empty($dataform['lokasi_bahaya']) ? decryptForNumber($dataform['lokasi_bahaya']) : null),
                'shift'                  => !empty($dataform['shift']) ? decryptForNumber($dataform['shift']) : null,
                'data_pelaporan'         => !empty($dataform['data_pelaporan']) ? decryptForNumber($dataform['data_pelaporan']) : null,
                'kategori_bahaya'        => !empty($dataform['kategori_bahaya']) ? decryptForNumber($dataform['kategori_bahaya']) : null,
                'desc_kategori_bahaya'   => ($dataform['desc_kategori_bahaya'] ?? null) === 'other'
                    ? ($dataform['desc_kategori_bahaya_other'] ?? null)
                    : (!empty($dataform['desc_kategori_bahaya']) ? decryptForNumber($dataform['desc_kategori_bahaya']) : null),
                'desc_temuan_bahaya'     => $dataform['desc_temuan_bahaya'] ?? null,
                'rekomendasi_perbaikan'  => $dataform['rekomendasi_perbaikan'] ?? null,
                'employee_no'            => $dataform['employee_no'] ?? null,
                'full_name'              => $dataform['full_name'] ?? null,
                'posisi'                 => $dataform['posisi'] ?? null,
                'dept_penanggungjwb'     => !empty($dataform['dept_penanggungjwb']) ? decryptForNumber($dataform['dept_penanggungjwb']) : null,
                'nama_pengawas'          => $dataform['nama_pengawas'] ?? null,
                'due_date'               => $dataform['due_date'] ?? null,
                'status_pelaporan'       => !empty($dataform['status_pelaporan']) ? decryptForNumber($dataform['status_pelaporan']) : null,
                'updated_date'           => now(),
                'updated_by'             => Auth::user()->name ?? '',
            ];

            // Handle document file upload
            if ($request->hasFile('data.dataform.0.document')) {
                $file = $request->file('data.dataform.0.document');
                $path = 'uploads/hse/documents';
                $fileName = 'doc_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path($path), $fileName);
                $updateData['document'] = $path . '/' . $fileName;
            }

            DB::table('thsepelaporanbahaya')
                ->where('pk_hsepelaporanbahaya_id', $pk_id)
                ->update($updateData);

            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties(['UPDATE' => $updateData])
                ->log('Update - ' . Route::currentRouteName());

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Data Berhasil Diupdate',
                'url' => route('admin.reports.edit', encrypt($pk_id))
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => 'Silahkan Hubungi Administrator.'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $pk_id = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }

        $headertag = 'Pelaporan Bahaya';
        $headername = 'Detail Pelaporan Bahaya';
        $headerlink = route('admin.reports.index');
        $parentname = 'Daftar Pelaporan Bahaya';
        $parentlink = route('admin.reports.index');

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        $report = DB::table('thsepelaporanbahaya AS t')
            ->select([
                't.*',
                'shift_m.name AS shift_name',
                'data_m.name AS data_pelaporan_name',
                'kat_m.name AS kategori_bahaya_name',
                'status_m.name AS status_pelaporan_name',
                DB::raw('COALESCE(lokasi_m.name, t.lokasi_bahaya) AS lokasi_bahaya_name'),
                'dept_m.name AS dept_penanggungjwb_name',
                DB::raw('COALESCE(jenis_bahaya_m.name, t.desc_kategori_bahaya) AS desc_kategori_bahaya'),
            ])
            ->leftJoin('thsedata_master AS shift_m', 't.shift', '=', 'shift_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS data_m', 't.data_pelaporan', '=', 'data_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS kat_m', 't.kategori_bahaya', '=', 'kat_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS status_m', 't.status_pelaporan', '=', 'status_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS lokasi_m', 't.lokasi_bahaya', '=', 'lokasi_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS dept_m', 't.dept_penanggungjwb', '=', 'dept_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS jenis_bahaya_m', 't.desc_kategori_bahaya', '=', 'jenis_bahaya_m.pk_hsedatamaster_id')
            ->where('t.pk_hsepelaporanbahaya_id', $pk_id)
            ->first();

        if (!$report) {
            toastr('Data tidak ditemukan', 'error');
            return redirect()->back();
        }

        return view('backend.master.admin.reports.view', compact('headerparam', 'report'));
    }
    public function exportexcel(Request $request)
    {
        try {
            $dataFilter = [
                "tgl_pelaporan" => "",
                "shift" => "",
                "data_pelaporan" => "",
                "kategori_bahaya" => "",
                "status_pelaporan" => ""
            ];
            if ($request->has('data') && isset($request['data'])) {
                $dataFilter = $request['data'][0];
            }
            ExportExcelReportsHSE::dispatch($dataFilter, $request->user());
        } catch (Exception $e) {
            Log::error("Export Excel Error: " . $e->getMessage());
        }
    }
}