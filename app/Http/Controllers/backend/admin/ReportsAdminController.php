<?php

namespace App\Http\Controllers\backend\admin;

use App\Http\Controllers\Controller;
use App\Traits\ModuleTraits;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use DB;

class ReportsAdminController extends Controller
{
    use ModuleTraits, FileUploadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
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

        return view('backend.master.admin.reports.index', compact('headerparam', 'dataShift', 'dataKategori', 'dataStatus', 'dataDataPelaporan', 'dataLokasi', 'dataDepartemen'));
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
            'lokasi_m.name AS lokasi_bahaya',
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
                    $query->where('t.shift', decryptId($datafilter["shift"]));
                }
                if (!empty($datafilter["kategori_bahaya"])) {
                    $query->where('t.kategori_bahaya', decryptId($datafilter["kategori_bahaya"]));
                }
                if (!empty($datafilter["status_pelaporan"])) {
                    $query->where('t.status_pelaporan', decryptId($datafilter["status_pelaporan"]));
                }
                if (!empty($datafilter["data_pelaporan"])) {
                    $query->where('t.data_pelaporan', decryptId($datafilter["data_pelaporan"]));
                }
                if (!empty($datafilter["lokasi_bahaya"])) {
                    $query->where('t.lokasi_bahaya', decryptId($datafilter["lokasi_bahaya"]));
                }
                if (!empty($datafilter["dept_penanggungjwb"])) {
                    $query->where('t.dept_penanggungjwb', decryptId($datafilter["dept_penanggungjwb"]));
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
            'dataDepartemen'
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

        try {
            $dataform = $request->data['dataform'][0];

            DB::beginTransaction();

            $updateData = [
                'tgl_pelaporan'          => $dataform['tgl_pelaporan'] ?? null,
                'lokasi_bahaya'          => !empty($dataform['lokasi_bahaya']) ? decryptId($dataform['lokasi_bahaya']) : null,
                'shift'                  => !empty($dataform['shift']) ? decryptId($dataform['shift']) : null,
                'data_pelaporan'         => !empty($dataform['data_pelaporan']) ? decryptId($dataform['data_pelaporan']) : null,
                'kategori_bahaya'        => !empty($dataform['kategori_bahaya']) ? decryptId($dataform['kategori_bahaya']) : null,
                'desc_kategori_bahaya'   => !empty($dataform['desc_kategori_bahaya']) ? decryptId($dataform['desc_kategori_bahaya']) : null,
                'desc_temuan_bahaya'     => $dataform['desc_temuan_bahaya'] ?? null,
                'rekomendasi_perbaikan'  => $dataform['rekomendasi_perbaikan'] ?? null,
                'employee_no'            => $dataform['employee_no'] ?? null,
                'full_name'              => $dataform['full_name'] ?? null,
                'posisi'                 => $dataform['posisi'] ?? null,
                'dept_penanggungjwb'     => !empty($dataform['dept_penanggungjwb']) ? decryptId($dataform['dept_penanggungjwb']) : null,
                'nama_pengawas'          => $dataform['nama_pengawas'] ?? null,
                'due_date'               => $dataform['due_date'] ?? null,
                'status_pelaporan'       => !empty($dataform['status_pelaporan']) ? decryptId($dataform['status_pelaporan']) : null,
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
                'lokasi_m.name AS lokasi_bahaya_name',
                'dept_m.name AS dept_penanggungjwb_name',
                'jenis_bahaya_m.name AS desc_kategori_bahaya',
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
}