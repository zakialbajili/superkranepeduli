<?php

namespace App\Providers;

use App\Models\UserModel;
use DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // // Should return TRUE or FALSE
        // Gate::define('manage_users', function (UserModel $user) {
        //     return $user->full_name == 'Indra Aditya Renaldi';
        // });

        // $rawModule = DB::table('vmodule2actioninrow')
        //     ->select(['url', 'fk_module2action_id'])
        //     ->get();

        // $allowedModules = [
        //     'admin.storeToken',
        //     'admin.utility.unreadNotification'
        // ];

        // foreach ($allowedModules as $itemAllowed) {
        //     Gate::define($itemAllowed, function () {
        //         return true;
        //     });
        // }
        // foreach ($rawModule as $itemModule) {
        //     if (strpos($itemModule->url, "|") > 0) {
        //         $abl = explode("|", $itemModule->url);
        //         foreach ($abl as $ablitem) {
        //             Gate::define($ablitem, function (UserModel $user) use ($itemModule) {
        //                 $rawGrant = DB::table('tuser2')
        //                     ->join('mpuser2groupmenu', "mpuser2groupmenu.fk_user_id", "=", "tuser2.pk_user_id")
        //                     ->join('vgroupmenumoduleaction', "mpuser2groupmenu.fk_groupmenu_id", "=", "vgroupmenumoduleaction.fk_groupmenu_id")
        //                     ->select('tuser2.pk_user_id')
        //                     ->where('vgroupmenumoduleaction.fk_moduleaction_id', $itemModule->fk_module2action_id)
        //                     ->where('tuser2.pk_user_id', $user->pk_user_id);
        //                 return $rawGrant->count() > 0;
        //             });
        //         }
        //     } else {
        //         Gate::define($itemModule->url, function (UserModel $user) use ($itemModule) {
        //             $rawGrant = DB::table('tuser2')
        //                 ->join('mpuser2groupmenu', "mpuser2groupmenu.fk_user_id", "=", "tuser2.pk_user_id")
        //                 ->join('vgroupmenumoduleaction', "mpuser2groupmenu.fk_groupmenu_id", "=", "vgroupmenumoduleaction.fk_groupmenu_id")
        //                 ->select('tuser2.pk_user_id')
        //                 ->where('vgroupmenumoduleaction.fk_moduleaction_id', $itemModule->fk_module2action_id)
        //                 ->where('tuser2.pk_user_id', $user->pk_user_id);
        //             return $rawGrant->count() > 0;
        //         });
        //     }
        // }
    }
}