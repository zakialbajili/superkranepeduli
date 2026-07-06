<?php
namespace App\Services;

use App\Traits\ModuleTraits;
use DB;
use Exception;
use Log;

class ServiceInventoryManagement
{
    use ModuleTraits;
    public function postingInventoryInStock($date, $noref, $whpartlocationid, $whpartnumberid, $whlocationid, $uomid, $qty, $price, $foreignprice, $rate, $employee_no, $fullName, $additional = [])
    {
        try {
            //breakdown data addtional array untuk mendapatkan parameter yang bukan parameter pokok

            $colAdditional = collect($additional);

            $sourcetype = $colAdditional->get('sourcetype', NULL);
            $sourceid = $colAdditional->get('sourceid', NULL);
            $unit_id = $colAdditional->get('unit_id', NULL);
            $whreqheader_id = $colAdditional->get('whreqheader_id', NULL);
            $whreqitem_id = $colAdditional->get('whreqitem_id', NULL);
            $whorderitem_id = $colAdditional->get('whorderitem_id', NULL);
            $whorderheader_id = $colAdditional->get('whorderheader_id', NULL);
            $whreceiveitem_id = $colAdditional->get('whreceiveitem_id', NULL);
            $whreceiveheader_id = $colAdditional->get('whreceiveheader_id', NULL);
            $whtransmitaldetail_id = $colAdditional->get('whtransmitaldetail_id', NULL);
            $whtransmitalheader_id = $colAdditional->get('whtransmitalheader_id', NULL);
            $whtransferheader_id = $colAdditional->get('whtransferheader_id', NULL);
            $whtransferdetail_id = $colAdditional->get('whtransferdetail_id', NULL);
            $whstockbalancingheader_id = $colAdditional->get('whstockbalancingheader_id', NULL);
            $whstockbalancingdetail_id = $colAdditional->get('whstockbalancingdetail_id', NULL);
            $whitemin_id = $colAdditional->get('whitemin_id', NULL);
            $whitemout_id = $colAdditional->get('whitemout_id', NULL);

            //account hutang 1045 globalparameter
            $glhppid = $this->getGlobalParamTraits(1045);

            //ambil data partnumber untuk mendapatkan Account dari partnumber
            $rawPartnumber = DB::table('twhpartnumber')
                ->select('twhitemnumber.fk_glinternal_id', 'part_no', 'description')
                ->join('twhitemnumber', 'twhpartnumber.fk_whitemnumber_id', '=', 'twhitemnumber.pk_whitemnumber_id')
                ->where('pk_whpartnumber_id', $whpartnumberid)
                ->first();
            $glpartnumberid = 0;
            $part_no = NULL;
            $part_description = '';
            if ($rawPartnumber) {
                $glpartnumberid = $rawPartnumber->fk_glinternal_id;
                $part_no = $rawPartnumber->part_no;
                $part_description = $rawPartnumber->description;
            } else {
                throw new Exception('Partnumber belum disetting GL Internal. Silahkan hubungi Team terkait.');
            }

            $description = $part_no . ' | ' . $part_description . ' | Qty : ' . $qty . ' | ' . $noref . ' | Barang Masuk';

            //ambil data stock partnumber untuk saat ini sesuai warehouse location
            $rawItemBased = DB::table('twhpartnumberlocationbased')
                ->selectRaw('price')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $basedprice = 0;
            if ($rawItemBased) {
                $basedprice = $rawItemBased->price;
            }

            $rawItemin = DB::table('twhitemin')
                ->selectRaw('sum(ifnull(qty,0)*ifnull(qty_ratio,1)) as qty')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $qtyIn = 0;
            if ($rawItemin) {
                $qtyIn = $rawItemin->qty;
            }

            $rawItemOut = DB::table('twhitemout')
                ->selectRaw('sum(ifnull(qty,0)*ifnull(qty_ratio,1)) as qty')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $qtyOut = 0;
            if ($rawItemOut) {
                $qtyOut = $rawItemOut->qty;
            }

            $qtybalance = $qtyIn - $qtyOut;
            $totalBalance = $qtybalance * $basedprice;

            $totalIncoming = $qty * ($price);
            $priceAvg = ($totalBalance + $totalIncoming) / ($qty + $qtybalance);

            //posting ke Warehouse twhitemin
            $whitemin_id = DB::table('twhitemin')
                ->insertGetId([
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_sourcetype_id' => $sourcetype,
                    'fk_source_id' => $sourceid,
                    'part_no' => $part_no,
                    'qty' => $qty,
                    'fk_uom_id' => $uomid,
                    'qty_begin' => $qtybalance,
                    'qty_ratio' => 1,
                    'fk_uom_id_ratio' => $uomid,
                    'qty_last' => $qty + $qtybalance,
                    'created_date' => $date,
                    'created_by' => $employee_no,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'transaction_no' => $noref,
                    'price' => $price,
                    'foreignprice' => $foreignprice,
                    'rate' => $rate,
                ]);

            //posting ke warehouse parameter based untuk memberikan hpp terbaru
            DB::table('twhpartnumberlocationbased')
                ->where('fk_whlocation_id', $whlocationid)
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->update([
                    'price' => $priceAvg,
                    'updated_date' => now(),
                ]);

            //posting ke jurnal untuk masing masing inventory sesuai part number Debet dan hutang Kredit
            $rawInventoryAccount = DB::table('taccountdetail')
                ->select(
                    'account_no',
                    'account_name',
                    'account_category',
                    'account_position',
                    'default_balance',
                )
                ->where('pk_accountdetail_id', $glpartnumberid)
                ->first();
            $account_no = NULL;
            $account_name = NULL;
            $account_category = NULL;
            $account_position = NULL;
            $default_balance = NULL;
            if ($rawInventoryAccount) {
                $account_no = $rawInventoryAccount->account_no;
                $account_name = $rawInventoryAccount->account_name;
                $account_category = $rawInventoryAccount->account_category;
                $account_position = $rawInventoryAccount->account_position;
                $default_balance = $rawInventoryAccount->default_balance;
            } else {
                throw new Exception('Akun Inventory GL Internal tidak ditemukan. Silahkan hubungi Team terkait.');
            }

            DB::table('tjournal')
                ->insert([
                    'journal_date' => $date,
                    'unique_id' => $sourceid,
                    'source_id' => $sourcetype,
                    'fk_sourcetype_id' => $sourcetype,
                    'fk_accountdetail_id' => $glpartnumberid,
                    'account_no' => $account_no,
                    'account_name' => $account_name,
                    'account_category' => $account_category,
                    'account_position' => $account_position,
                    'account_balance' => $default_balance,
                    'description' => $description,
                    'debt' => $totalIncoming,
                    'credit' => 0,
                    'rate' => $rate,
                    'foreignvalue' => $foreignprice,
                    'no_ref' => $noref,
                    'created_date' => now(),
                    'created_by' => $fullName,
                    'fk_unit_id' => $unit_id,
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'fk_whreqheader_id' => $whreqheader_id,
                    'fk_whreqitem_id' => $whreqitem_id,
                    'fk_whorderitem_id' => $whorderitem_id,
                    'fk_whorderheader_id' => $whorderheader_id,
                    'fk_whreceiveitem_id' => $whreceiveitem_id,
                    'fk_whreceiveheader_id' => $whreceiveheader_id,
                    'fk_whtransmitaldetail_id' => $whtransmitaldetail_id,
                    'fk_whtransmitalheader_id' => $whtransmitalheader_id,
                    'fk_whtransferheader_id' => $whtransferheader_id,
                    'fk_whtransferdetail_id' => $whtransferdetail_id,
                    'fk_whstockbalancingheader_id' => $whstockbalancingheader_id,
                    'fk_whstockbalancingdetail_id' => $whstockbalancingdetail_id,
                    'fk_whitemin_id' => $whitemin_id,
                    'fk_whitemout_id' => $whitemout_id,
                ]);


            $rawInventoryAccount = DB::table('taccountdetail')
                ->select(
                    'account_no',
                    'account_name',
                    'account_category',
                    'account_position',
                    'default_balance',
                )
                ->where('pk_accountdetail_id', $glhppid)
                ->first();
            $account_no = NULL;
            $account_name = NULL;
            $account_category = NULL;
            $account_position = NULL;
            $default_balance = NULL;
            if ($rawInventoryAccount) {
                $account_no = $rawInventoryAccount->account_no;
                $account_name = $rawInventoryAccount->account_name;
                $account_category = $rawInventoryAccount->account_category;
                $account_position = $rawInventoryAccount->account_position;
                $default_balance = $rawInventoryAccount->default_balance;
            } else {
                throw new Exception('Akun HPP GL Internal tidak ditemukan. Silahkan hubungi Team terkait.');
            }


            DB::table('tjournal')
                ->insert([
                    'journal_date' => $date,
                    'unique_id' => $sourceid,
                    'source_id' => $sourcetype,
                    'fk_sourcetype_id' => $sourcetype,
                    'fk_accountdetail_id' => $glhppid,
                    'account_no' => $account_no,
                    'account_name' => $account_name,
                    'account_category' => $account_category,
                    'account_position' => $account_position,
                    'account_balance' => $default_balance,
                    'description' => $description,
                    'debt' => 0,
                    'credit' => $totalIncoming,
                    'rate' => $rate,
                    'foreignvalue' => $foreignprice,
                    'no_ref' => $noref,
                    'created_date' => now(),
                    'created_by' => $fullName,
                    'fk_unit_id' => $unit_id,
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'fk_whreqheader_id' => $whreqheader_id,
                    'fk_whreqitem_id' => $whreqitem_id,
                    'fk_whorderitem_id' => $whorderitem_id,
                    'fk_whorderheader_id' => $whorderheader_id,
                    'fk_whreceiveitem_id' => $whreceiveitem_id,
                    'fk_whreceiveheader_id' => $whreceiveheader_id,
                    'fk_whtransmitaldetail_id' => $whtransmitaldetail_id,
                    'fk_whtransmitalheader_id' => $whtransmitalheader_id,
                    'fk_whtransferheader_id' => $whtransferheader_id,
                    'fk_whtransferdetail_id' => $whtransferdetail_id,
                    'fk_whstockbalancingheader_id' => $whstockbalancingheader_id,
                    'fk_whstockbalancingdetail_id' => $whstockbalancingdetail_id,
                    'fk_whitemin_id' => $whitemin_id,
                    'fk_whitemout_id' => $whitemout_id,
                ]);

        } catch (\Throwable $th) {
            log::critical('SUBMITTED - ' . $fullName . ' SOURCE - GLACCOUNT. ');
            throw new Exception('Terjadi Kesalahan Pada Posting Inventory.' . $th->getMessage());
        }
    }
    public function postingInventoryOutStock($date, $noref, $whpartlocationid, $whpartnumberid, $whlocationid, $uomid, $qty, $employee_no, $fullName, $additional = [])
    {
        try {
            //breakdown data addtional array untuk mendapatkan parameter yang bukan parameter pokok

            $colAdditional = collect($additional);

            $sourcetype = $colAdditional->get('sourcetype', NULL);
            $sourceid = $colAdditional->get('sourceid', NULL);
            $whemployee_id = $colAdditional->get('whemployeeid_id', NULL);
            $unit_id = $colAdditional->get('unit_id', NULL);
            $whreqheader_id = $colAdditional->get('whreqheader_id', NULL);
            $whreqitem_id = $colAdditional->get('whreqitem_id', NULL);
            $whorderitem_id = $colAdditional->get('whorderitem_id', NULL);
            $whorderheader_id = $colAdditional->get('whorderheader_id', NULL);
            $whreceiveitem_id = $colAdditional->get('whreceiveitem_id', NULL);
            $whreceiveheader_id = $colAdditional->get('whreceiveheader_id', NULL);
            $whtransmitaldetail_id = $colAdditional->get('whtransmitaldetail_id', NULL);
            $whtransmitalheader_id = $colAdditional->get('whtransmitalheader_id', NULL);
            $whtransferheader_id = $colAdditional->get('whtransferheader_id', NULL);
            $whtransferdetail_id = $colAdditional->get('whtransferdetail_id', NULL);
            $whstockbalancingheader_id = $colAdditional->get('whstockbalancingheader_id', NULL);
            $whstockbalancingdetail_id = $colAdditional->get('whstockbalancingdetail_id', NULL);
            $whitemin_id = $colAdditional->get('whitemin_id', NULL);
            $whitemout_id = $colAdditional->get('whitemout_id', NULL);


            //account hpp 1046 globalparameter
            $glhppid = $this->getGlobalParamTraits(1046);

            //ambil data partnumber untuk mendapatkan Account dari partnumber
            $rawPartnumber = DB::table('twhpartnumber')
                ->select('twhitemnumber.fk_glinternal_id', 'part_no', 'description')
                ->join('twhitemnumber', 'twhpartnumber.fk_whitemnumber_id', '=', 'twhitemnumber.pk_whitemnumber_id')
                ->where('pk_whpartnumber_id', $whpartnumberid)
                ->first();
            $glpartnumberid = 0;
            $part_no = NULL;
            $part_description = '';
            if ($rawPartnumber) {
                $glpartnumberid = $rawPartnumber->fk_glinternal_id;
                $part_no = $rawPartnumber->part_no;
                $part_description = $rawPartnumber->description;
            } else {
                throw new Exception('Partnumber belum disetting GL Internal. Silahkan hubungi Team terkait.');
            }

            $description = $part_no . ' | ' . $part_description . ' | Qty : ' . $qty . ' | ' . $noref . ' | Barang Keluar';

            //ambil data stock partnumber untuk saat ini sesuai warehouse location
            $rawItemBased = DB::table('twhpartnumberlocationbased')
                ->selectRaw('price')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $basedprice = 0;
            if ($rawItemBased) {
                $basedprice = $rawItemBased->price;
            }

            $rawItemin = DB::table('twhitemin')
                ->selectRaw('sum(ifnull(qty,0)*ifnull(qty_ratio,1)) as qty')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $qtyIn = 0;
            if ($rawItemin) {
                $qtyIn = $rawItemin->qty;
            }

            $rawItemOut = DB::table('twhitemout')
                ->selectRaw('sum(ifnull(qty,0)*ifnull(qty_ratio,1)) as qty')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $qtyOut = 0;
            if ($rawItemOut) {
                $qtyOut = $rawItemOut->qty;
            }

            $qtybalance = $qtyIn - $qtyOut;
            $totalBalance = $qtybalance * $basedprice;

            $totaloutprice = $qty * $basedprice;

            //posting ke Warehouse twhitemin
            $whitemin_id = DB::table('twhitemout')
                ->insertGetId([
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_sourcetype_id' => $sourcetype,
                    'fk_source_id' => $sourceid,
                    'part_no' => $part_no,
                    'qty' => $qty,
                    'fk_uom_id' => $uomid,
                    'qty_begin' => $qtybalance,
                    'qty_ratio' => 1,
                    'fk_uom_id_ratio' => $uomid,
                    'qty_last' => $qtybalance - $qty,
                    'created_date' => $date,
                    'created_by' => $employee_no,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'fk_unit_id' => $unit_id,
                    'fk_employee_id' => $whemployee_id,
                    'transmital_no' => $noref,
                    'price' => $basedprice,
                    'foreignprice' => $basedprice,
                    'rate' => 1,
                ]);

            //posting ke jurnal untuk masing masing inventory sesuai part number Debet dan hutang Kredit


            $rawInventoryAccount = DB::table('taccountdetail')
                ->select(
                    'account_no',
                    'account_name',
                    'account_category',
                    'account_position',
                    'default_balance',
                )
                ->where('pk_accountdetail_id', $glhppid)
                ->first();
            $account_no = NULL;
            $account_name = NULL;
            $account_category = NULL;
            $account_position = NULL;
            $default_balance = NULL;
            if ($rawInventoryAccount) {
                $account_no = $rawInventoryAccount->account_no;
                $account_name = $rawInventoryAccount->account_name;
                $account_category = $rawInventoryAccount->account_category;
                $account_position = $rawInventoryAccount->account_position;
                $default_balance = $rawInventoryAccount->default_balance;
            } else {
                throw new Exception('Akun HPP GL Internal tidak ditemukan. Silahkan hubungi Team terkait.');
            }

            $rawInventoryAccount = DB::table('taccountdetail')
                ->select(
                    'account_no',
                    'account_name',
                    'account_category',
                    'account_position',
                    'default_balance',
                )
                ->where('pk_accountdetail_id', $glhppid)
                ->first();
            $account_no = NULL;
            $account_name = NULL;
            $account_category = NULL;
            $account_position = NULL;
            $default_balance = NULL;
            if ($rawInventoryAccount) {
                $account_no = $rawInventoryAccount->account_no;
                $account_name = $rawInventoryAccount->account_name;
                $account_category = $rawInventoryAccount->account_category;
                $account_position = $rawInventoryAccount->account_position;
                $default_balance = $rawInventoryAccount->default_balance;
            } else {
                throw new Exception('Akun Inventory GL Internal tidak ditemukan. Silahkan hubungi Team terkait.');
            }

            DB::table('tjournal')
                ->insert([
                    'journal_date' => $date,
                    'unique_id' => $sourceid,
                    'source_id' => $sourcetype,
                    'fk_sourcetype_id' => $sourcetype,
                    'fk_accountdetail_id' => $glhppid,
                    'account_no' => $account_no,
                    'account_name' => $account_name,
                    'account_category' => $account_category,
                    'account_position' => $account_position,
                    'account_balance' => $default_balance,
                    'description' => $description,
                    'debt' => $totaloutprice,
                    'credit' => 0,
                    'rate' => 1,
                    'foreignvalue' => $basedprice,
                    'no_ref' => $noref,
                    'created_date' => now(),
                    'created_by' => $fullName,
                    'fk_unit_id' => $unit_id,
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'fk_whreqheader_id' => $whreqheader_id,
                    'fk_whreqitem_id' => $whreqitem_id,
                    'fk_whorderitem_id' => $whorderitem_id,
                    'fk_whorderheader_id' => $whorderheader_id,
                    'fk_whreceiveitem_id' => $whreceiveitem_id,
                    'fk_whreceiveheader_id' => $whreceiveheader_id,
                    'fk_whtransmitaldetail_id' => $whtransmitaldetail_id,
                    'fk_whtransmitalheader_id' => $whtransmitalheader_id,
                    'fk_whtransferheader_id' => $whtransferheader_id,
                    'fk_whtransferdetail_id' => $whtransferdetail_id,
                    'fk_whstockbalancingheader_id' => $whstockbalancingheader_id,
                    'fk_whstockbalancingdetail_id' => $whstockbalancingdetail_id,
                    'fk_whitemin_id' => $whitemin_id,
                    'fk_whitemout_id' => $whitemout_id,
                    'fk_employee_id' => $whemployee_id,
                ]);


            $rawInventoryAccount = DB::table('taccountdetail')
                ->select(
                    'account_no',
                    'account_name',
                    'account_category',
                    'account_position',
                    'default_balance',
                )
                ->where('pk_accountdetail_id', $glpartnumberid)
                ->first();
            $account_no = NULL;
            $account_name = NULL;
            $account_category = NULL;
            $account_position = NULL;
            $default_balance = NULL;
            if ($rawInventoryAccount) {
                $account_no = $rawInventoryAccount->account_no;
                $account_name = $rawInventoryAccount->account_name;
                $account_category = $rawInventoryAccount->account_category;
                $account_position = $rawInventoryAccount->account_position;
                $default_balance = $rawInventoryAccount->default_balance;
            } else {
                throw new Exception('Akun HPP GL Internal tidak ditemukan. Silahkan hubungi Team terkait.');
            }


            DB::table('tjournal')
                ->insert([
                    'journal_date' => $date,
                    'unique_id' => $sourceid,
                    'source_id' => $sourcetype,
                    'fk_sourcetype_id' => $sourcetype,
                    'fk_accountdetail_id' => $glpartnumberid,
                    'account_no' => $account_no,
                    'account_name' => $account_name,
                    'account_category' => $account_category,
                    'account_position' => $account_position,
                    'account_balance' => $default_balance,
                    'description' => $description,
                    'debt' => 0,
                    'credit' => $totaloutprice,
                    'rate' => 1,
                    'foreignvalue' => $basedprice,
                    'no_ref' => $noref,
                    'created_date' => now(),
                    'created_by' => $fullName,
                    'fk_unit_id' => $unit_id,
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'fk_whreqheader_id' => $whreqheader_id,
                    'fk_whreqitem_id' => $whreqitem_id,
                    'fk_whorderitem_id' => $whorderitem_id,
                    'fk_whorderheader_id' => $whorderheader_id,
                    'fk_whreceiveitem_id' => $whreceiveitem_id,
                    'fk_whreceiveheader_id' => $whreceiveheader_id,
                    'fk_whtransmitaldetail_id' => $whtransmitaldetail_id,
                    'fk_whtransmitalheader_id' => $whtransmitalheader_id,
                    'fk_whtransferheader_id' => $whtransferheader_id,
                    'fk_whtransferdetail_id' => $whtransferdetail_id,
                    'fk_whstockbalancingheader_id' => $whstockbalancingheader_id,
                    'fk_whstockbalancingdetail_id' => $whstockbalancingdetail_id,
                    'fk_whitemin_id' => $whitemin_id,
                    'fk_whitemout_id' => $whitemout_id,
                    'fk_employee_id' => $whemployee_id,
                ]);

        } catch (\Throwable $th) {
            log::critical('SUBMITTED - ' . $fullName . ' SOURCE - GLACCOUNT. ');
            throw new Exception('Terjadi Kesalahan Pada Posting Inventory' . $th->getMessage());
        }
    }

