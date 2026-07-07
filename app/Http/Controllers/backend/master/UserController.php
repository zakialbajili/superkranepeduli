<?php

namespace App\Http\Controllers\backend\master;

use App\DataTables\EmployeesDataTable;
use App\Http\Controllers\Controller;
use App\Models\GroupMenuModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserGroupMenuModel;
use App\Models\UserRoleModel;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $headertag = 'Pengguna';
        $headername = 'Daftar Pengguna';
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

        return view('backend.master.users.index', compact('headerparam'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $headertag = 'Pengguna';
        $headername = 'Tambah Pengguna';
        $headerlink = route('admin.users.index');
        $parentname = 'Halaman Utama';
        $parentlink = route('admin.users.index');

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        $datarole = RoleModel::where('active', 1)
            ->orderBy('name')
            ->get();
        $datagroupmenu = GroupMenuModel::where('active', 1)
            ->select(['pk_groupmenu_id', 'name'])
            ->orderBy('name')
            ->get();

        $datamodule = DB::table('tmodule2')
            ->select('pk_module_id', 'name')
            ->orderBy('name')
            ->get();

        return view('backend.master.users.add', compact('headerparam', 'datarole', 'datagroupmenu', 'datamodule'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'emp_no' => ['required', 'string'],
            'groupdata' => ['required', 'string'],
            'default_page' => ['required'],
            'roledata' => ['required', 'string'],
            'password' => 'required|confirmed|min:6|max:20',
        ], [
            'emp_no.required' => 'NIK Karyawan harus diisi dengan benar',
            'default_page.required' => 'Halaman Utama harus diisi dengan benar',
            'emp_no.string' => 'NIK Karyawan harus diisi dengan benar',
            'password.string' => 'Nama Karyawan harus diisi dengan benar',
            'password.min' => 'Password Min 6 Karakter',
            'password.max' => 'Password Max 6 Karakter',
            'password.required' => 'Password harus diisi dengan benar',
            'password.confirmed' => 'Password tidak sesuai',
            'key.rules' => 'your messages'
        ]);

        try {
            DB::beginTransaction();
            $LogActivity = [];
            $LogActivity['NEW']['USER'] = [
                'full_name' => $request->emp_name,
                'username' => $request->emp_no,
                'fk_module_id' => decryptForNumber($request->default_page),
            ];
            $isactive = boolval($request->status);
            $userid = DB::table('thseuser')->insertGetId(
                [
                    'name' => $request->emp_name,
                    'employee_no' => $request->emp_no,
                    'fk_module_id' => decryptForNumber($request->default_page),
                    'password' => bcrypt($request->password),
                    'active' => $isactive,
                ]
            );

            foreach (json_decode($request->groupdata, true)['value'] as $item) {
                $LogActivity['NEW']['User Group Menu'][] = [
                    'fk_groupmenu_id' => decrypt($item['id']),
                    'fk_user_id' => $userid,
                ];
                $usergroupmenu = new UserGroupMenuModel();
                $usergroupmenu->fk_groupmenu_id = decrypt($item['id']);
                $usergroupmenu->fk_user_id = $userid;
                $usergroupmenu->save();
            }

            foreach (json_decode($request->roledata, true)['value'] as $item) {
                $LogActivity['NEW']['Role'][] = [
                    'fk_role_id' => decrypt($item['id']),
                    'fk_user_id' => $userid,
                ];
                $userrole = new UserRoleModel();
                $userrole->fk_role_id = decrypt($item['id']);
                $userrole->fk_user_id = $userid;
                $userrole->save();
            }
            DB::commit();
            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties($LogActivity)
                ->log('Add - ' . Route::currentRouteName());

            toastr('Data Telah Tersimpan', 'success', 'Sukses');

            return redirect()->route('admin.users.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            toastr('Data Gagal Disimpan', 'error', 'Gagal');
            return back();
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $userid = 0;
        try {
            $userid = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }

        $headertag = 'Pengguna';
        $headername = 'Lihat Pengguna';
        $headerlink = route('admin.users.index');
        $parentname = 'Halaman Utama';
        $parentlink = route('admin.users.index');

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        $datarole = RoleModel::where('active', 1)
            ->orderBy('name')
            ->get();
        $datagroupmenu = GroupMenuModel::where('active', 1)
            ->orderBy('name')
            ->get();
        $datauser = DB::table('thseuser')
            ->select(['pk_user_id', 'name', 'active', 'employee_no'])
            ->first();

        $datauserrole = DB::table('mpuser2role')
            ->where('fk_user_id', $userid)
            ->join('trole2', 'mpuser2role.fk_role_id', '=', 'trole2.pk_role_id')
            ->get();
        $datausergroupmenu = DB::table('mpuser2groupmenu')
            ->join('tgroupmenu2', 'mpuser2groupmenu.fk_groupmenu_id', '=', 'tgroupmenu2.pk_groupmenu_id')
            ->where('fk_user_id', $userid)
            ->get();

        return view('backend.master.users.view', compact('headerparam', 'datarole', 'datagroupmenu', 'datauser', 'datauserrole', 'datausergroupmenu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $userid = 0;
        try {
            $userid = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }

        $headertag = 'Pengguna';
        $headername = 'Edit Pengguna';
        $headerlink = route('admin.users.index');
        $parentname = 'Halaman Utama';
        $parentlink = route('admin.users.index');

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        $datarole = RoleModel::where('active', 1)
            ->orderBy('name')
            ->get();
        $datagroupmenu = GroupMenuModel::where('active', 1)
            ->orderBy('name')
            ->get();
        $datauser = DB::table('thseuser')
            ->select(['pk_user_id', 'name', 'employee_no', 'active', 'fk_module_id'])
            ->where('pk_user_id', $userid)
            ->first();

        $datauserrole = DB::table('mpuser2role')
            ->where('fk_user_id', $userid)
            ->join('trole2', 'mpuser2role.fk_role_id', '=', 'trole2.pk_role_id')
            ->get();
        $datausergroupmenu = DB::table('mpuser2groupmenu')
            ->join('tgroupmenu2', 'mpuser2groupmenu.fk_groupmenu_id', '=', 'tgroupmenu2.pk_groupmenu_id')
            ->where('fk_user_id', $userid)
            ->get();

        $datamodule = DB::table('tmodule2')
            ->select('pk_module_id', 'name')
            ->orderBy('name')
            ->get();

        return view('backend.master.users.edit', compact('headerparam', 'datarole', 'datagroupmenu', 'datauser', 'datauserrole', 'datausergroupmenu', 'datamodule'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $userid = 0;
        try {
            $userid = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }
        $request->validate([
            'emp_no' => ['required', 'string'],
            'groupdata' => ['required', 'string'],
            'default_page' => ['required'],
            'roledata' => ['required', 'string'],
            'password' => 'confirmed|max:20',
        ], [
            'emp_no.required' => 'NIK Karyawan harus diisi dengan benar',
            'default_page.required' => 'Halaman Utama harus diisi dengan benar',
            'emp_no.string' => 'NIK Karyawan harus diisi dengan benar',
            'password.string' => 'Nama Karyawan harus diisi dengan benar',
            'password.min' => 'Password Min 6 Karakter',
            'password.max' => 'Password Max 6 Karakter',
            'password.required' => 'Password harus diisi dengan benar',
            'password.confirmed' => 'Password tidak sesuai',
            'key.rules' => 'your messages'
        ]);

        try {
            DB::beginTransaction();
            $LogActivity = [];
            $user = UserModel::find($userid);
            if ($request->password != null) {
                $user->password = bcrypt($request->password);
            }
            $user->employee_no = $request->emp_no;
            $user->name = $request->emp_name;
            $user->active = $request->status;
            $user->fk_module_id = decryptForNumber($request->default_page);
            $user->save();

            $LogActivity['NEW']['USER'] = UserModel::where('pk_user_id', $userid)->select('pk_user_id', 'name')->first();

            $listidusergroupmenu = [];

            $LogActivity['OLD']['User Group Menu'][] = userGroupMenuModel::where('fk_user_id', $id)->get();
            foreach (json_decode($request->groupdata, true)['value'] as $item) {
                try {
                    $usergroupmenuid = decrypt($item['unique']);
                } catch (\Throwable $th) {
                    $usergroupmenuid = 0;
                }

                $usergroupmenu = userGroupMenuModel::where('fk_user_id', $id)
                    ->where('pk_usergroupmenu_id', $usergroupmenuid)->count();
                //simpan jika tidak ada pada database namun ada di table
                if ($usergroupmenu == 0) {
                    $usergroupmenuid = DB::table('mpuser2groupmenu')->insertGetId(
                        [
                            'fk_groupmenu_id' => decrypt($item['id']),
                            'fk_user_id' => $userid,
                        ]
                    );
                }

                $listidusergroupmenu[] = $usergroupmenuid;
            }
            //hapus jika ada diserver namun tidak ada di table;
            userGroupMenuModel::where('fk_user_id', $userid)
                ->whereNotIn('pk_usergroupmenu_id', $listidusergroupmenu)->delete();
            $LogActivity['NEW']['User Group Menu'][] = userGroupMenuModel::where('fk_user_id', $id)->get();

            $listiduserrole = [];

            $LogActivity['OLD']['Role'][] = UserRoleModel::where('fk_user_id', $userid)->get();
            foreach (json_decode($request->roledata, true)['value'] as $item) {
                try {
                    $userroleid = decrypt($item['unique']);
                } catch (\Throwable $th) {
                    $userroleid = 0;
                }
                $userrole = UserRoleModel::where('fk_user_id', $userid)
                    ->where('pk_userrole_id', $userroleid)->count();
                //simpan jika tidak ada pada database namun ada di table
                if ($userrole == 0) {
                    $userroleid = DB::table('mpuser2role')->insertGetId(
                        [
                            'fk_role_id' => decrypt($item['id']),
                            'fk_user_id' => $userid,
                        ]
                    );
                }

                $listiduserrole[] = $userroleid;
            }
            //hapus jika ada diserver namun tidak ada di table;
            if (count($listiduserrole) > 0) {
                UserRoleModel::where('fk_user_id', $userid)
                    ->whereNotIn('pk_userrole_id', $listiduserrole)->delete();
            }
            $LogActivity['NEW']['Role'][] = UserRoleModel::where('fk_user_id', $userid)->get();

            DB::commit();

            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties($LogActivity)
                ->log('Update - ' . Route::currentRouteName());

            toastr('Data Telah Tersimpan', 'success', 'Sukses');

            return redirect()->route('admin.users.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            toastr('Data Gagal Disimpan', 'error', 'Gagal');
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $userid = 0;
        try {
            $userid = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }
        // if ($subCategory > 0) {
        //     return response(['status' => 'error', 'message' => 'This items contain, sub items for delete this you have to delete the sub items first!']);
        // }
        // $category->delete();
        try {
            $LogActivity = [];
            DB::beginTransaction();
            $LogActivity['OLD']['User'] = UserModel::where('pk_user_id', $userid)->select('pk_user_id', 'name')->get();
            $LogActivity['OLD']['Role'] = UserRoleModel::where('fk_user_id', $userid)->get();
            $LogActivity['OLD']['Group Menu'] = UserGroupMenuModel::where('fk_user_id', $userid)->get();

            DB::delete('delete from mpuser2role where fk_user_id = ?', [$userid]);
            DB::delete('delete from mpuser2groupmenu where fk_user_id = ?', [$userid]);
            DB::delete('delete from thseuser where pk_user_id = ?', [$userid]);

            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties($LogActivity)
                ->log('Delete - ' . Route::currentRouteName());
            DB::commit();

            return response(['status' => 'success', 'Data Berhasil Dihapus!']);
        } catch (\Throwable $th) {
            toastr('Data Gagal Dihapus', 'error', 'Gagal');
            return redirect()->back();
        }
    }
    public function changestatus(Request $request)
    {
        $userid = 0;
        try {
            $userid = decrypt($request->id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }
        try {
            DB::beginTransaction();
            DB::update('update thseuser set active = ? where pk_user_id = ?', [$request->status == 'true' ? 1 : 0, $userid]);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Status Berhasil Diupdate!']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(['status' => 'error', 'message' => 'Update Status Gagal Silahkan hub Administrator!']);
        }

    }

    public function addrole(Request $request)
    {

        $request->session()->push($request->keys, $request->id);

        return response(['message' => 'Role Berhasil Diupdate!']);
    }

    public function datatables(Request $request)
    {
        // <<<<<<<<<<<<<< START untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        $columns = [
            'name',
            'login_last',
            'active'
        ];
        $columnkey = 'pk_user_id';
        $table = 'thseuser';
        $order = [$columns[0], 'asc'];

        $selectColumn = $columns;
        $selectColumn[] = $columnkey;
        if (isset($request["order"])) {
            $order = [$columns[$request['order']['0']['column']], $request['order']['0']['dir']];
        }

        $queryBuilder = DB::table($table)
            ->select($selectColumn);
        $alldata = (clone $queryBuilder)->count();
        if (!empty($request['search']['value'])) {
            $queryBuilder->where(function ($query) use ($columns, $request) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                };
            });
        }
        $filteredrecordcount = (clone $queryBuilder)->count();
        $datas = $queryBuilder->orderBy($order[0], $order[1])
            ->skip($request['start'])
            ->take($request['length'])
            ->get();

        $dataresult = [];
        foreach ($datas as $data) {
            //ubah dibagian ini untuk membuat raw html
            $id = encrypt($data->$columnkey);
            $subdata = [];
            foreach ($columns as $column) {
                if ($column == 'active') {
                    $subdata[] = '<label class="switch">
                        <input type="checkbox" id="status" name="status"  data-id="' . $id . '" class="change-status" ' . ($data->active == 1 ? "checked" : '') . '>
                        <span class="slider round"></span>
                    </label>';
                } else {
                    $subdata[] = $data->$column;
                }
            }
            $view = "<a href='" . route('admin.users.show', $id) . "' class='btn btn-sm btn-primary ml-2'><i class='fas fa-file-alt'></i></button>";
            $edit = "<a href='" . route('admin.users.edit', $id) . "' class='btn btn-sm btn-success ml-2'><i class='fas fa-edit'></i></button>";
            $delete = "<a href='" . route('admin.users.destroy', $id) . "' class='btn btn-sm btn-danger ml-2 delete-item'><i class='fa fa-trash'></i></a>";
            $subdata[] = $view . $edit . $delete;
            $dataresult[] = $subdata;
        }

        $output = array(
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult
        );
        // <<<<<<<<<<<<<< END untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

        return response()->json($output, 200);
    }

    public function Karyawandatatables(Request $request)
    {
        // <<<<<<<<<<<<<< START untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        $columns = ['employee_no', 'full_name', 'phone_1', 'phone_2'];
        $columnkey = 'pk_employee_id';
        $table = 'temployee';
        $order = [$columns[0], 'asc'];

        $selectColumn = $columns;
        $selectColumn[] = $columnkey;
        if (isset($request["order"])) {
            $order = [$columns[$request['order']['0']['column']], $request['order']['0']['dir']];
        }

        if ($request['length'] != 1) {
            $datas = DB::table($table)
                ->select($selectColumn)
                ->orWhere(function ($query) use ($columns, $request) {
                    foreach ($columns as $column) {
                        $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                    };
                })
                ->whereNotIn('pk_employee_id', UserModel::select(['fk_employee_id']))
                ->orderBy($order[0], $order[1])
                ->skip($request['start'])
                ->take($request['length'])
                ->get();
        } else {
            $datas = DB::table($table)
                ->select($selectColumn)
                ->orWhere(function ($query) use ($columns, $request) {
                    foreach ($columns as $column) {
                        $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                    };
                })
                ->whereNotIn('pk_employee_id', UserModel::select(['fk_employee_id']))
                ->orderBy($order[0], $order[1])
                ->get();
        }

        $filteredrecordcount = DB::table($table)
            ->select($columns)
            ->orWhere(function ($query) use ($columns, $request) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                };
            })
            ->whereNotIn('pk_employee_id', UserModel::select(['fk_employee_id']))
            ->orderBy($order[0], $order[1])
            ->count();

        $dataresult = [];
        foreach ($datas as $data) {
            //ubah dibagian ini untuk membuat raw html
            $id = $data->$columnkey;
            $subdata = [];
            foreach ($columns as $column) {
                $subdata[] = $data->$column;
            }
            $select = "<button data-id='$id' class='btn btn-sm btn-danger ml-2 selected-item'><i class='fa fa-check'></i></button>";
            $subdata[] = $select;
            $dataresult[] = $subdata;
        }

        //ambil total data
        $alldata = DB::table($table)->count();
        $output = array(
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult
        );
        // <<<<<<<<<<<<<< END untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

        return response()->json($output, 200);
    }

    public function storeToken(Request $request)
    {
        try {
            $user_id = decrypt($request->id);
            DB::table('tuser2')->where('pk_user_id', $user_id)->update([
                'web_fcm_token' => $request->token,
            ]);

            return response()->json(['Token successfully stored.']);
        } catch (\Throwable $th) {
            //throw $th;
        }

    }
}