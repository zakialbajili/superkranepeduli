<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleApproval2Model extends Model
{
    use HasFactory;
    protected $table = 'tmodule2approval';
    protected $primaryKey = 'pk_moduleapproval_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    public function ProjectMaster()
    {
        return $this->belongsTo(ProjectMasterModel::class, 'fk_status_id', 'pk_projectmaster_id');
    }
}