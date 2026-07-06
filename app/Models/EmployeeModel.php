<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeModel extends Model
{
    use HasFactory;
    protected $table = 'temployee';
    protected $primaryKey = 'pk_employee_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}
