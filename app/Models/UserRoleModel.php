<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoleModel extends Model
{
    use HasFactory;
    protected $table = 'mpuser2role';
    protected $primaryKey = 'pk_userrole_id';
    public $timestamps = false;
}
