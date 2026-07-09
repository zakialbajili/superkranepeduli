<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'temployee';
    protected $primaryKey = 'employee_no';
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $hidden = ['birth_date'];
}
