<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMenuModel extends Model
{
    use HasFactory;
    protected $table = 'tgroupmenu2';
    protected $primaryKey = 'pk_groupmenu_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}
