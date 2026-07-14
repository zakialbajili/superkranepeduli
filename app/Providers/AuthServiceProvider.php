<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\UserModel;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
        try {

            $rawModule = Cache::remember('moduleactions', 600, function () {
                return DB::table('vmodule2actioninrow')
                    ->select(['url', 'fk_module2action_id', 'name'])
                    ->get();
            });
            $allowedModules = [
                'admin.storeToken',
                'admin.utility.unreadNotification',
                'admin.utility.selectNotification',
                'admin.notification.index',
                'admin.notification.readdatatables',
                'admin.notification.unreaddatatables',
            ];

            foreach ($rawModule as $itemModule) {
                if (strpos($itemModule->url, "|") > 0) {
                    $abl = explode("|", $itemModule->url);
                    foreach ($abl as $ablitem) {
                        Gate::define($ablitem, function (UserModel $user) use ($itemModule) {
                            $rawGrant = DB::table('thseuser')
                                ->select('thseuser.pk_user_id')
                                ->join('mpuser2groupmenu', "mpuser2groupmenu.fk_user_id", "=", "thseuser.pk_user_id")
                                ->join('vgroupmenumoduleaction', "mpuser2groupmenu.fk_groupmenu_id", "=", "vgroupmenumoduleaction.fk_groupmenu_id")
                                ->where('vgroupmenumoduleaction.fk_moduleaction_id', $itemModule->fk_module2action_id)
                                ->where('thseuser.pk_user_id', $user->pk_user_id)->count();
                            return $rawGrant > 0;
                        });
                    }
                } else {
                    if ($itemModule->url != "") {
                        Gate::define($itemModule->url, function (UserModel $user) use ($itemModule) {
                            $rawGrant = DB::table('thseuser')
                                ->select('thseuser.pk_user_id')
                                ->join('mpuser2groupmenu', "mpuser2groupmenu.fk_user_id", "=", "thseuser.pk_user_id")
                                ->join('vgroupmenumoduleaction', "mpuser2groupmenu.fk_groupmenu_id", "=", "vgroupmenumoduleaction.fk_groupmenu_id")
                                ->where('vgroupmenumoduleaction.fk_moduleaction_id', $itemModule->fk_module2action_id)
                                ->where('thseuser.pk_user_id', $user->pk_user_id)->count();
                            return $rawGrant > 0;
                        });
                    }
                }
            }

            foreach ($allowedModules as $itemAllowed) {
                Gate::define($itemAllowed, function () {
                    return true;
                });
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
