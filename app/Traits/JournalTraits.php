<?php
namespace App\Traits;

use App\Models\AccountModel;
use DB;
use Illuminate\Support\Facades\Auth;

trait JournalTraits
{
    public function insertJournal($journal_date, $source_id, $unique_id, $accountdetail_id, $debt, $credit, $description, $created_by)
    {
        try {

            $itemAccount = AccountModel::where('pk_accountdetail_id', $accountdetail_id)->first();
            if ($itemAccount == null) {
                return "FAILED";
            }
            $account_no = $itemAccount->account_no;
            $account_category = $itemAccount->account_category;
            $account_name = $itemAccount->account_name;
            $account_position = $itemAccount->account_position;
            $account_balance = $itemAccount->default_balance;

            DB::table("tjournal")->insert([
                "unique_id" => $unique_id,
                "source_id" => $source_id,
                "fk_accountdetail_id" => $accountdetail_id,
                "journal_date" => $journal_date,
                "account_no" => $account_no,
                "account_name" => $account_name,
                "account_category" => $account_category,
                "account_position" => $account_position,
                "account_balance" => $account_balance,
                "debt" => $debt,
                "credit" => $credit,
                "description" => $description,
                "created_date" => now(),
                "created_by" => $created_by,
            ]);
            return "SUCCESS";
        } catch (\Throwable $th) {
            return "FAILED";
        }
    }
}
