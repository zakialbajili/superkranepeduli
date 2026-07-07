<?php

namespace App\Http\Controllers\backend\master;

use App\DataTables\RoleDatatables;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Traits\ModuleTraits;
use App\Traits\StatusTraits;
use Illuminate\Support\Facades\Auth;


use DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(RoleDatatables $dataTable)
    {
        $headertag = 'Role';
        $headername = 'Daftar Role';
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

        return $dataTable->render('backend.master.roles.index', compact('headerparam'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $headertag = 'Role';
        $headername = 'Tambah Role';
        $headerlink = Route('admin.roles.index');
        $parentname = 'Halaman Utama';
        $parentlink = Route('admin.roles.index');

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];
        return view('backend.master.roles.add', compact('headerparam'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'data.dataform.*.rolename' => 'Nama Role dibutuhkan',
        ];
        $request->validate([
            'data.dataform1.*.rolename' => 'required',
        ], $messages);
        try {
            $dataform = $request->data['dataform'][0];
            DB::beginTransaction();
            $fullName = Auth::user()->name;
            $rolename = $dataform['rolename'];
            $roledesc = $dataform['roledesc'];

            $LogActivity = [];
            $LogActivity['NEW']['Role Header'] = [
                "name" => $rolename,
                "description" => $roledesc,
                "created_date" => now(),
                "created_by" => $fullName,
            ];
            $roleid = DB::table('trole2')
                ->insertGetId($LogActivity['NEW']['Role Header']);

            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties($LogActivity)
                ->log('Add - ' . Route::currentRouteName());
            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan',
                'url' => route('admin.roles.edit', encrypt($roleid))
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response([
                'status' => 'error ',
                'message' => 'Silahkan Hubungi Administrator.'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $pk_role_id = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }

        $headertag = 'Role';
        $headername = 'Edit Role';
        $headerlink = Route('admin.roles.index');
        $parentname = 'Halaman Utama';
        $parentlink = Route('admin.roles.index');

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        $rawRoleHeader = DB::table('trole2')
            ->where('pk_role_id', $pk_role_id)
            ->first();


        $routeName = Route::currentRouteName();
        $rawpengguna = DB::select("SELECT thseuser.name AS full_name FROM mpuser2role INNER JOIN thseuser ON thseuser.pk_user_id = mpuser2role.fk_user_id WHERE mpuser2role.fk_role_id = ?", [$pk_role_id]);
        $pengguna = '';
        if (count($rawpengguna) > 0) {
            foreach ($rawpengguna as $userdata) {
                $pengguna .= '<tr>
                    <td>' . $userdata->full_name . '</td>
                </tr>'; # code...
            }
        }
        return view(
            'backend.master.roles.edit',
            compact(
                'headerparam',
                'rawRoleHeader',
                'pengguna'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $roleid = decrypt($id);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'Data Gagal Diupdate, Data Role Tidak Sesuai!']);
        }
        $messages = [
            'data.dataform.*.rolename' => 'Nama Role dibutuhkan',
        ];
        $request->validate([
            'data.dataform1.*.rolename' => 'required',
        ], $messages);
        try {
            $dataform = $request->data['dataform'][0];
            DB::beginTransaction();
            $fullName = Auth::user()->name;
            $rolename = $dataform['rolename'];
            $roledesc = $dataform['roledesc'];
            $LogActivity = [];
            $LogActivity['NEW']['Role Header'] = [
                "name" => $rolename,
                "description" => $roledesc,
                "updated_date" => now(),
                "updated_by" => $fullName,
            ];
            DB::table('trole2')
                ->where('pk_role_id', $roleid)
                ->update($LogActivity['NEW']['Role Header']);
            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties($LogActivity)
                ->log('Update - ' . Route::currentRouteName());
            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan',
                'url' => route('admin.roles.edit', encrypt($roleid))
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response([
                'status' => 'error ',
                'message' => 'Silahkan Hubungi Administrator.'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $roleid = 0;
        try {
            $roleid = decrypt($id);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'Data Gagal Dihapus, Data Role Tidak Sesuai!']);
        }

        try {
            DB::beginTransaction();
            $detailRole = DB::table('trole2')->where('pk_role_id', $roleid)->first();

            // Hapus relasi user-role terlebih dahulu
            DB::delete('delete from mpuser2role where fk_role_id = ?', [$roleid]);
            DB::delete('delete from trole2 where pk_role_id = ?', [$roleid]);
            $LogActivity = [];
            $LogActivity['Delete'] = $detailRole;

            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties($LogActivity)
                ->log('Delete - ' . Route::currentRouteName());
            DB::commit();
            return response(['status' => 'success', 'Data berhasil dihapus!']);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response(['status' => 'error', 'Silahkan hubungi Administrator.']);
        }
    }
}