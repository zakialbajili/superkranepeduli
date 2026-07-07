<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function index()
    {
        return view('login');
    }
    public function AdminLogin()
    {
        return view('loginAdmin');
    }
    public function customLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt(['employee_no' => $request->username, 'password' => $request->password, 'active' => 1])) {
            $request->session()->regenerate();
            $rawData = DB::select('SELECT DISTINCT b.pk_menu_id,b.is_parent_menu,b.parent_menu_id,b.name,IF(ISNULL(a.pk_user_id),NULL,vmodule2actioninrow.url) url FROM `tmenu2` b
                LEFT JOIN (
                    SELECT fk_module2action_id,pk_user_id
                    FROM `thseuser`
                    INNER JOIN `mpuser2groupmenu`
                        ON thseuser.`pk_user_id` = `mpuser2groupmenu`.`fk_user_id`
                    INNER JOIN `vgroupmenumoduleaction`
                        ON `mpuser2groupmenu`.`fk_groupmenu_id`=`vgroupmenumoduleaction`.`fk_groupmenu_id`
                    INNER JOIN vmodule2actioninrow ON vmodule2actioninrow.fk_module2action_id=vgroupmenumoduleaction.fk_moduleaction_id
                    WHERE thseuser.`pk_user_id`=?
                ) a ON  a.fk_module2action_id=b.fk_moduleaction_id
                LEFT JOIN `vmodule2actioninrow` on `vmodule2actioninrow`.`fk_module2action_id`=b.`fk_moduleaction_id` order by b.pk_menu_id', [Auth::user()->pk_user_id]);
            session(['menu' => $rawData]);

            Auth::user()->update(['token' => Session::getId(), 'login_last'=>now()]);

            $currentpage = 'admin.notification.index';
            // $currentpage = 'admin.costsubmissionreportdata.index';

            // $moduledata = DB::select('SELECT tmodule2.`url_view` FROM thseuser INNER JOIN `tmodule2`
            // ON thseuser.`fk_module_id`=tmodule2.`pk_module_id` WHERE thseuser.`pk_user_id`=?', [Auth::user()->pk_user_id]);
            // if ($moduledata) {
            //     if (strpos($moduledata[0]->url_view, "|") > 0) {
            //         $abl = explode("|", $moduledata[0]->url_view);
            //         $currentpage = $abl[0];
            //     } else {
            //         if ($moduledata[0]->url_view != "") {
            //             $currentpage = $moduledata[0]->url_view;
            //         }
            //     }
            // }
            return redirect()->route($currentpage);
        }

        return redirect("login")->with('error', 'Username / Password Salah');
    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
