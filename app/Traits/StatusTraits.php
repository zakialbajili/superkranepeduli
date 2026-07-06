<?php
namespace App\Traits;

use DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\ModuleTraits;

trait StatusTraits
{
    use ModuleTraits;
    public function getStatusContent($routename, $idrecord, $currentstatusid)
    {
        //idmodule module id
        $idmodule = $this->getModule($routename);
        if ($idmodule == null) {
            return null;
        }
        $idmodule = decryptForNumber($idmodule);
        $StatusAttribute = $this->getStatusAttribute($idmodule, $currentstatusid);

        $idApprovalModule = 0;
        $listnextstatus = $StatusAttribute["nextstatus"];
        $idApprovalModule = $StatusAttribute["idApprovalModule"];
        $categoryApprovalModule = $StatusAttribute["categoryApprovalModule"];
        $typeApprovalModule = $StatusAttribute["typeApprovalModule"];
        $currentStatusName = $StatusAttribute["currentStatusName"];
        $rawStatus = DB::table("tprojectmaster")
            ->select('pk_projectmaster_id', 'name')
            ->whereIn('pk_projectmaster_id', $listnextstatus)
            ->get();

        $statusItemsContent = "";
        if (count($rawStatus) > 0) {
            foreach ($rawStatus as $statusItem) {
                $statusItemsContent .= '<option value="' . encryptId($statusItem->pk_projectmaster_id) . '">' . $statusItem->name . '</option>';
            }
        }

        // Generate Status Controller
        $statusAction = '<div class="row">' .
            '<form id="statusform">' .
            '<input type="hidden" id="moduleapprovalid" name="moduleapprovalid" value="' . encryptId($idApprovalModule) . '"/>' .
            '<div class="form-group">' .
            '<label for="status">Status</label>' .
            '<select id="status" name="status" class="form-control custom-select">' .
            '<option value="" Selected>Pilih Status</option>' .
            $statusItemsContent .
            '</select>' .
            '</div>' .
            '<div class="form-group">' .
            '<label for="status_notes">Catatan Status</label>' .
            '<textarea id="status_notes" name="status_notes"class="form-control"></textarea>' .
            '</div>' .
            '</form>' .
            '</div>';

        $rawStatusHistory = DB::table('vapprovalstatushistory2')
            ->select('status_name', 'note', 'created_date', 'created_by')
            ->orderBy('created_date', 'desc')
            ->where('fk_module2_id', $idmodule)
            ->where('unique_id', $idrecord)
            ->get();

        $statusTableItems = '';
        $currentdate = "";
        if (count($rawStatusHistory) > 0) {
            foreach ($rawStatusHistory as $StatusHistoryItem) {
                if ($currentdate != \Carbon\Carbon::parse($StatusHistoryItem->created_date)->format('d M Y')) {
                    $statusTableItems .= '<div class="time-label">' .
                        '<span class="bg-red">' . \Carbon\Carbon::parse($StatusHistoryItem->created_date)->format('d M Y') . '</span>' .
                        '</div>' .
                        '<div>' .
                        '<i class="fas fa-envelope bg-info"></i>' .
                        '<div class="timeline-item">' .
                        '<span class="time"><i class="fas fa-clock mr-1"></i>' . \Carbon\Carbon::parse($StatusHistoryItem->created_date)->format('H:i') . '</span>' .
                        '<h3 class="timeline-header">' . $StatusHistoryItem->created_by . '</h3>' .
                        '<div class="timeline-body">' .
                        '<b>' . $StatusHistoryItem->status_name . ': </b> ' . $StatusHistoryItem->note .
                        '</div>' .
                        '</div>' .
                        '</div>';
                    ;
                } else {
                    $statusTableItems .=
                        '<div>' .
                        '<i class="fas fa-envelope bg-info"></i>' .
                        '<div class="timeline-item">' .
                        '<span class="time"><i class="fas fa-clock mr-1"></i>' . \Carbon\Carbon::parse($StatusHistoryItem->created_date)->format('H:i') . '</span>' .
                        '<h3 class="timeline-header">' . $StatusHistoryItem->created_by . '</h3>' .
                        '<div class="timeline-body">' .
                        '<b>' . $StatusHistoryItem->status_name . ': </b> ' . $StatusHistoryItem->note .
                        '</div>' .
                        '</div>' .
                        '</div>';
                }
                $currentdate = \Carbon\Carbon::parse($StatusHistoryItem->created_date)->format('d M Y');
            }
        }


        $statusTable = '<div class="timeline">' .
            $statusTableItems .
            '</div>';

        $badgespan = "bg-danger";
        $statusInfo = "Anda tidak diijinkan untuk merubah data!";
        switch ($categoryApprovalModule) {
            case 'Locked':
                $badgespan = "bg-warning";
                $statusInfo = "Anda hanya diijinkan untuk merubah status!";
                break;
            case 'Unlocked':
                $badgespan = "bg-primary";
                $statusInfo = "Anda diijinkan untuk merubah data!";
                break;
            default:
                $badgespan = "bg-danger";
                $statusInfo = "Anda tidak diijinkan untuk merubah data!";
                break;
        }
        $statusAttribute = '<div class="row">' .
            '<p><span class="badge ' . $badgespan . ' warning mr-2" style="font-size: large">' . $currentStatusName . '</span> ' . $statusInfo . '</p>' .
            '</div>';

        $content = [
            "StatusContent" => $statusAction,
            "StatusTable" => $statusTable,
            "StatusAttribute" => $statusAttribute,
            "ModuleApproval" => encryptId($idApprovalModule),
        ];

        return $content;
    }

