<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountBasedModel extends Model
{
    use HasFactory;
    protected $table = 'taccbased';
    protected $primaryKey = 'pk_accbased_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}