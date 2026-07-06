<?php
namespace App\Services;

use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class ServiceAccurateCustomer
{
    public function insertToApp($event)
    {
        try {

            $id = $event['aolid'];

            $authdata = getAuthAcc();
            if (!isset($authdata['d']['database']['host'])) {
                return;
            }
            $host = $authdata['d']['database']['host'];

            $date = Carbon::now()->format('d/m/Y H:i:s');
            $hash = hash_hmac('SHA256', $date, (config('app.ACC_KEY')));

            $response = Http::withToken((config('app.ACC_TOKEN')))
                ->withHeaders([
                    'X-Api-Timestamp' => $date,
                    'X-Api-Signature' => $hash
                ])->get($host . '/accurate/api/customer/detail.do', ["id" => $id]);

            $dataresponse = json_decode($response->body(), true);
            $responsebody = '';
            if (isset($dataresponse['d']['customerNo'])) {
                $responsebody = $dataresponse['d'];
            } else {
                return;
            }

            DB::beginTransaction();

            $aolprheader = [
                'id' => $id,
                'customerNo' => $responsebody['customerNo'],
                'customerTaxTypeName' => $responsebody['customerTaxTypeName'],
                'npwpNo' => $responsebody['npwpNo'],
                'name' => $responsebody['name'],
                'shipStreet' => $responsebody['shipStreet'],
                'lastUpdate' => Carbon::createFromFormat('d/m/Y H:i:s', $responsebody['lastUpdate'])->format('Y-m-d H:i:s'),
                'currencyCode' => data_get($responsebody, 'currency.code', NULL),
                'created_date' => now(),
            ];

            $data = DB::table('taolcustomer')
                ->where("id", $id)
                ->first();
            if ($data) {
                DB::table('taolcustomer')
                    ->where("id", $id)
                    ->update($aolprheader);
            } else {
                DB::table('taolcustomer')
                    ->insert($aolprheader);
            }

            foreach ($responsebody['detailOpenBalance'] as $detailItem) {
                $payload='{"databaseId":1378095,"type":"SALES_INVOICE","data":[{"salesInvoiceId":' . $detailItem['salesInvoiceId'] . ',"salesInvoiceNo":"' . $detailItem['number'] . '","action":"WRITE"}]}';
                DB::table('aolwebhook')
                    ->insert([
                        'type' => 'SALES_INVOICE',
                        'payload' =>$payload,
                        'created_at' => now(),
                        'status' => 'NEW',
                        'retry' => 1,
                    ]);

            }

            DB::table('tlogaccurate')
                ->insert([
                    "source" => "CUSTOMER",
                    "response" => json_encode($dataresponse, true),
                    "actiontype" => "SAVE",
                    "created_by" => $event['fullName'],
                    "created_date" => now(),
                ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            log::critical('INSERT TO APP - ' . $event['fullName'] . ' SOURCE - PURCHASE INVOICE. ' . $th->getMessage());
            throw new \Exception($th);
        }
    }
}
