<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountModel extends Model
{
    use HasFactory;
    protected $table = 'taccountdetail';
    protected $primaryKey = 'pk_accountdetail_id';

    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $fillable = [
        "fk_accountbased_id",
        "parent_account_id",
        "parent_account_no",
        "account_no",
        "account_name",
        "description",
        "account_category",
        "account_position",
        "default_balance",
        "actived",
        "created_date",
        "created_by",
        "updated_date",
        "updated_by",
    ];
}