    public function postingInventoryRejectInStock($date, $noref, $whpartlocationid, $whpartnumberid, $whlocationid, $uomid, $qty, $price, $foreignprice, $rate, $employee_no, $fullName, $additional = [])
    {
        try {
            //breakdown data addtional array untuk mendapatkan parameter yang bukan parameter pokok

            $colAdditional = collect($additional);

            $sourcetype = $colAdditional->get('sourcetype', NULL);
            $sourceid = $colAdditional->get('sourceid', NULL);
            $unit_id = $colAdditional->get('unit_id', NULL);
            $whreqheader_id = $colAdditional->get('whreqheader_id', NULL);
            $whreqitem_id = $colAdditional->get('whreqitem_id', NULL);
            $whorderitem_id = $colAdditional->get('whorderitem_id', NULL);
            $whorderheader_id = $colAdditional->get('whorderheader_id', NULL);
            $whreceiveitem_id = $colAdditional->get('whreceiveitem_id', NULL);
            $whreceiveheader_id = $colAdditional->get('whreceiveheader_id', NULL);
            $whtransmitaldetail_id = $colAdditional->get('whtransmitaldetail_id', NULL);
            $whtransmitalheader_id = $colAdditional->get('whtransmitalheader_id', NULL);
            $whtransferheader_id = $colAdditional->get('whtransferheader_id', NULL);
            $whtransferdetail_id = $colAdditional->get('whtransferdetail_id', NULL);
            $whstockbalancingheader_id = $colAdditional->get('whstockbalancingheader_id', NULL);
            $whstockbalancingdetail_id = $colAdditional->get('whstockbalancingdetail_id', NULL);
            $whitemin_id = $colAdditional->get('whitemin_id', NULL);
            $whitemout_id = $colAdditional->get('whitemout_id', NULL);

            //account hutang 1045 globalparameter
            $glhppid = $this->getGlobalParamTraits(1045);

            //ambil data partnumber untuk mendapatkan Account dari partnumber
            $rawPartnumber = DB::table('twhpartnumber')
                ->select('twhitemnumber.fk_glinternal_id', 'part_no', 'description')
                ->join('twhitemnumber', 'twhpartnumber.fk_whitemnumber_id', '=', 'twhitemnumber.pk_whitemnumber_id')
                ->where('pk_whpartnumber_id', $whpartnumberid)
                ->first();
            $glpartnumberid = 0;
            $part_no = NULL;
            $part_description = '';
            if ($rawPartnumber) {
                $glpartnumberid = $rawPartnumber->fk_glinternal_id;
                $part_no = $rawPartnumber->part_no;
                $part_description = $rawPartnumber->description;
            } else {
                throw new Exception('Partnumber belum disetting GL Internal. Silahkan hubungi Team terkait.');
            }

            $description = $part_no . ' | ' . $part_description . ' | Qty : ' . $qty . ' | ' . $noref . ' | Pembatalan Barang Masuk';

            //ambil data stock partnumber untuk saat ini sesuai warehouse location
            $rawItemBased = DB::table('twhpartnumberlocationbased')
                ->selectRaw('price')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $basedprice = 0;
            if ($rawItemBased) {
                $basedprice = $rawItemBased->price;
            }

            $rawItemin = DB::table('twhitemin')
                ->selectRaw('sum(ifnull(qty,0)*ifnull(qty_ratio,1)) as qty')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $qtyIn = 0;
            if ($rawItemin) {
                $qtyIn = $rawItemin->qty;
            }

            $rawItemOut = DB::table('twhitemout')
                ->selectRaw('sum(ifnull(qty,0)*ifnull(qty_ratio,1)) as qty')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $qtyOut = 0;
            if ($rawItemOut) {
                $qtyOut = $rawItemOut->qty;
            }

            $qtybalance = $qtyIn - $qtyOut;
            $totalBalance = $qtybalance * $basedprice;

            $totalIncoming = $qty * ($price);
            if (($qtybalance - $qty) == 0) {
                $priceAvg = $basedprice;
            } else {
                $priceAvg = ($totalBalance - $totalIncoming) / ($qtybalance - $qty);
            }


            //posting ke Warehouse twhitemout
            $whitemin_id = DB::table('twhitemout')
                ->insertGetId([
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_sourcetype_id' => $sourcetype,
                    'fk_source_id' => $sourceid,
                    'part_no' => $part_no,
                    'qty' => $qty,
                    'fk_uom_id' => $uomid,
                    'qty_begin' => $qtybalance,
                    'qty_ratio' => 1,
                    'fk_uom_id_ratio' => $uomid,
                    'qty_last' => $qtybalance - $qty,
                    'created_date' => now(),
                    'created_by' => $employee_no,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'transmital_no' => $noref,
                    'price' => $price,
                    'foreignprice' => $foreignprice,
                    'rate' => $rate,
                ]);

            //posting ke warehouse parameter based untuk memberikan hpp terbaru
            DB::table('twhpartnumberlocationbased')
                ->where('fk_whlocation_id', $whlocationid)
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->update([
                    'price' => $priceAvg,
                    'updated_date' => now(),
                ]);

            //posting ke jurnal untuk masing masing inventory sesuai hutang debet dan part number Kredit
            $rawInventoryAccount = DB::table('taccountdetail')
                ->select(
                    'account_no',
                    'account_name',
                    'account_category',
                    'account_position',
                    'default_balance',
                )
                ->where('pk_accountdetail_id', $glhppid)
                ->first();
            $account_no = NULL;
            $account_name = NULL;
            $account_category = NULL;
            $account_position = NULL;
            $default_balance = NULL;
            if ($rawInventoryAccount) {
                $account_no = $rawInventoryAccount->account_no;
                $account_name = $rawInventoryAccount->account_name;
                $account_category = $rawInventoryAccount->account_category;
                $account_position = $rawInventoryAccount->account_position;
                $default_balance = $rawInventoryAccount->default_balance;
            } else {
                throw new Exception('Akun HPP GL Internal tidak ditemukan. Silahkan hubungi Team terkait.');
            }


            DB::table('tjournal')
                ->insert([
                    'journal_date' => $date,
                    'unique_id' => $sourceid,
                    'source_id' => $sourcetype,
                    'fk_sourcetype_id' => $sourcetype,
                    'fk_accountdetail_id' => $glhppid,
                    'account_no' => $account_no,
                    'account_name' => $account_name,
                    'account_category' => $account_category,
                    'account_position' => $account_position,
                    'account_balance' => $default_balance,
                    'description' => $description,
                    'credit' => 0,
                    'debt' => $totalIncoming,
                    'rate' => $rate,
                    'foreignvalue' => $foreignprice,
                    'no_ref' => $noref,
                    'created_date' => now(),
                    'created_by' => $fullName,
                    'fk_unit_id' => $unit_id,
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'fk_whreqheader_id' => $whreqheader_id,
                    'fk_whreqitem_id' => $whreqitem_id,
                    'fk_whorderitem_id' => $whorderitem_id,
                    'fk_whorderheader_id' => $whorderheader_id,
                    'fk_whreceiveitem_id' => $whreceiveitem_id,
                    'fk_whreceiveheader_id' => $whreceiveheader_id,
                    'fk_whtransmitaldetail_id' => $whtransmitaldetail_id,
                    'fk_whtransmitalheader_id' => $whtransmitalheader_id,
                    'fk_whtransferheader_id' => $whtransferheader_id,
                    'fk_whtransferdetail_id' => $whtransferdetail_id,
                    'fk_whstockbalancingheader_id' => $whstockbalancingheader_id,
                    'fk_whstockbalancingdetail_id' => $whstockbalancingdetail_id,
                    'fk_whitemin_id' => $whitemin_id,
                    'fk_whitemout_id' => $whitemout_id,
                ]);

            $rawInventoryAccount = DB::table('taccountdetail')
                ->select(
                    'account_no',
                    'account_name',
                    'account_category',
                    'account_position',
                    'default_balance',
                )
                ->where('pk_accountdetail_id', $glpartnumberid)
                ->first();
            $account_no = NULL;
            $account_name = NULL;
            $account_category = NULL;
            $account_position = NULL;
            $default_balance = NULL;
            if ($rawInventoryAccount) {
                $account_no = $rawInventoryAccount->account_no;
                $account_name = $rawInventoryAccount->account_name;
                $account_category = $rawInventoryAccount->account_category;
                $account_position = $rawInventoryAccount->account_position;
                $default_balance = $rawInventoryAccount->default_balance;
            } else {
                throw new Exception('Akun Inventory GL Internal tidak ditemukan. Silahkan hubungi Team terkait.');
            }

            DB::table('tjournal')
                ->insert([
                    'journal_date' => $date,
                    'unique_id' => $sourceid,
                    'source_id' => $sourcetype,
                    'fk_sourcetype_id' => $sourcetype,
                    'fk_accountdetail_id' => $glpartnumberid,
                    'account_no' => $account_no,
                    'account_name' => $account_name,
                    'account_category' => $account_category,
                    'account_position' => $account_position,
                    'account_balance' => $default_balance,
                    'description' => $description,
                    'credit' => $totalIncoming,
                    'debt' => 0,
                    'rate' => $rate,
                    'foreignvalue' => $foreignprice,
                    'no_ref' => $noref,
                    'created_date' => now(),
                    'created_by' => $fullName,
                    'fk_unit_id' => $unit_id,
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'fk_whreqheader_id' => $whreqheader_id,
                    'fk_whreqitem_id' => $whreqitem_id,
                    'fk_whorderitem_id' => $whorderitem_id,
                    'fk_whorderheader_id' => $whorderheader_id,
                    'fk_whreceiveitem_id' => $whreceiveitem_id,
                    'fk_whreceiveheader_id' => $whreceiveheader_id,
                    'fk_whtransmitaldetail_id' => $whtransmitaldetail_id,
                    'fk_whtransmitalheader_id' => $whtransmitalheader_id,
                    'fk_whtransferheader_id' => $whtransferheader_id,
                    'fk_whtransferdetail_id' => $whtransferdetail_id,
                    'fk_whstockbalancingheader_id' => $whstockbalancingheader_id,
                    'fk_whstockbalancingdetail_id' => $whstockbalancingdetail_id,
                    'fk_whitemin_id' => $whitemin_id,
                    'fk_whitemout_id' => $whitemout_id,
                ]);


        } catch (\Throwable $th) {
            log::critical('SUBMITTED - ' . $fullName . ' SOURCE - GLACCOUNT. ');
            throw new Exception('Terjadi Kesalahan Pada Posting Inventory');
        }
    }

