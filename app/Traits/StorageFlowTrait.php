<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;
use DB;

trait StorageFlowTrait
{
    private $keys = [
        "fk_purchasereceivedetail_id",
        "fk_purchasedetail_id",
        "fk_purchasereturndetail_id",
        "fk_salesreceivedetail_id",
        "fk_salesdetail_id",
        "fk_salesreturndetail_id",
    ];

    private $sourcetables = [
        "tpurchasereceivedetail" => [
            "sourcetype_id" => 2,
            "hpp_relation_table" => "tpurchaseorderdetail",
            "hpp_relation_foreignkey" => "fk_purchaseorderdetail_id",
            "hpp_relation_primarykey" => "pk_purchaseorderdetail_id",
        ],
    ];
    public function Incoming($transaction_date, $sourcetable, $sourcecolumn, $sourcekey, $sourceid)
    {
        array_push($sourcecolumn, $this->sourcetables[$sourcetable]["hpp_relation_foreignkey"]);
        $fullName = Auth::user()->full_name;
        $keysfound = "";
        try {
            $rawdetail = DB::table($sourcetable)
                ->select($sourcecolumn)
                ->where($sourcekey, $sourceid)
                ->get();

            foreach ($rawdetail as $tableitem) {

                $qty = 0;
                $fk_productstorage_id = "";
                $fk_warehouse_id = "";
                $fk_product_id = "";
                $fk_hppsource_id = "";
                $detail_id = "";
                $price = 0;
                $currenthpp = 0;
                $hpp = 0;

                foreach ($tableitem as $key => $value) {
                    if (array_search($key, $this->keys) >= 0) {
                        $keysfound = $this->keys[array_search($key, $this->keys)];
                    }

                    if ($key == "fk_productstorage_id") {
                        $fk_productstorage_id = $value;
                    }
                    if ($key == "qty") {
                        $qty = floatval($value);
                    }
                    if ($key == "fk_warehouse_id") {
                        $fk_warehouse_id = $value;
                    }
                    if ($key == "fk_product_id") {
                        $fk_product_id = $value;
                    }
                    if ($key == $keysfound) {
                        $detail_id = $value;
                    }
                    if ($key == $this->sourcetables[$sourcetable]["hpp_relation_foreignkey"]) {
                        $fk_hppsource_id = $value;
                    }
                }

                if ($keysfound == "") {
                    goto nextdata;
                }

                $balance = DB::table('vproductbalance')
                    ->select('balance', 'hpp', 'fk_measurement_id', 'is_stored')
                    ->where('pk_product_id', $fk_product_id)
                    ->first();
                $qty_start = $balance->balance;
                $measurement = $balance->fk_measurement_id;
                $currenthpp = $balance->hpp;

                if ($balance->is_stored == 0) {
                    goto nextdata;
                }


                $hppdata = DB::table('tpurchaseorderdetail')
                    ->select('price')
                    ->where($this->sourcetables[$sourcetable]["hpp_relation_primarykey"], $fk_hppsource_id)
                    ->first();

                if ($hppdata != null) {
                    $price = $hppdata->price;
                }
                $hpp = ((floatval($currenthpp) * floatval($qty_start)) + (floatval($price) * floatval($qty))) / (floatval($qty_start) + floatval($qty));
                $incomingItem = [
                    "transaction_date" => $transaction_date,
                    "source_type" => $this->sourcetables[$sourcetable]["sourcetype_id"],
                    "unique_id" => $sourceid,
                    "pk_productstorage_id" => $fk_productstorage_id,
                    "fk_product_id" => $fk_product_id,
                    "fk_warehouse_id" => $fk_warehouse_id,
                    "qty" => $qty,
                    "fk_measurement_id" => $measurement,
                    "hpp" => $hpp,
                    "qty_start" => $qty_start,
                    "qty_in" => $qty,
                    "qty_balance" => $qty_start + $qty,
                    "notes" => "",
                    $keysfound => $detail_id,
                    "created_date" => now(),
                    "created_by" => $fullName
                ];

                DB::table('tstorageflow')
                    ->insert($incomingItem);

                DB::table('tproduct')
                    ->where('pk_product_id', $fk_product_id)
                    ->update([
                        'hpp' => $hpp
                    ]);

                nextdata:
            }
            return true;
        } catch (\Throwable $th) {
            return false;
        }

    }
}