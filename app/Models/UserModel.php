<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'tuser2';
    protected $primaryKey = 'pk_user_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $hidden = [
        'password',
        'password2',
    ];
    protected $fillable = [
        'username',
        'email',
        'password',
        'token',
    ];

    public function roles()
    {
        return $this->hasMany(UserRoleModel::class, 'fk_user_id', 'pk_user_id');
    }

    public function employees()
    {
        return $this->belongsTo(EmployeeModel::class,'fk_employee_id','pk_employee_id');
    }
}
