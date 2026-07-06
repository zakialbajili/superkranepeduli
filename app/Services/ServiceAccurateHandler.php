<?php
namespace App\Services;

use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class ServiceAccurateHandler
{
    //event : array['id']
    public function FromApps($source, $event, $fullname)
    {
        try {
            //insert into aolappsjobs
            DB::table("aolappsjobs")
                ->insert([
                    "type" => $source,
                    "event" => json_encode($event),
                    "created_by" => $fullname,
                    "created_date" => now(),
                    "status" => "NEW",
                    "status_updated_date" => now(),
                    "retry" => 0,
                ]);
        } catch (\Throwable $th) {
            log::critical('SAVE - ' . $event['fullName'] . ' Accurate Handler - From Apps. ' . $th->getMessage());
        }
    }

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
                ])->get($host . '/accurate/api/purchase-requisition/detail.do', ["id" => $id]);

            $dataresponse = json_decode($response->body(), true);
            $responsebody = '';
            if (isset($dataresponse['d'])) {
                $responsebody = $dataresponse['d'];
            } else {
                return;
            }

            DB::beginTransaction();

            $aolprheader = [
                "id" => $id,
                "transdate" => Carbon::createFromFormat('d/m/Y', $responsebody['transDate'])->format('Y-m-d'),
                "number" => $responsebody['number'],
                "approvalstatus" => $responsebody['approvalStatus'],
                "status" => $responsebody['status'],
                "processhistory" => json_encode($responsebody['processHistory']),
                "created_date" => NOW(),
            ];

            $data = DB::table('taolpurchaserequisitionheader')
                ->where("id", $id)
                ->first();
            if ($data) {
                DB::table('taolpurchaserequisitionheader')
                    ->where("id", $id)
                    ->update($aolprheader);
            } else {
                DB::table('taolpurchaserequisitionheader')
                    ->insert($aolprheader);
            }

            foreach ($responsebody['detailItem'] as $detailItem) {

                $systemdata = json_decode($detailItem['charField15'], true);
                $orderitem_id = decryptForNumber(data_get($systemdata, 'orderitem_id', "0"));
                $orderheader_id = decryptForNumber(data_get($systemdata, 'orderheader_id', "0"));
                $reqheader_id = decryptForNumber(data_get($systemdata, 'reqheader_id', "0"));
                $reqitem_id = decryptForNumber(data_get($systemdata, 'reqitem_id', "0"));
                $partnoaccsms_id = decryptForNumber(data_get($systemdata, 'partnoaccsms_id', "0"));
                $unit_id = decryptForNumber(data_get($systemdata, 'unit_id', "0"));
                $whpartnumber_id = decryptForNumber(data_get($systemdata, 'whpartnumber_id', "0"));
                $whlocation_id = decryptForNumber(data_get($systemdata, 'whlocation_id', "0"));
                $whpartnumberlocation_id = decryptForNumber(data_get($systemdata, 'whpartnumberlocation_id', "0"));

                $aolprdetail = [
                    "id" => $detailItem['id'],
                    "headerid" => $id,
                    "requireddate" => Carbon::createFromFormat('d/m/Y', $detailItem['requiredDate'])->format('Y-m-d'),
                    "quantity" => $detailItem['quantity'],
                    "receivedQuantity" => $detailItem['receivedQuantity'],
                    "seq" => $detailItem['seq'],
                    "detailnotes" => $detailItem['detailNotes'],
                    "processhistory" => json_encode($responsebody['processHistory']),
                    "charField1" => $detailItem['charField1'],
                    "charField15" => $detailItem['charField15'],
                    "req_header_id_app" => $reqheader_id,
                    "req_detail_id_app" => $reqitem_id,
                    "partnoaccapp_id" => $partnoaccsms_id,
                    "unit_id" => $unit_id,
                    "whpartnumber_id" => $whpartnumber_id,
                    "whlocation_id" => $whlocation_id,
                    "whpartnumberlocation_id" => $whpartnumberlocation_id,
                    "order_header_id_app" => $orderheader_id,
                    "order_detail_id_app" => $orderitem_id,
                    "created_date" => NOW(),
                ];
                $aolitem = DB::table('taolpurchaserequisitiondetail')
                    ->where("id", $detailItem['id'])
                    ->where("headerid", $id)
                    ->first();
                if ($aolitem) {
                    DB::table('taolpurchaserequisitiondetail')
                        ->where("id", $detailItem['id'])
                        ->where("headerid", $id)
                        ->update($aolprdetail);
                } else {
                    DB::table('taolpurchaserequisitiondetail')
                        ->insert($aolprdetail);
                }

                $systemdata = json_decode($detailItem['charField15']);

                //update order item status
                if (isset($systemdata->orderitem_id)) {
                    $orderitem_id = decryptForNumber($systemdata->orderitem_id);
                    $orderheader_id = decryptForNumber($systemdata->orderheader_id);
                    DB::table('twhorderitem')
                        ->where('pk_whorderitem_id', $orderitem_id)
                        ->where('fk_whorderheader_id', $orderheader_id)
                        ->update([
                            "req_id_acc" => $detailItem['id'],
                            "req_header_id_acc" => $id,
                            "seq_acc" => $detailItem['seq'],
                            "status_acc" => $responsebody['status'],
                            "approvalstatus_acc" => $responsebody['approvalStatus'],
                            "reqno_acc" => $responsebody['number'],
                        ]);
                }
            }

            DB::table('tlogaccurate')
                ->insert([
                    "source" => "PURCHASE REQUISITION",
                    "response" => json_encode($dataresponse, true),
                    "actiontype" => "SAVE",
                    "created_by" => $event['fullName'],
                    "created_date" => now(),
                ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            log::critical('INSERT TO APP - ' . $event['fullName'] . ' SOURCE - PURCHASE REQUISITION. ' . $th->getMessage());
        }
    }
}
