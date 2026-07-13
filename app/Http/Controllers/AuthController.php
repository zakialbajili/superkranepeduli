<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function index()
    {
        return view('backend.login');
    }

    public function userLogin(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'employee_no' => 'required|string',
            'password' => 'required|string|min:8|max:8' // Harus 8 digit DDMMYYYY
        ]);

        try {
            $employeeNo = $request->input('employee_no');
            $rawPassword = $request->input('password');

            // 2. Konversi DDMMYYYY menjadi format SQL YYYY-MM-DD
            $day = substr($rawPassword, 0, 2);
            $month = substr($rawPassword, 2, 2);
            $year = substr($rawPassword, 4, 4);
            $formattedDate = "$year-$month-$day";

            // 3. Cek ke database lokal DENGAN validasi active = 1
            $user = DB::table('tuser')
                ->where('employee_no', $employeeNo)
                ->where('birth_date', $formattedDate)
                ->where('active', 1)
                ->first();

            // 4. Logika penentuan sukses/gagal
            if ($user) {

                // --- PENGAMANAN SESSION ---
                // Regenerasi ID Session agar terhindar dari Session Fixation
                $request->session()->regenerate();

                // Buat Session Karyawan
                session([
                    'is_logged_in_api' => true,
                    'employee_no' => $user->employee_no,
                    'full_name' => $user->full_name,
                    'position' => $user->posisi,
                ]);

                // --- UPDATE LOGIN TERAKHIR & TOKEN ---
                // Simpan juga session id ke dalam kolom token seperti admin
                DB::table('tuser')
                    ->where('pk_user_id', $user->pk_user_id)
                    ->update([
                        'token' => $request->session()->getId(),
                        'login_last' => now()
                    ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Login Success!'
                ], 200);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'Login gagal! NIK atau Password salah, atau akun tidak aktif.'
                ], 401);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan sistem.',
                'error' => $th->getMessage()
            ], 500);
        }
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

            Auth::user()->update(['token' => Session::getId(), 'login_last' => now()]);

            $currentpage = 'admin.notification.index';

            $moduledata = DB::select('SELECT tmodule2.`url_view` FROM thseuser INNER JOIN `tmodule2`
            ON thseuser.`fk_module_id`=tmodule2.`pk_module_id` WHERE thseuser.`pk_user_id`=?', [Auth::user()->pk_user_id]);
            if ($moduledata) {
                if (strpos($moduledata[0]->url_view, "|") > 0) {
                    $abl = explode("|", $moduledata[0]->url_view);
                    $currentpage = $abl[0];
                } else {
                    if ($moduledata[0]->url_view != "") {
                        $currentpage = $moduledata[0]->url_view;
                    }
                }
            }
            return redirect()->route($currentpage);
        }

        return redirect("login/admin")->with('error', 'Username / Password Salah');
    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
