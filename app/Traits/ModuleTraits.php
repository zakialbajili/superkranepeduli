<?php
namespace App\Traits;

use DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Log;

trait ModuleTraits
{

    public function getModule($routename)
    {
        $rawModule = DB::table("vmodule2actioninrow")->where("url", "Like", "%" . $routename . "%")->first();
        if ($rawModule == null) {
            return null;
        }

        return encryptId($rawModule->fk_module_id);
    }


    public function sendNotification($module_id, $status, $title, $body, $data = [])
    {
        $roles = DB::table('tmodule2approval')
            ->where('fk_module_id', $module_id)
            ->where('fk_status_id', $status)
            ->select('fk_role_id')
            ->first();

        if ($roles != null) {
            $arrayrole = explode(",", $roles->fk_role_id);
            $messaging = app('firebase.messaging');

            $messages = [];
            $datauserrole = DB::table("mpuser2role")
                ->whereIn("fk_role_id", $arrayrole)
                ->get();

            $link = '#';
            if (count($data) > 0) {
                if (isset($data['link'])) {
                    $link = $data['link'];
                }
            }
            foreach ($datauserrole as $item) {
                $datauser = DB::table('tuser2')
                    ->where('pk_user_id', $item->fk_user_id)
                    ->first();
                DB::table('tnotification')
                    ->insert([
                        "fk_user_id" => $item->fk_user_id,
                        "content" => $title . ' ' . $body,
                        "data" => json_encode($data),
                        "type" => 'Approval',
                        "link" => $link,
                        "is_read" => 0,
                        "created_date" => now(),
                        "updated_date" => now(),
                    ]);
                $token = $datauser->web_fcm_token;
                if ($token != "") {

                    $message = CloudMessage::new();
                    $datamessage = $message->withTarget('token', $token)
                        ->withNotification(Notification::create($title, $body));

                    $messages[] = $datamessage;

                }
            }
            $messaging->sendAll($messages);
        }
    }

    public function sendNotificationFileCreated($user_id, $body, $link)
    {
        try {
            $messaging = app('firebase.messaging');

            $datauser = DB::table('tuser2')
                ->where('pk_user_id', $user_id)
                ->first();
            DB::table('tnotification')
                ->insert([
                    "fk_user_id" => $user_id,
                    "content" => $body,
                    "type" => 'File',
                    "link" => $link,
                    "is_read" => 0,
                    "created_date" => now(),
                    "updated_date" => now(),
                ]);
            $token = $datauser->web_fcm_token;
            if ($token != "") {

                $datamessage = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create($body, $body));

                $messages[] = $datamessage;
                $messaging->sendAll($messages);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getProjectMasterTraits($id)
    {

        $data = DB::table('tprojectmaster')
            ->where('pk_projectmaster_id', $id)
            ->first();

        if ($data) {
            $val = [
                "name" => $data->name,
                "master_category" => $data->master_category,
                "master_type" => $data->master_type,
                "master_value" => $data->master_value,
            ];
        } else {
            $val = [
                "name" => "",
                "master_category" => "",
                "master_type" => "",
                "master_value" => "",
            ];
        }
        return $val;
    }

    public function getEmployeeTraits($id)
    {

        $data = DB::table('temployee')
            ->where('pk_employee_id', $id)
            ->first();

        if ($data) {
            $val = [
                "employee_no" => $data->employee_no,
                "full_name" => $data->full_name,
                "position" => $data->position,
            ];
        } else {
            $val = [
                "employee_no" => "",
                "full_name" => "",
                "position" => "",
            ];
        }
        return $val;
    }

    public function getGlobalParamTraits($id)
    {
        $data = DB::table('tglobalparameter')
            ->where('pk_globalparameter_id', $id)
            ->first();
        return $data->value;
    }

    public function generateselect($table, $keycolumn, $displaycolumn, array $condition, $value = ""): string
    {
        try {
            $list = '';
            if (count($condition) == 0) {
                $raw = DB::table($table)
                    ->select($keycolumn, $displaycolumn)
                    ->orderBy($displaycolumn, 'asc')
                    ->get();
            } else {
                $raw = DB::table($table)
                    ->select($keycolumn, $displaycolumn)
                    ->orderBy($displaycolumn, 'asc')
                    ->where($condition)
                    ->get();
            }
            foreach ($raw as $item) {
                $list .= '<option value="' . encryptId($item->$keycolumn) . '" ' . ($value == $item->$keycolumn ? 'Selected' : "") . '>' . $item->$displaycolumn . '</option>';
            }
            return $list;
        } catch (\Throwable $th) {
            log::info($th->getMessage());
            return "";
        }

    }

}
