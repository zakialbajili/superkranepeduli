<?php
namespace App\Services;

use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class ServiceAccurateCategoryData
{
    public function save($event)
    {
        try {
            $index = $event['index'];
            $name = $event['name'];

            $authdata = getAuthAcc();
            if (!isset($authdata['d']['database']['host'])) {
                return;
            }
            $host = $authdata['d']['database']['host'];

            $date = Carbon::now()->format('d/m/Y H:i:s');
            $hash = hash_hmac('SHA256', $date, (config('app.ACC_KEY')));

            $data = [];
            $detailItem = [];


            $data = [
                "index" => $index,
                "name" => $name,
            ];
            $response = Http::withToken((config('app.ACC_TOKEN')))
                ->withHeaders([
                    'X-Api-Timestamp' => $date,
                    'X-Api-Signature' => $hash
                ])->post($host . '/accurate/api/data-classification/save.do', $data);

            $dataresponse = json_decode($response->body(), true);

            DB::table('tlogaccurate')
                ->insert([
                    "source" => "DATA CLASIFICATION",
                    "response" => json_encode($dataresponse, true),
                    "actiontype" => "SAVE",
                    "created_by" => "SYSTEM",
                    "created_date" => now(),
                ]);

            // $responsebody = $dataresponse['r'];

            // if (isset($responsebody['id'])) {
            //     $event['aolid'] = $responsebody['id'];
            //     $this->insertToApp($event);
            // }
        } catch (\Throwable $th) {
            log::critical('SAVE - SOURCE - DATA CLASIFICATION. ' . $th->getMessage());
            throw new \Exception($th);
        }
    }
}
