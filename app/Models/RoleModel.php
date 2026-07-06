<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleModel extends Model
{
    use HasFactory;
    protected $table = 'trole2';
    protected $primaryKey = 'pk_role_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}
