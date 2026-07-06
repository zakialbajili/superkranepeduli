<?php
namespace App\Services;

use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class AolWebhookItemHandler
{
    public function insertToApp($event)
    {
        try {
            $accId = $event;

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
                ])->get($host . '/accurate/api/item/detail.do', ["id" => $event]);

            $dataresponse = json_decode($response->body(), true);

            $unit1name = NULL;
            $no = NULL;
            $itemTypeName = NULL;
            $notes = '';
            $cogsGlAccountId = NULL;
            $name = NULL;
            $itemCategory = NULL;


            if (isset($dataresponse['d']['unit1Name'])) {
                $unit1name = $dataresponse['d']['unit1Name'];
            }
            if (isset($dataresponse['d']['no'])) {
                $no = $dataresponse['d']['no'];
            }
            if (isset($dataresponse['d']['itemTypeName'])) {
                $itemTypeName = $dataresponse['d']['itemTypeName'];
            }
            if (isset($dataresponse['d']['notes'])) {
                $notes = $dataresponse['d']['notes'];
            }
            if (isset($dataresponse['d']['cogsGlAccountId'])) {
                $cogsGlAccountId = $dataresponse['d']['cogsGlAccountId'];
            }
            if (isset($dataresponse['d']['name'])) {
                $name = $dataresponse['d']['name'];
            }
            if (isset($dataresponse['d']['name'])) {
                $itemCategory = $dataresponse['d']['itemCategory']['name'];
            }

            $rawaolItem = DB::table('taolitem')
                ->where("accId", $accId)
                ->first();
            if ($rawaolItem) {
                DB::table('taolitem')
                    ->where("accId", $accId)
                    ->update([
                        "no" => $no,
                        "unit1name" => $unit1name,
                        "itemTypeName" => $itemTypeName,
                        "notes" => $notes,
                        "cogsGlAccountId" => $cogsGlAccountId,
                        "itemCategory" => $itemCategory,
                        "name" => $name,
                        "updated_date" => now()
                    ]);
            } else {
                DB::table('taolitem')
                    ->insert([
                        "no" => $no,
                        "unit1name" => $unit1name,
                        "itemTypeName" => $itemTypeName,
                        "notes" => $notes,
                        "cogsGlAccountId" => $cogsGlAccountId,
                        "name" => $name,
                        "itemCategory" => $itemCategory,
                        "accId" => $accId,
                        "created_date" => now()
                    ]);
            }

            DB::table('tlogaccurate')
                ->insert([
                    "source" => "ITEM",
                    "response" => json_encode($dataresponse, true),
                    "actiontype" => "ITEM",
                    "created_by" => 'WEBHOOK',
                    "created_date" => now(),
                ]);
            return response()->json('ok');
        } catch (\Throwable $th) {
            // log::critical('SUBMITTED - WEBHOOK SOURCE - ITEM. ' . $th->getMessage());
            throw new \Exception($th);
        }


    }
}
