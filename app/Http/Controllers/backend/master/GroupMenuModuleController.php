<?php

namespace App\Http\Controllers\backend\master;

use App\DataTables\GroupMenuModuleDataTable;
use App\Http\Controllers\Controller;
use App\Models\GroupMenu2ModuleModel;
use App\Models\GroupMenuModel;
use App\Models\ModuleModel;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class GroupMenuModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GroupMenuModuleDataTable $dataTable)
    {
        $headertag = 'Group Menu';
        $headername = 'Daftar Group Menu';
        $headerlink = '';
        $parentname = 'Halaman Utama';
        $parentlink = '';

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        return $dataTable->render('backend.master.groupmenumodules.index', compact('headerparam'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $headertag = 'Group Menu';
        $headername = 'Daftar Group Menu';
        $headerlink = '';
        $parentname = 'Halaman Utama';
        $parentlink = '';
        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        //generate menu access
        $rawModule = ModuleModel::orderby('name')->get();

        $html = "";
        foreach ($rawModule as $item) {
            $view = $item->url_view == null ? "" : '<input type="checkbox" name="vie-' . encryptId($item->pk_module_id) . '" />';
            $add = $item->url_add == null ? "" : '<input type="checkbox" name="add-' . encryptId($item->pk_module_id) . '" />';
            $update = $item->url_update == null ? "" : '<input type="checkbox" name="upd-' . encryptId($item->pk_module_id) . '" />';
            $delete = $item->url_delete == null ? "" : '<input type="checkbox" name="del-' . encryptId($item->pk_module_id) . '" />';
            $detail = $item->url_detail == null ? "" : '<input type="checkbox" name="det-' . encryptId($item->pk_module_id) . '" />';
            $approval = $item->url_approval == null ? "" : '<input type="checkbox" name="app-' . encryptId($item->pk_module_id) . '" />';
            $id = '<input type="hidden" name="modid-' . encryptId($item->pk_module_id) . '" value="' . encryptId($item->pk_module_id) . '" />';


            $html .= '<tr>
                            <td style="display:none">' . $id . '</td>
                            <td>' . $item->name . '</td>
                            <td>' . $view . '</td>
                            <td>' . $add . '</td>
                            <td>' . $update . '</td>
                            <td>' . $delete . '</td>
                            <td>' . $detail . '</td>
                            <td>' . $approval . '</td>
                        </tr>';

        }


        return view('backend.master.groupmenumodules.add', compact('headerparam', 'html'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->data;

        if ($data['groupname'] == null) {
            return response(['status' => 'error', 'message' => 'Nama Group harus diisi']);
        }
        // if ($data['groupdesc'] == null) {
        //     return response(['status' => 'error', 'message' => 'Deskripsi Group harus diisi']);
        // }


        try {
            DB::beginTransaction();
            $LogActivity = [];
            $LogActivity['NEW']['Group Menu 2'] = [
                "name" => $data['groupname'],
                "description" => $data['groupdesc'],
                "created_date" => now()
            ];
            $groupid = DB::table('tgroupmenu2')->insertGetId(
                $LogActivity['NEW']['Group Menu 2']
            );
            $LogActivityGroupMenu = [];
            foreach ($data['accessdata'] as $item) {
                $module_id = 0;
                $view = 0;
                $add = 0;
                $update = 0;
                $delete = 0;
                $detail = 0;
                $approval = 0;

                foreach ($item as $key => $value) {
                    if (substr($key, 0, 6) == "modid-") {
                        $module_id = decryptForNumber($value);
                    }
                    if (substr($key, 0, 4) == "vie-") {
                        $view = $value == "on" ? 1 : 0;
                    }
                    if (substr($key, 0, 4) == "add-") {
                        $add = $value == "on" ? 1 : 0;
                    }
                    if (substr($key, 0, 4) == "upd-") {
                        $update = $value == "on" ? 1 : 0;
                    }
                    if (substr($key, 0, 4) == "del-") {
                        $delete = $value == "on" ? 1 : 0;
                    }
                    if (substr($key, 0, 4) == "det-") {
                        $detail = $value == "on" ? 1 : 0;
                    }
                    if (substr($key, 0, 4) == "app-") {
                        $approval = $value == "on" ? 1 : 0;
                    }
                }

                if ($module_id == 0) {
                    return response(['status' => 'error', 'message' => 'Invalid Data']);
                }

                $LogActivityGroupMenu = [
                    "fk_groupmenu_id" => $groupid,
                    "fk_module_id" => $module_id,
                    "is_view" => $view,
                    "is_add" => $add,
                    "is_update" => $update,
                    "is_detail" => $detail,
                    "is_delete" => $delete,
                    "is_approval" => $approval,
                ];

                DB::table('mpgroupmenu2module')->insert(
                    $LogActivityGroupMenu
                );

                $LogActivity['NEW']['Group Menu 2 Module'][] = $LogActivityGroupMenu;
            }
            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties($LogActivity)
                ->log('Add - ' . Route::currentRouteName());

            DB::commit();
            return response(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(['status' => 'error', 'message' => $th->getMessage()]);
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
        $groupid = 0;
        try {
            $groupid = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }

        $headertag = 'Group Menu';
        $headername = 'Edit Group Menu';
        $headerlink = '';
        $parentname = 'Halaman Utama';
        $parentlink = '';
        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];


        $rawGroupMenu = GroupMenuModel::findOrFail($groupid);

        //generate menu access
        $rawModule = ModuleModel::orderby('name')->get();

        $html = "";
        foreach ($rawModule as $item) {
            //check apakah id module ada di mpgroupmenu2module
            $rawmpgroupmenu = GroupMenu2ModuleModel::where('fk_groupmenu_id', $groupid)
                ->where('fk_module_id', $item->pk_module_id);
            if ($rawmpgroupmenu->count() == 0) {
                $view = $item->url_view == null ? "" : '<input type="checkbox" name="vie-' . encryptId($item->pk_module_id) . '" />';
                $add = $item->url_add == null ? "" : '<input type="checkbox" name="add-' . encryptId($item->pk_module_id) . '" />';
                $update = $item->url_update == null ? "" : '<input type="checkbox" name="upd-' . encryptId($item->pk_module_id) . '" />';
                $delete = $item->url_delete == null ? "" : '<input type="checkbox" name="del-' . encryptId($item->pk_module_id) . '" />';
                $detail = $item->url_detail == null ? "" : '<input type="checkbox" name="det-' . encryptId($item->pk_module_id) . '" />';
                $approval = $item->url_approval == null ? "" : '<input type="checkbox" name="app-' . encryptId($item->pk_module_id) . '" />';
                $id = '<input type="hidden" name="modid-' . encryptId($item->pk_module_id) . '" value="' . encryptId($item->pk_module_id) . '" />';
                $groid = '<input type="hidden" name="groid-' . encryptId($item->pk_module_id) . '" value="0" />';
            } else {
                $groupitem = $rawmpgroupmenu->first();

                $viewchecked = $groupitem->is_view == 1 ? "checked" : "";
                $view = $item->url_view == null ? "" : '<input type="checkbox" name="vie-' . encryptId($item->pk_module_id) . '" ' . $viewchecked . ' />';

                $addchecked = $groupitem->is_add == 1 ? "checked" : "";
                $add = $item->url_add == null ? "" : '<input type="checkbox" name="add-' . encryptId($item->pk_module_id) . '" ' . $addchecked . ' />';

                $updatechecked = $groupitem->is_update == 1 ? "checked" : "";
                $update = $item->url_update == null ? "" : '<input type="checkbox" name="upd-' . encryptId($item->pk_module_id) . '" ' . $updatechecked . ' />';

                $deletechecked = $groupitem->is_delete == 1 ? "checked" : "";
                $delete = $item->url_delete == null ? "" : '<input type="checkbox" name="del-' . encryptId($item->pk_module_id) . '" ' . $deletechecked . ' />';

                $detailchecked = $groupitem->is_detail == 1 ? "checked" : "";
                $detail = $item->url_detail == null ? "" : '<input type="checkbox" name="det-' . encryptId($item->pk_module_id) . '" ' . $detailchecked . ' />';

                $approvalchecked = $groupitem->is_approval == 1 ? "checked" : "";
                $approval = $item->url_approval == null ? "" : '<input type="checkbox" name="app-' . encryptId($item->pk_module_id) . '" ' . $approvalchecked . ' />';

                $id = '<input type="hidden" name="modid-' . encryptId($item->pk_module_id) . '" value="' . encryptId($item->pk_module_id) . '" />';
                $groid = '<input type="hidden" name="groid-' . encryptId($item->pk_module_id) . '" value="' . encryptId($groupitem->pk_groupmenumodule_id) . '" />';
            }

            $html .= '<tr>
                            <td style="display:none">' . $groid . '</td>
                            <td style="display:none">' . $id . '</td>
                            <td>' . $item->name . '</td>
                            <td>' . $view . '</td>
                            <td>' . $add . '</td>
                            <td>' . $update . '</td>
                            <td>' . $delete . '</td>
                            <td>' . $detail . '</td>
                            <td>' . $approval . '</td>
                        </tr>';

        }

        $rawpengguna = DB::select("SELECT tuser2.full_name FROM mpuser2groupmenu INNER JOIN tuser2 ON tuser2.pk_user_id=mpuser2groupmenu.fk_user_id WHERE mpuser2groupmenu.fk_groupmenu_id=?", [$groupid]);
        $pengguna = '';
        if (count($rawpengguna) > 0) {
            foreach ($rawpengguna as $userdata) {
                $pengguna .= '<tr>
                    <td>' . $userdata->full_name . '</td>
                </tr>'; # code...
            }
        }
        return view('backend.master.groupmenumodules.edit', compact('headerparam', 'html', 'rawGroupMenu', 'pengguna'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $groupid = 0;
        try {
            $groupid = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }

        $data = $request->data;

        if ($data['groupname'] == null) {
            return response(['status' => 'error', 'message' => 'Nama Group harus diisi']);
        }
        // if ($data['groupdesc'] == null) {
        //     return response(['status' => 'error', 'message' => 'Deskripsi Group harus diisi']);
        // }


        try {
            DB::beginTransaction();

            $LogActivity = [];

            $LogActivity['NEW']['Group Menu 2'] = [
                "pk_groupmenu_id" => $groupid,
                "name" => $data['groupname'],
                "description" => $data['groupdesc'],
            ];

            $LogActivity['OLD']['Group Menu 2'] = DB::table('tgroupmenu2')
                ->where('pk_groupmenu_id', $groupid)
                ->get();


            DB::table('tgroupmenu2')
                ->where('pk_groupmenu_id', $groupid)
                ->update(
                    [
                        "name" => $data['groupname'],
                        "description" => $data['groupdesc'],
                        "updated_date" => now(),
                        "updated_by" => Auth::user()->name,
                    ]
                );
            foreach ($data['accessdata'] as $item) {
                $mp_id = 0;
                $module_id = 0;
                $view = 0;
                $add = 0;
                $update = 0;
                $delete = 0;
                $detail = 0;
                $approval = 0;

                foreach ($item as $key => $value) {
                    if (substr($key, 0, 6) == "modid-") {
                        $module_id = decryptForNumber($value);
                    }
                    if (substr($key, 0, 6) == "groid-") {
                        $mp_id = decryptForNumber($value);
                    }
                    if (substr($key, 0, 4) == "vie-") {
                        $view = $value == "on" ? 1 : 0;
                    }
                    if (substr($key, 0, 4) == "add-") {
                        $add = $value == "on" ? 1 : 0;
                    }
                    if (substr($key, 0, 4) == "upd-") {
                        $update = $value == "on" ? 1 : 0;
                    }
                    if (substr($key, 0, 4) == "del-") {
                        $delete = $value == "on" ? 1 : 0;
                    }
                    if (substr($key, 0, 4) == "det-") {
                        $detail = $value == "on" ? 1 : 0;
                    }
                    if (substr($key, 0, 4) == "app-") {
                        $approval = $value == "on" ? 1 : 0;
                    }
                }
                if ($module_id == 0) {
                    return response(['status' => 'error', 'message' => 'Invalid Data']);
                }



                if ($mp_id == 0) {
                    $LogActivity['NEW']['groupmenumodule'][] = [
                        'pk_groupmenumodule_id' => $mp_id,
                        "fk_groupmenu_id" => $groupid,
                        "fk_module_id" => $module_id,
                        "is_view" => $view,
                        "is_add" => $add,
                        "is_update" => $update,
                        "is_detail" => $detail,
                        "is_delete" => $delete,
                        "is_approval" => $approval,
                    ];

                    DB::table('mpgroupmenu2module')->insert([
                        "fk_groupmenu_id" => $groupid,
                        "fk_module_id" => $module_id,
                        "is_view" => $view,
                        "is_add" => $add,
                        "is_update" => $update,
                        "is_detail" => $detail,
                        "is_delete" => $delete,
                        "is_approval" => $approval,
                    ]);
                } else {


                    $LogActivity['OLD']['groupmenumodule'][] = DB::table('mpgroupmenu2module')
                        ->where('pk_groupmenumodule_id', $mp_id)
                        ->where('fk_groupmenu_id', $groupid)
                        ->where('fk_module_id', $module_id)
                        ->get();
                    ;

                    DB::table('mpgroupmenu2module')
                        ->where('pk_groupmenumodule_id', $mp_id)
                        ->where('fk_groupmenu_id', $groupid)
                        ->where('fk_module_id', $module_id)
                        ->update([
                            "is_view" => $view,
                            "is_add" => $add,
                            "is_update" => $update,
                            "is_detail" => $detail,
                            "is_delete" => $delete,
                            "is_approval" => $approval,
                        ]);

                    $LogActivity['NEW']['groupmenumodule'][] = DB::table('mpgroupmenu2module')
                        ->where('pk_groupmenumodule_id', $mp_id)
                        ->where('fk_groupmenu_id', $groupid)
                        ->where('fk_module_id', $module_id)
                        ->get();
                    ;
                }
            }
            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties($LogActivity)
                ->log('update - ' . Route::currentRouteName());
            DB::commit();
            return response(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $groupid = 0;
        try {
            $groupid = decrypt($id);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'Data Gagal Dihapus, Data Module Tidak Sesuai!']);
        }
        try {
            DB::beginTransaction();
            $LogActivity = [];
            $LogActivity['OLD']['Group Menu'] = DB::table('tgroupmenu2')
                ->where('pk_groupmenu_id', $groupid)
                ->get();
            ;

            $LogActivity['OLD']['Group Menu Module'] = DB::table('mpgroupmenu2module')
                ->where('fk_groupmenu_id', $groupid)
                ->get();
            ;

            DB::delete('delete from mpgroupmenu2module where fk_groupmenu_id = ?', [$groupid]);
            DB::delete('delete from tgroupmenu2 where pk_groupmenu_id = ?', [$groupid]);

            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties($LogActivity)
                ->log('Delete - ' . Route::currentRouteName());

            DB::commit();
            return response(['status' => 'success', 'Data berhasil dihapus!']);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response(['status' => 'error', 'Silahkan hubungi Administrator!' . $th->getMessage()]);
        }
    }
}