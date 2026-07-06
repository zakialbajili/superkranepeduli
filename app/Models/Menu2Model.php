<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu2Model extends Model
{
    use HasFactory;
    protected $table = 'tmenu2';
    protected $primaryKey = 'pk_menu_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}
