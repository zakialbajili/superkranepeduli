<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMenu2ModuleModel extends Model
{
    use HasFactory;
    protected $table = 'mpgroupmenu2module';
    protected $primaryKey = 'pk_groupmenumodule_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}