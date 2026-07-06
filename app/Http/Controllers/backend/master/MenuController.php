<?php

namespace App\Http\Controllers\backend\master;

use App\Http\Controllers\Controller;
use App\Models\Menu2Model;
use App\Models\ModuleModel;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Route;
use Str;

class MenuController extends Controller
{
    private $storemenuid = [];
    public function index()
    {
        $headertag = 'Menu';
        $headername = 'Daftar Menu';
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


        //get module
        $datamodule = ModuleModel::select('pk_module_id', 'name')->get();


        $datamenu = json_encode($this->generatemenu());

        return view('backend.master.menu.index', compact('headerparam', 'datamenu', 'datamodule'));
    }
    function generatemenu()
    {
        $datamenu = DB::Select('Select * from vmenu2generate');
        $generatemenu = [];
        $generatemenu[] = [
            "id" => "root",
            "parent" => "#",
            "text" => "root",
            "data" => []
        ];
        foreach ($datamenu as $item) {
            $parentid = "root";
            if ($item->parent_menu_id > 0) {
                $parentid = encryptId($item->parent_menu_id);
            }
            $generatemenu[] = [
                "id" => encryptId($item->pk_menu_id),
                "parent" => $parentid,
                "text" => $item->name,
                "data" => [
                    "module_action_id" => encryptId($item->fk_moduleaction_id),
                    "module_id" => encryptId($item->fk_module2_id),
                    "url" => $item->url
                ]
            ];
        }
        return $generatemenu;
    }
    public function getmenudata(Request $request)
    {
        $idmenu = 0;
        try {
            $idmenu = decryptForNumber($request->id);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'message' => 'Data Tidak Valid']);
        }

        if ($idmenu == 0) {
            return response(['status' => 'error', 'message' => 'Data Tidak Valid!']);
        }

        //ambil data menu
        $rawMenu = DB::table('vmenu2generate')
            ->select('pk_menu_id', 'name', 'url', 'fk_module2_id', 'fk_module2action_id', 'fk_moduleaction_id')
            ->where('pk_menu_id', $idmenu)
            ->first();
        $dataMenu = [
            'idmenu' => encryptId($rawMenu->pk_menu_id),
            'name' => $rawMenu->name,
            'url' => $rawMenu->url,
            'module_id' => encryptId($rawMenu->fk_module2_id),
            'module_action' => encryptId($rawMenu->fk_moduleaction_id),
        ];

        $rawModuleAction = DB::table('vmodule2actioninrow')
            ->where('fk_module_id', $rawMenu->fk_module2_id)
            ->where('action_name', 'View')
            ->get();

        $dataModuleAction = [];
        foreach ($rawModuleAction as $item) {
            $dataModuleAction[] = [
                "id" => encryptId($item->fk_module2action_id),
                "text" => $item->action_name,
                "url" => $item->url
            ];
        }
        $datareturn = [
            "menu" => $dataMenu,
            "moduleaction" => $dataModuleAction
        ];

        return response(['status' => 'success', 'data' => $datareturn]);
    }
    public function getmoduleactionsinmoduleid(Request $request)
    {
        $idmodule = 0;
        try {
            $idmodule = decryptForNumber($request->id);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'message' => 'Data Tidak Valid']);
        }

        if ($idmodule == 0) {
            return response(['status' => 'error', 'message' => 'Data Tidak Valid!']);
        }

        $rawModuleAction = DB::table('vmodule2actioninrow')
            ->where('fk_module_id', $idmodule)
            ->where('action_name', 'View')
            ->get();

        $dataModuleAction = [];
        foreach ($rawModuleAction as $item) {
            $dataModuleAction[] = [
                "id" => encryptId($item->fk_module2action_id),
                "text" => $item->action_name,
                "url" => $item->url
            ];
        }

        return response(['status' => 'success', 'data' => $dataModuleAction]);
    }

    public function savemenu(Request $request)
    {
        $datarequest = json_decode($request->data, true);
        try {
            if (count($datarequest[0]['children']) == 0) {
                return response(['status' => 'error', 'message' => 'Data Tidak Valid!']);
            }
            DB::beginTransaction();
            DB::table('tmenu2')->delete();
            $this->breakdownmenu($datarequest[0]['children']);

            // if (count($this->storemenuid) > 0) {
            //     Menu2Model::whereNotIn('pk_menu_id', $this->storemenuid)->delete();
            // }

            DB::commit();
            return response(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(['status' => 'error', 'message' => 'Silahkan hubungi administrator!! ' . $th->getMessage()]);
        }
    }

    function breakdownmenu($requestdata, $parentid = 0)
    {
        try {
            $headerseq = 1;
            $tempmenuid = [];
            foreach ($requestdata as $headeritem) {
                // $menuid = decryptForNumber($headeritem['id']);
                $is_parent_menu = count($headeritem['children']) > 0 ? 1 : 0;
                $fkmoduleactionid = decryptForNumber((isset($headeritem['data']['module_action_id']) ? $headeritem['data']['module_action_id'] : 0));
                $moduleid = decryptForNumber((isset($headeritem['data']['module_id']) ? $headeritem['data']['module_id'] : 0));
                $url = isset($headeritem['data']['url']) ? $headeritem['data']['url'] : "";
                if ($fkmoduleactionid == "0") {
                    $moduleactionid = 0;
                } else {
                    $moduleactionid = substr($fkmoduleactionid, Str::length($fkmoduleactionid) - 1, 1);
                }
                $name = $headeritem['text'];
                $sequence = $headerseq;
                $menuid = DB::table('tmenu2')->insertGetId(
                    [
                        'is_parent_menu' => $is_parent_menu,
                        'parent_menu_id' => $parentid,
                        'fk_moduleaction_id' => $fkmoduleactionid,
                        'name' => $name,
                        'sequence' => $sequence,
                        'url' => $url,
                        'active' => 1,
                        // 'created_date' => $url,
                        // 'created_by' => $url,
                        'fk_module2_id' => $moduleid,
                        'fk_module2action_id' => $moduleactionid,
                    ]
                );
                // if ($menuid == 0) {
                //     //insert table
                //     $menuid = DB::table('tmenu2')->insertGetId(
                //         [
                //             'is_parent_menu' => $is_parent_menu,
                //             'parent_menu_id' => $parentid,
                //             'fk_moduleaction_id' => $fkmoduleactionid,
                //             'name' => $name,
                //             'sequence' => $sequence,
                //             'url' => $url,
                //             'active' => 1,
                //             // 'created_date' => $url,
                //             // 'created_by' => $url,
                //             'fk_module2_id' => $moduleid,
                //             'fk_module2action_id' => $moduleactionid,
                //         ]
                //     );
                // } else {
                //     //update table

                //     DB::table('tmenu2')
                //         ->where('pk_menu_id', $menuid)
                //         ->update([
                //             'is_parent_menu' => $is_parent_menu,
                //             'parent_menu_id' => $parentid,
                //             'fk_moduleaction_id' => $fkmoduleactionid,
                //             'name' => $name,
                //             'sequence' => $sequence,
                //             'url' => $url,
                //             'active' => 1,
                //             // 'created_date' => $url,
                //             // 'created_by' => $url,
                //             'fk_module2_id' => $moduleid,
                //             'fk_module2action_id' => $moduleactionid,
                //         ]);

                // }

                if ($is_parent_menu > 0) {
                    $this->breakdownmenu($headeritem['children'], $menuid);
                }
                $headerseq++;
                array_push($this->storemenuid, $menuid);
            }

            return $tempmenuid;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}