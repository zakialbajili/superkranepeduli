<?php

namespace App\Http\Controllers\backend\master;

use App\DataTables\ModulesDataTable;
use App\Http\Controllers\Controller;
use App\Models\ModuleApproval2Model;
use App\Models\ModuleModel;
use App\Models\ProjectMasterModel;
use App\Models\RoleModel;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;
use Str;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ModulesDataTable $dataTable)
    {
        $headertag = 'Module';
        $headername = 'Daftar Module';
        $headerlink = 'x';
        $parentname = 'Halaman Utama';
        $parentlink = 'x';

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        return $dataTable->render('backend.master.modules.index', compact('headerparam'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $headertag = 'Module';
        $headername = 'Tambah Module';
        $headerlink = route('admin.modules.index');
        $parentname = 'Halaman Utama';
        $parentlink = route('admin.modules.index');

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        $datanextstatus = ProjectMasterModel::where('master_category', 'Approval Status')
            ->orderBy('name')
            ->get();
        $datarole = RoleModel::where('active', 1)
            ->orderBy('name')
            ->get();
        return view('backend.master.modules.add', compact('headerparam', 'datanextstatus', 'datarole'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd(json_decode($request->dataform, true)['status']);
        $dataform = json_decode($request->dataform, true);

        $modulename = $dataform['modulename'];
        $moduledesc = $dataform['moduledescription'];
        $routeview = $dataform['routeview'];
        $routeadd = $dataform['routeadd'];
        $routeedit = $dataform['routeedit'];
        $routedelete = $dataform['routedelete'];
        $routedetail = $dataform['routedetail'];



        if ($modulename == '') {
            return response(['status' => 'error', 'message' => 'Nama Module harus diisi']);
        }
        try {
            DB::beginTransaction();
            //simpan ke table module
            $activitycontent = [];
            $activitycontent['Module'] = [
                'name' => $modulename,
                'slugs' => Str::slug($modulename),
                'description' => $moduledesc,
                'url_view' => $routeview,
                'url_add' => $routeadd,
                'url_update' => $routeedit,
                'url_delete' => $routedelete,
                'url_detail' => $routedetail,
            ];

            $moduleid = DB::table('tmodule2')->insertGetId(
                $activitycontent['Module']
            );
            //simpan ke table module approval
            if ($request->approvaldata['count'] > 0) {
                $activitycontent['Module Approval'] = [];
                foreach ($request->approvaldata['value'] as $item) {
                    $idstatus = 0;
                    $tipe = $item['Tipe'];
                    $kategori = $item['Kategori'];
                    $catatan = $item['Catatan'];

                    try {
                        $idstatus = decrypt($item['idstat']);
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        return response(['status' => 'error', 'message' => 'Status pada approval tidak sesuai!']);
                    }

                    $idrole = explode(",", $item['idrole']);
                    $rolestore = "-1";
                    if (!is_array($idrole)) {
                        return response(['status' => 'error', 'message' => 'Role pada approval tidak sesuai!!']);
                    }
                    $temprole = 0;
                    foreach ($idrole as $itemrole) {
                        try {
                            $temprole = decrypt($itemrole);
                        } catch (\Throwable $th) {
                            DB::rollBack();
                            return response(['status' => 'error', 'message' => 'Role pada approval tidak sesuai!']);
                        }
                        $rolestore = $rolestore . "," . $temprole;
                    }
                    $idnextstat = explode(",", $item['idnextstat']);
                    $idnextstattore = "-1";
                    if (!is_array($idnextstat)) {
                        DB::rollBack();
                        return response(['status' => 'error', 'message' => 'Status Selanjutnya pada approval tidak sesuai!!']);
                    }
                    $tempidnextstat = 0;
                    foreach ($idnextstat as $itemstatus) {
                        try {
                            $tempidnextstat = decrypt($itemstatus);
                        } catch (\Throwable $th) {
                            DB::rollBack();
                            return response(['status' => 'error', 'message' => 'Status Selanjutnya pada approval tidak sesuai!']);
                        }
                        $idnextstattore = $idnextstattore . "," . $tempidnextstat;
                    }

                    //simpan ke table tmodule2approval
                    $activitycontent['Module Approval'][] = [
                        "fk_module_id" => $moduleid,
                        "fk_status_id" => $idstatus,
                        "fk_nextstatus_id" => $idnextstattore,
                        "fk_role_id" => $rolestore,
                        "notes" => $catatan,
                        "moduleapproval" => $tipe,
                        "status_category" => $kategori,
                    ];
                    $moduleapproval = new ModuleApproval2Model;
                    $moduleapproval->fk_module_id = $moduleid;
                    $moduleapproval->fk_status_id = $idstatus;
                    $moduleapproval->fk_nextstatus_id = $idnextstattore;
                    $moduleapproval->fk_role_id = $rolestore;
                    $moduleapproval->notes = $catatan;
                    $moduleapproval->status_type = $tipe;
                    $moduleapproval->status_category = $kategori;
                    // $moduleapproval->created_date=$catatan;
                    // $moduleapproval->created_by=$catatan;
                    // $moduleapproval->updated_date=$catatan;
                    // $moduleapproval->udpated_by=$catatan;
                    $moduleapproval->save();

                }

                activity()
                    ->causedBy(Auth::user()->pk_user_id)
                    ->withProperties($activitycontent)
                    ->log('Add - ' . Route::currentRouteName());
            }
            DB::commit();
            return response(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(['status' => 'error', 'message' => $th->getMessage()]);
        }

        // dd($request->approvaldata['value']);
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
        $moduleid = 0;
        try {
            $moduleid = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }
        $headertag = 'Module';
        $headername = 'Edit Module';
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
        $datamodule = ModuleModel::find($moduleid);

        $content = '';
        foreach ($datamodule->Module2Approvals as $Module2Approval) {
            $rolesid = explode(",", $Module2Approval->fk_role_id);
            $rolecontentid = '';
            $rolecontent = '';
            if (is_array($rolesid)) {
                foreach ($rolesid as $roleid) {
                    if ($roleid > 0) {
                        $rolecontentid = $rolecontentid . encryptId($roleid) . ",";
                        $roledata = RoleModel::where('pk_role_id', $roleid)->first();
                        $rolecontent = $rolecontent . $roledata->name . ",";
                    }
                }
            }
            $rolecontentid = substr($rolecontentid, 0, strlen($rolecontentid) - 1);
            $rolecontent = substr($rolecontent, 0, strlen($rolecontent) - 1);

            $statusesid = explode(",", $Module2Approval->fk_nextstatus_id);
            $statuscontentid = '';
            $statuscontent = '';
            if (is_array($statusesid)) {
                foreach ($statusesid as $statusid) {
                    if ($statusid > 0) {
                        $statuscontentid = $statuscontentid . encryptId($statusid) . ",";

                        $statusdata = ProjectMasterModel::where('pk_projectmaster_id', $statusid)->first();
                        $statuscontent = $statuscontent . $statusdata->name . ",";
                    }
                }
            }

            $statuscontentid = substr($statuscontentid, 0, strlen($statuscontentid) - 1);
            $statuscontent = substr($statuscontent, 0, strlen($statuscontent) - 1);

            $content .= '<tr id="' . encryptId($Module2Approval->pk_moduleapproval_id) . '">' .
                '<td style="display:none">' . encryptId($Module2Approval->pk_moduleapproval_id) . '</td>' .
                '<td style="display:none">' . encryptId($Module2Approval->fk_status_id) . '</td>' .
                '<td>' . $Module2Approval->ProjectMaster->name . '</td>' .
                '<td>' . $Module2Approval->status_type . '</td>' .
                '<td>' . $Module2Approval->status_category . '</td>' .
                '<td style="display:none">' . $rolecontentid . '</td>' .
                '<td>' . $rolecontent . '</td>' .
                '<td style="display:none">' . $statuscontentid . '</td>' .
                '<td>' . $statuscontent . '</td>' .
                '<td>' . $Module2Approval->notes . '</td>' .
                '<td><button class="btn btn-sm btn-success edit-approval"><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-danger ml-2 delete-approval"><i class="fa fa-trash"></i></button></td>' .
                '</tr>';
        }


        $datanextstatus = ProjectMasterModel::where('master_category', 'Approval Status')
            ->orderBy('name')
            ->get();
        $datarole = RoleModel::where('active', 1)
            ->orderBy('name')
            ->get();
        return view('backend.master.modules.edit', compact('headerparam', 'datanextstatus', 'datarole', 'datamodule', 'content'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $moduleid = 0;
        try {
            $moduleid = decrypt($id);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'message' => 'Data tidak sesuai!!']);
        }

        //dd(json_decode($request->dataform, true)['status']);
        $dataform = json_decode($request->dataform, true);

        $modulename = $dataform['modulename'];
        $moduledesc = $dataform['moduledescription'];
        $routeview = $dataform['routeview'];
        $routeadd = $dataform['routeadd'];
        $routeedit = $dataform['routeedit'];
        $routedelete = $dataform['routedelete'];
        $routedetail = $dataform['routedetail'];


        if ($modulename == '') {
            return response(['status' => 'error', 'message' => 'Nama Module harus diisi']);
        }

        try {
            DB::beginTransaction();
            //simpan ke table module
            $oldmodule = DB::table('tmodule2')
                ->where('pk_module_id', $moduleid)
                ->get();

            $activitycontent = [];
            $activitycontent['OLD']['Module'] = [
                $oldmodule
            ];

            $activitycontent['NEW']['Module'] = [
                'name' => $modulename,
                'slugs' => Str::slug($modulename),
                'description' => $moduledesc,
                'url_view' => $routeview,
                'url_add' => $routeadd,
                'url_update' => $routeedit,
                'url_delete' => $routedelete,
                'url_detail' => $routedetail,
            ];
            DB::table('tmodule2')
                ->where('pk_module_id', $moduleid)
                ->update(
                    $activitycontent['NEW']['Module']
                );

            $activitymodule2approval = DB::table('tmodule2approval')
                ->where('fk_module_id', $moduleid)
                ->get();
            $activitycontent["OLD"]["Module 2 Approval"][] = $activitymodule2approval;

            $listidapproval = [];
            if ($request->approvaldata['count'] > 0) {
                foreach ($request->approvaldata['value'] as $item) {
                    $idapproval = "0";
                    try {
                        if (is_numeric($item['id'])) {
                            $idapproval = 0;
                        } else {
                            $idapproval = decryptForNumber($item['id']);
                        }
                    } catch (\Throwable $th) {
                        $idapproval = 0;
                    }
                    $idstatus = 0;
                    $tipe = $item['Tipe'];
                    $kategori = $item['Kategori'];
                    $catatan = $item['Catatan'];

                    try {
                        $idstatus = decryptForNumber($item['idstat']);
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        return response(['status' => 'error', 'message' => 'Status pada approval tidak sesuai!']);
                    }

                    $idrole = explode(",", $item['idrole']);
                    $rolestore = "-1";
                    if (!is_array($idrole)) {
                        return response(['status' => 'error', 'message' => 'Role pada approval tidak sesuai!!']);
                    }
                    $temprole = 0;
                    foreach ($idrole as $itemrole) {
                        try {
                            $temprole = decryptForNumber($itemrole);
                        } catch (\Throwable $th) {
                            DB::rollBack();
                            return response(['status' => 'error', 'message' => 'Role pada approval tidak sesuai!']);
                        }
                        $rolestore = $rolestore . "," . $temprole;
                    }
                    $idnextstat = explode(",", $item['idnextstat']);
                    $idnextstattore = "-1";
                    if (!is_array($idnextstat)) {
                        DB::rollBack();
                        return response(['status' => 'error', 'message' => 'Status Selanjutnya pada approval tidak sesuai!!']);
                    }
                    $tempidnextstat = 0;
                    foreach ($idnextstat as $itemstatus) {
                        try {
                            $tempidnextstat = decryptForNumber($itemstatus);
                        } catch (\Throwable $th) {
                            DB::rollBack();
                            return response(['status' => 'error', 'message' => 'Status Selanjutnya pada approval tidak sesuai!']);
                        }
                        $idnextstattore = $idnextstattore . "," . $tempidnextstat;
                    }

                    //simpan ke table tmodule2approval
                    $activitymodule2approval = [];
                    if ($idapproval == 0) {
                        $activitymodule2approval = [
                            "fk_module_id" => $moduleid,
                            "fk_status_id" => $idstatus,
                            "fk_nextstatus_id" => $idnextstattore,
                            "fk_role_id" => $rolestore,
                            "notes" => $catatan,
                            "status_type" => $tipe,
                            "status_category" => $kategori,
                        ];
                        $idapproval = DB::table('tmodule2approval')->insertGetId(
                            $activitymodule2approval
                        );
                    } else {

                        $activitymodule2approval = [
                            "fk_status_id" => $idstatus,
                            "fk_nextstatus_id" => $idnextstattore,
                            "fk_role_id" => $rolestore,
                            "notes" => $catatan,
                            "status_type" => $tipe,
                            "status_category" => $kategori,
                        ];

                        ModuleApproval2Model::where('pk_moduleapproval_id', $idapproval)->update($activitymodule2approval);
                    }
                    $activitycontent["NEW"]["Module 2 Approval"][] = $activitymodule2approval;
                    $listidapproval[] = $idapproval;
                }
            } //hapus jika ada diserver namun tidak ada di table;
            if (count($listidapproval) > 0) {
                ModuleApproval2Model::where('fk_module_id', $moduleid)
                    ->whereNotIn('pk_moduleapproval_id', $listidapproval)->delete();
            }

            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties($activitycontent)
                ->log('Update - ' . Route::currentRouteName());
            DB::commit();
            return response(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(['status' => 'error', 'message' => 'Silahkan hubungi administrator!! ' . $th->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $moduleid = 0;
        try {
            $moduleid = decrypt($id);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'Data Gagal Dihapus, Data Module Tidak Sesuai!']);
        }
        ;

        try {
            DB::beginTransaction();

            $module = ModuleModel::where('pk_module_id', $moduleid)->get();
            $moduelapproval = ModuleApproval2Model::where('fk_module_id', $moduleid)->get();

            DB::delete('delete from tmodule2approval where fk_module_id = ?', [$moduleid]);
            DB::delete('delete from tmodule2 where pk_module_id = ?', [$moduleid]);
            activity()
                ->causedBy(Auth::user()->pk_user_id)
                ->withProperties(["Module Approval" => $moduelapproval, "Module" => $module])
                ->log('Delete - ' . Route::currentRouteName());
            DB::commit();
            return response(['status' => 'success', 'Data berhasil dihapus!']);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response(['status' => 'error', 'Silahkan hubungi Administrator!']);
        }
    }
}
;