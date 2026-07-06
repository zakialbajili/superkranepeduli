<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleModel extends Model
{
    use HasFactory;
    protected $table = 'tmodule2';
    protected $primaryKey = 'pk_module_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    public function Module2Approvals(){
        return $this->hasMany(ModuleApproval2Model::class,"fk_module_id","pk_module_id");
    }
}
