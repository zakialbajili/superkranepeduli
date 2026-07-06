<?php

namespace App\Listeners;

use App\Events\EventAccurateGlAccount;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class SaveAccurateGlAccountListener
{
    /**
     * Handle the event.
     */
    public function handle(EventAccurateGlAccount $event): void
    {
        try {
            $glaccount = DB::table('taccglaccount')
                ->where('pk_accglaccount_id', $event->data['glaccountid'])
                ->first();

            $authdata = getAuthAcc();
            if (!isset($authdata['d']['database']['host'])) {
                return;
            }
            $host = $authdata['d']['database']['host'];

            $date = Carbon::now()->format('d/m/Y H:i:s');
            $hash = hash_hmac('SHA256', $date, (env('ACC_KEY')));

            $data = [];
            if ($glaccount->accId > 0) {
                $data = [
                    "name" => $glaccount->name,
                    "memo" => $glaccount->memo,
                    "id" => $glaccount->accId,
                ];
                if ($glaccount->parentId > 0) {
                    $data['parentNo'] = $glaccount->parentNo;
                }
            } else {
                $data = [
                    "accountType" => $glaccount->accountType,
                    "asOf" => Carbon::createFromFormat('Y-m-d', $glaccount->asOf)->format('d/m/Y'),
                    "currencyCode" => $glaccount->currencyCode,
                    "name" => $glaccount->name,
                    "no" => $glaccount->no,
                    "memo" => $glaccount->memo,
                    "openBalance" => $glaccount->openBalance,
                ];

                if ($glaccount->parentId > 0) {
                    $data['parentNo'] = $glaccount->parentNo;
                }
            }

            $response = Http::withToken((env('ACC_TOKEN')))
                ->withHeaders([
                    'X-Api-Timestamp' => $date,
                    'X-Api-Signature' => $hash
                ])->post($host . '/accurate/api/glaccount/save.do', $data);

            $dataresponse = json_decode($response->body(), true);

            $accId = 0;
            if (isset($dataresponse['r']['id'])) {
                $accId = $dataresponse['r']['id'];
            }

            $glaccount = DB::table('taccglaccount')
                ->where('pk_accglaccount_id', $event->data['glaccountid'])
                ->update([
                    "accId" => $accId
                ]);


            DB::table('tlogaccurate')
                ->insert([
                    "source" => "GL ACCOUNT",
                    "response" => json_encode($dataresponse, true),
                    "actiontype" => "GL ACCOUNT",
                    "created_by" => $event->data['fullName'],
                    "created_date" => now(),
                ]);

        } catch (\Throwable $th) {
            log::critical('SUBMITTED - ' . $event->data['fullName'] . ' SOURCE - GLACCOUNT. ' . $th->getMessage());
        }


    }
}


