<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroupMenuModel extends Model
{
    use HasFactory;
    protected $table = 'mpuser2groupmenu';
    protected $primaryKey = 'pk_usergroupmenu_id';
    public $timestamps = false;
}
