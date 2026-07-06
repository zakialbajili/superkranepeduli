<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMasterModel extends Model
{
    use HasFactory;
    protected $table = 'tprojectmaster';
    protected $primaryKey = 'pk_projectmaster_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}