    public function postingInventoryRejectOutStock($date, $noref, $whpartlocationid, $whpartnumberid, $whlocationid, $uomid, $qty, $employee_no, $fullName, $additional = [])
    {
        try {
            //breakdown data addtional array untuk mendapatkan parameter yang bukan parameter pokok

            $colAdditional = collect($additional);

            $sourcetype = $colAdditional->get('sourcetype', NULL);
            $sourceid = $colAdditional->get('sourceid', NULL);
            $whemployee_id = $colAdditional->get('whemployeeid_id', NULL);
            $unit_id = $colAdditional->get('unit_id', NULL);
            $whreqheader_id = $colAdditional->get('whreqheader_id', NULL);
            $whreqitem_id = $colAdditional->get('whreqitem_id', NULL);
            $whorderitem_id = $colAdditional->get('whorderitem_id', NULL);
            $whorderheader_id = $colAdditional->get('whorderheader_id', NULL);
            $whreceiveitem_id = $colAdditional->get('whreceiveitem_id', NULL);
            $whreceiveheader_id = $colAdditional->get('whreceiveheader_id', NULL);
            $whtransmitaldetail_id = $colAdditional->get('whtransmitaldetail_id', NULL);
            $whtransmitalheader_id = $colAdditional->get('whtransmitalheader_id', NULL);
            $whtransferheader_id = $colAdditional->get('whtransferheader_id', NULL);
            $whtransferdetail_id = $colAdditional->get('whtransferdetail_id', NULL);
            $whstockbalancingheader_id = $colAdditional->get('whstockbalancingheader_id', NULL);
            $whstockbalancingdetail_id = $colAdditional->get('whstockbalancingdetail_id', NULL);
            $whitemin_id = $colAdditional->get('whitemin_id', NULL);
            $whitemout_id = $colAdditional->get('whitemout_id', NULL);


            //account hpp 1046 globalparameter
            $glhppid = $this->getGlobalParamTraits(1046);

            //ambil data partnumber untuk mendapatkan Account dari partnumber
            $rawPartnumber = DB::table('twhpartnumber')
                ->select('twhitemnumber.fk_glinternal_id', 'part_no', 'description')
                ->join('twhitemnumber', 'twhpartnumber.fk_whitemnumber_id', '=', 'twhitemnumber.pk_whitemnumber_id')
                ->where('pk_whpartnumber_id', $whpartnumberid)
                ->first();
            $glpartnumberid = 0;
            $part_no = NULL;
            $part_description = '';
            if ($rawPartnumber) {
                $glpartnumberid = $rawPartnumber->fk_glinternal_id;
                $part_no = $rawPartnumber->part_no;
                $part_description = $rawPartnumber->description;
            } else {
                throw new Exception('Partnumber belum disetting GL Internal. Silahkan hubungi Team terkait.');
            }

            $description = $part_no . ' | ' . $part_description . ' | Qty : ' . $qty . ' | ' . $noref . ' | Pembatalan Barang Keluar';

            //ambil data stock partnumber untuk saat ini sesuai warehouse location
            $rawItemBased = DB::table('twhpartnumberlocationbased')
                ->selectRaw('price')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $basedprice = 0;
            if ($rawItemBased) {
                $basedprice = $rawItemBased->price;
            }

            $rawItemin = DB::table('twhitemin')
                ->selectRaw('sum(ifnull(qty,0)*ifnull(qty_ratio,1)) as qty')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $qtyIn = 0;
            if ($rawItemin) {
                $qtyIn = $rawItemin->qty;
            }

            $rawItemOut = DB::table('twhitemout')
                ->selectRaw('sum(ifnull(qty,0)*ifnull(qty_ratio,1)) as qty')
                ->where('fk_whpartnumber_id', $whpartnumberid)
                ->where('fk_whlocation_id', $whlocationid)
                ->first();
            $qtyOut = 0;
            if ($rawItemOut) {
                $qtyOut = $rawItemOut->qty;
            }

            $qtybalance = $qtyIn - $qtyOut;
            $totalBalance = $qtybalance * $basedprice;

            $totaloutprice = $qty * $basedprice;

            //posting ke Warehouse twhitemin
            $whitemin_id = DB::table('twhitemin')
                ->insertGetId([
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_sourcetype_id' => $sourcetype,
                    'fk_source_id' => $sourceid,
                    'part_no' => $part_no,
                    'qty' => $qty,
                    'fk_uom_id' => $uomid,
                    'qty_begin' => $qtybalance,
                    'qty_ratio' => 1,
                    'fk_uom_id_ratio' => $uomid,
                    'qty_last' => $qtybalance + $qty,
                    'created_date' => $date,
                    'created_by' => $employee_no,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'transaction_no' => $noref,
                    'price' => $basedprice,
                    'foreignprice' => $basedprice,
                    'rate' => 1,
                ]);

            //posting ke jurnal untuk masing masing inventory sesuai part number Debet dan hutang Kredit
            $rawInventoryAccount = DB::table('taccountdetail')
                ->select(
                    'account_no',
                    'account_name',
                    'account_category',
                    'account_position',
                    'default_balance',
                )
                ->where('pk_accountdetail_id', $glpartnumberid)
                ->first();
            $account_no = NULL;
            $account_name = NULL;
            $account_category = NULL;
            $account_position = NULL;
            $default_balance = NULL;
            if ($rawInventoryAccount) {
                $account_no = $rawInventoryAccount->account_no;
                $account_name = $rawInventoryAccount->account_name;
                $account_category = $rawInventoryAccount->account_category;
                $account_position = $rawInventoryAccount->account_position;
                $default_balance = $rawInventoryAccount->default_balance;
            } else {
                throw new Exception('Akun HPP GL Internal tidak ditemukan. Silahkan hubungi Team terkait.');
            }


            DB::table('tjournal')
                ->insert([
                    'journal_date' => $date,
                    'fk_sourcetype_id' => $sourcetype,
                    'unique_id' => $sourceid,
                    'source_id' => $sourceid,
                    'fk_accountdetail_id' => $glpartnumberid,
                    'account_no' => $account_no,
                    'account_name' => $account_name,
                    'account_category' => $account_category,
                    'account_position' => $account_position,
                    'account_balance' => $default_balance,
                    'description' => $description,
                    'debt' => $totaloutprice,
                    'credit' => 0,
                    'rate' => 1,
                    'foreignvalue' => $basedprice,
                    'no_ref' => $noref,
                    'created_date' => now(),
                    'created_by' => $fullName,
                    'fk_unit_id' => $unit_id,
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'fk_whreqheader_id' => $whreqheader_id,
                    'fk_whreqitem_id' => $whreqitem_id,
                    'fk_whorderitem_id' => $whorderitem_id,
                    'fk_whorderheader_id' => $whorderheader_id,
                    'fk_whreceiveitem_id' => $whreceiveitem_id,
                    'fk_whreceiveheader_id' => $whreceiveheader_id,
                    'fk_whtransmitaldetail_id' => $whtransmitaldetail_id,
                    'fk_whtransmitalheader_id' => $whtransmitalheader_id,
                    'fk_whtransferheader_id' => $whtransferheader_id,
                    'fk_whtransferdetail_id' => $whtransferdetail_id,
                    'fk_whstockbalancingheader_id' => $whstockbalancingheader_id,
                    'fk_whstockbalancingdetail_id' => $whstockbalancingdetail_id,
                    'fk_whitemin_id' => $whitemin_id,
                    'fk_whitemout_id' => $whitemout_id,
                    'fk_employee_id' => $whemployee_id,
                ]);

            $rawInventoryAccount = DB::table('taccountdetail')
                ->select(
                    'account_no',
                    'account_name',
                    'account_category',
                    'account_position',
                    'default_balance',
                )
                ->where('pk_accountdetail_id', $glhppid)
                ->first();
            $account_no = NULL;
            $account_name = NULL;
            $account_category = NULL;
            $account_position = NULL;
            $default_balance = NULL;
            if ($rawInventoryAccount) {
                $account_no = $rawInventoryAccount->account_no;
                $account_name = $rawInventoryAccount->account_name;
                $account_category = $rawInventoryAccount->account_category;
                $account_position = $rawInventoryAccount->account_position;
                $default_balance = $rawInventoryAccount->default_balance;
            } else {
                throw new Exception('Akun HPP GL Internal tidak ditemukan. Silahkan hubungi Team terkait.');
            }

            $rawInventoryAccount = DB::table('taccountdetail')
                ->select(
                    'account_no',
                    'account_name',
                    'account_category',
                    'account_position',
                    'default_balance',
                )
                ->where('pk_accountdetail_id', $glhppid)
                ->first();
            $account_no = NULL;
            $account_name = NULL;
            $account_category = NULL;
            $account_position = NULL;
            $default_balance = NULL;
            if ($rawInventoryAccount) {
                $account_no = $rawInventoryAccount->account_no;
                $account_name = $rawInventoryAccount->account_name;
                $account_category = $rawInventoryAccount->account_category;
                $account_position = $rawInventoryAccount->account_position;
                $default_balance = $rawInventoryAccount->default_balance;
            } else {
                throw new Exception('Akun Inventory GL Internal tidak ditemukan. Silahkan hubungi Team terkait.');
            }

            DB::table('tjournal')
                ->insert([
                    'journal_date' => $date,
                    'fk_sourcetype_id' => $sourcetype,
                    'unique_id' => $sourceid,
                    'source_id' => $sourceid,
                    'fk_accountdetail_id' => $glhppid,
                    'account_no' => $account_no,
                    'account_name' => $account_name,
                    'account_category' => $account_category,
                    'account_position' => $account_position,
                    'account_balance' => $default_balance,
                    'description' => $description,
                    'debt' => 0,
                    'credit' => $totaloutprice,
                    'rate' => 1,
                    'foreignvalue' => $basedprice,
                    'no_ref' => $noref,
                    'created_date' => now(),
                    'created_by' => $fullName,
                    'fk_unit_id' => $unit_id,
                    'fk_whpartnumber_id' => $whpartnumberid,
                    'fk_whpartnumberlocation_id' => $whpartlocationid,
                    'fk_whlocation_id' => $whlocationid,
                    'fk_whreqheader_id' => $whreqheader_id,
                    'fk_whreqitem_id' => $whreqitem_id,
                    'fk_whorderitem_id' => $whorderitem_id,
                    'fk_whorderheader_id' => $whorderheader_id,
                    'fk_whreceiveitem_id' => $whreceiveitem_id,
                    'fk_whreceiveheader_id' => $whreceiveheader_id,
                    'fk_whtransmitaldetail_id' => $whtransmitaldetail_id,
                    'fk_whtransmitalheader_id' => $whtransmitalheader_id,
                    'fk_whtransferheader_id' => $whtransferheader_id,
                    'fk_whtransferdetail_id' => $whtransferdetail_id,
                    'fk_whstockbalancingheader_id' => $whstockbalancingheader_id,
                    'fk_whstockbalancingdetail_id' => $whstockbalancingdetail_id,
                    'fk_whitemin_id' => $whitemin_id,
                    'fk_whitemout_id' => $whitemout_id,
                    'fk_employee_id' => $whemployee_id,
                ]);

        } catch (\Throwable $th) {
            log::critical('SUBMITTED - ' . $fullName . ' SOURCE - GLACCOUNT. ');
            throw new Exception('Terjadi Kesalahan Pada Posting Inventory' . $th->getMessage());
        }
    }
}