    public function getStatusAttribute($idmodule, $currentstatusid)
    {
        $idApprovalModule = 0;
        // DRAFT/WAITING/DLL
        $typeApprovalModule = "";
        //Locked/Unlocked
        $categoryApprovalModule = "Locked";
        $currentStatusName = "";
        //get next status list of current status;
        $rawNextStatus = DB::table("vmodule2approval")
            ->where("fk_module_id", $idmodule)
            ->where("fk_status_id", $currentstatusid)
            ->get();
        $listnextstatus = [];
        if (count(Auth::user()->roles) > 0) {
            foreach (Auth::user()->roles as $role) {
                foreach ($rawNextStatus as $nexStatuses) {
                    $arrayroles = explode(",", $nexStatuses->fk_role_id);
                    if (in_array($role->fk_role_id, $arrayroles)) {
                        $arraystatus = explode(",", $nexStatuses->fk_nextstatus_id);
                        $listnextstatus = array_merge($listnextstatus, $arraystatus);
                        $idApprovalModule = $nexStatuses->pk_moduleapproval_id;
                        $categoryApprovalModule = $nexStatuses->status_category;
                        $typeApprovalModule = $nexStatuses->status_type;
                    }
                    $currentStatusName = $nexStatuses->status_name;
                }
            }
        }
        $result = [
            "nextstatus" => $listnextstatus,
            "idApprovalModule" => $idApprovalModule,
            "categoryApprovalModule" => $categoryApprovalModule,
            "typeApprovalModule" => $typeApprovalModule,
            "currentStatusName" => $currentStatusName,
            "idModule" => $idmodule,
        ];
        return $result;
    }

    public function getStatusAttributeOnRouteName($routename, $currentstatusid)
    {
        $rawModule = DB::table("vmodule2actioninrow")->where("url", "Like", "%" . $routename . "%")->first();
        if ($rawModule == null) {
            return null;
        }

        $idmodule = $rawModule->fk_module_id;

        $idApprovalModule = 0;
        // DRAFT/WAITING/DLL
        $typeApprovalModule = "";
        //Locked/Unlocked
        $categoryApprovalModule = "Locked";
        $currentStatusName = "";
        //get next status list of current status;
        $rawNextStatus = DB::table("vmodule2approval")
            ->where("fk_module_id", $idmodule)
            ->where("fk_status_id", $currentstatusid)
            ->get();
        $listnextstatus = [];
        if (count(Auth::user()->roles) > 0) {
            foreach (Auth::user()->roles as $role) {
                foreach ($rawNextStatus as $nexStatuses) {
                    $arrayroles = explode(",", $nexStatuses->fk_role_id);
                    if (in_array($role->fk_role_id, $arrayroles)) {
                        $arraystatus = explode(",", $nexStatuses->fk_nextstatus_id);
                        $listnextstatus = array_merge($listnextstatus, $arraystatus);
                        $idApprovalModule = $nexStatuses->pk_moduleapproval_id;
                    }
                    $categoryApprovalModule = $nexStatuses->status_category;
                    $typeApprovalModule = $nexStatuses->status_type;
                    $currentStatusName = $nexStatuses->status_name;
                }
            }
        }
        $result = [
            "nextstatus" => $listnextstatus,
            "idApprovalModule" => $idApprovalModule,
            "categoryApprovalModule" => $categoryApprovalModule,
            "typeApprovalModule" => $typeApprovalModule,
            "currentStatusName" => $currentStatusName,
            "idModule" => $idmodule,
        ];
        return $result;
    }

    public function getModuleAttributeOnRouteName($routename)
    {
        $rawModule = DB::table("vmodule2actioninrow")->where("url", "Like", "%" . $routename . "%")->first();
        if ($rawModule == null) {
            return null;
        }

        $idmodule = $rawModule->fk_module_id;

        $result = [
            "idModule" => $idmodule,
        ];
        return $result;
    }

    public function getStatusAuthorized($routename)
    {
        $rawModule = DB::table("vmodule2actioninrow")->where("url", "Like", "%" . $routename . "%")->first();
        if ($rawModule == null) {
            return null;
        }

        $idmodule = $rawModule->fk_module_id;

        //get next status list of current status;
        $rawStatusOnModule = DB::table("vmodule2approval")
            ->select('fk_status_id', 'fk_role_id')
            ->where("fk_module_id", $idmodule)
            ->get();
        $listAuthorizedStatus = [];
        if (count(Auth::user()->roles) > 0) {
            foreach (Auth::user()->roles as $role) {
                foreach ($rawStatusOnModule as $statusOnModule) {
                    $arrayroles = explode(",", $statusOnModule->fk_role_id);
                    if (in_array($role->fk_role_id, $arrayroles)) {
                        $listAuthorizedStatus[] = $statusOnModule->fk_status_id;
                    }
                }
            }
        }
        $result = [
            "authorizedStatus" => $listAuthorizedStatus,
        ];
        return $result;
    }

    public function createStatus($routename, $data)
    {
        $idmodule = $this->getModule($routename);
        $idmodule = decryptForNumber($idmodule);
        $data['fk_module2_id'] = $idmodule;
        $data['created_date'] = now();

        DB::table('tapprovalstatushistory2')->insert(
            $data
        );
    }
}
