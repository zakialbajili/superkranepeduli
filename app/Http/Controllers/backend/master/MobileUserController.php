<?php

namespace App\Http\Controllers\backend\master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class MobileUserController extends Controller
{
    public function index()
    {
        $headerparam = [
            'headertag' => 'User Mobile',
            'headername' => 'Daftar User Mobile',
            'headerlink' => '#',
            'parentname' => 'Halaman Utama',
            'parentlink' => '#',
        ];

        return view('backend.master.admin.user.index', compact('headerparam'));
    }

    public function show(string $id)
    {
        $userid = decrypt($id);

        $datauser = DB::table('thseusermobile')
            ->where('pk_user_id', $userid)
            ->first();

        if (!$datauser) {
            toastr('Data tidak ditemukan', 'error');
            return redirect()->back();
        }

        $headerparam = [
            'headertag' => 'User Mobile',
            'headername' => 'Lihat User Mobile',
            'headerlink' => route('admin.user-mobile.index'),
            'parentname' => 'Halaman Utama',
            'parentlink' => route('admin.user-mobile.index'),
        ];

        return view('backend.master.admin.user.show', compact('headerparam', 'datauser'));
    }

    public function edit(string $id)
    {
        $userid = decrypt($id);

        $datauser = DB::table('thseusermobile')
            ->where('pk_user_id', $userid)
            ->first();

        if (!$datauser) {
            toastr('Data tidak ditemukan', 'error');
            return redirect()->back();
        }

        $headerparam = [
            'headertag' => 'User Mobile',
            'headername' => 'Edit User Mobile',
            'headerlink' => route('admin.user-mobile.index'),
            'parentname' => 'Halaman Utama',
            'parentlink' => route('admin.user-mobile.index'),
        ];

        return view('backend.master.admin.user.edit', compact('headerparam', 'datauser'));
    }

    public function update(Request $request, string $id)
    {
        $userid = decrypt($id);

        $request->validate([
            'employee_no' => ['required', 'string'],
            'full_name' => ['required', 'string'],
            'posisi' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'active' => ['required'],
        ], [
            'employee_no.required' => 'Employee No harus diisi',
            'full_name.required' => 'Nama harus diisi',
        ]);

        try {
            DB::table('thseusermobile')
                ->where('pk_user_id', $userid)
                ->update([
                    'employee_no' => $request->employee_no,
                    'full_name' => $request->full_name,
                    'posisi' => $request->posisi,
                    'birth_date' => $request->birth_date ? date('Y-m-d', strtotime($request->birth_date)) : null,
                    'active' => boolval($request->active),
                    'updated_date' => now(),
                ]);

            toastr('Data Telah Tersimpan', 'success', 'Sukses');
            return redirect()->route('admin.user-mobile.index');
        } catch (\Throwable $th) {
            toastr('Data Gagal Disimpan', 'error', 'Gagal');
            return back();
        }
    }

    public function changestatus(Request $request)
    {
        $userid = decrypt($request->id);

        try {
            DB::table('thseusermobile')
                ->where('pk_user_id', $userid)
                ->update(['active' => $request->status == 'true' ? 1 : 0]);

            return response(['status' => 'success', 'message' => 'Status Berhasil Diupdate!']);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'message' => 'Update Status Gagal!']);
        }
    }

    public function datatables(Request $request)
    {
        $columns = ['employee_no', 'full_name', 'posisi', 'login_last', 'active'];
        $columnkey = 'pk_user_id';
        $table = 'thseusermobile';
        $order = [$columns[0], 'asc'];

        $selectColumn = $columns;
        $selectColumn[] = $columnkey;

        if (isset($request["order"])) {
            $order = [$columns[$request['order']['0']['column']], $request['order']['0']['dir']];
        }

        $queryBuilder = DB::table($table)->select($selectColumn);
        $alldata = (clone $queryBuilder)->count();

        if (!empty($request['search']['value'])) {
            $queryBuilder->where(function ($query) use ($columns, $request) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
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
            $id = encrypt($data->$columnkey);
            $subdata = [];
            foreach ($columns as $column) {
                if ($column == 'active') {
                    $subdata[] = '<label class="switch">
                        <input type="checkbox" name="status" data-id="' . $id . '" class="change-status" ' . ($data->active == 1 ? "checked" : '') . '>
                        <span class="slider round"></span>
                    </label>';
                } elseif ($column == 'login_last') {
                    $subdata[] = $data->login_last ?? '-';
                } else {
                    $subdata[] = $data->$column;
                }
            }
            $view = "<a href='" . route('admin.user-mobile.show', $id) . "' class='btn btn-sm btn-primary ml-2'><i class='fas fa-file-alt'></i></a>";
            $edit = "<a href='" . route('admin.user-mobile.edit', $id) . "' class='btn btn-sm btn-success ml-2'><i class='fas fa-edit'></i></a>";
            $subdata[] = $view . $edit;
            $dataresult[] = $subdata;
        }

        return response()->json([
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult
        ], 200);
    }
}
