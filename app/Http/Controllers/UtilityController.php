<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class UtilityController extends Controller
{
    public function getNotifUnReadCount(Request $request)
    {
        try {
            $userId = decrypt($request->id);

            $data = DB::table('tnotification')
                ->where('fk_user_id', $userId)
                ->where('is_read', 0)
                ->limit(5)
                ->select('content', 'link', 'created_date', 'pk_notification_id')
                ->orderBy('created_date', 'desc')
                ->get();

            $unreadcount = DB::table('tnotification')
                ->where('fk_user_id', $userId)
                ->where('is_read', 0)
                ->select('content', 'link', 'created_date')
                ->count();


            $content = '<span class="dropdown-item dropdown-header">' . $unreadcount . ' Notifications</span>
                $itemcontent$
                <div class="dropdown-divider"></div>
                <a href="' . route('admin.notification.index') . '" class="dropdown-item dropdown-footer">See All Notifications</a>';

            $itemscontent = "";
            if (count($data) > 0) {
                foreach ($data as $item) {
                    $itemscontent .= '<div class="media">
                        <a href="' . route('admin.utility.selectNotification', encrypt($item->pk_notification_id)) . '" class="dropdown-item readnotif">
                        <div class="media-body ml-2 mr-2">
                        <div class="box" style="
                        overflow-wrap: break-word;
                        hyphens: manual;">
                        <p class="text-sm"> ' . $item->content . ' </p>
                        <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> ' . $item->created_date . '</p>
                        </div>
                        </div>
                        </a>
                    </div>';
                }
            }

            $content = str_replace('$itemcontent$', $itemscontent, $content);

            return response(["content" => $content, "count" => $unreadcount]);
        } catch (\Throwable $th) {
            return response(["content" => "", "count" => 0]);
        }
    }

    public function selectNotification(string $id)
    {
        $notificationid = 0;
        try {
            $notificationid = decrypt($id);
        } catch (\Throwable $th) {
            toastr('Invalid url', 'error');
            return redirect()->back();
        }

        $datanotif = DB::table('tnotification')
            ->select('type', 'link', 'content', 'data')
            ->where('fk_user_id', Auth::user()->pk_user_id)
            ->where('pk_notification_id', $notificationid)
            ->first();


        DB::table('tnotification')
            ->where('fk_user_id', Auth::user()->pk_user_id)
            ->where('pk_notification_id', $notificationid)
            ->update([
                'is_read' => 1,
                'updated_date' => now()
            ]);

        if ($datanotif->type == "File") {
            $file = Storage::disk('public')->get($datanotif->link);

            // Set the headers for the response.
            $response = Response::make($file, 200);
            $response->header('Content-Type', 'application/octet-stream');
            $response->header('Content-Disposition', 'attachment; filename=' . $datanotif->link);

            // Return the response with the file content.
            return $response;
            // return Storage::disk('public')->download($datanotif->link);
        } else {
            return redirect($datanotif->link);
        }
    }
}
