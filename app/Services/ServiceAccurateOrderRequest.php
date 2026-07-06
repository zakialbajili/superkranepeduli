<?php
namespace App\Services;

use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class ServiceAccurateOrderRequest
{
    public function save($event)
    {
        try {
            $orderheader = DB::table('twhorderheader')
                ->where('pk_whorderheader_id', $event['id'])
                ->first();

            $authdata = getAuthAcc();
            if (!isset($authdata['d']['database']['host'])) {
                return;
            }
            $host = $authdata['d']['database']['host'];

            $date = Carbon::now()->format('d/m/Y H:i:s');
            $hash = hash_hmac('SHA256', $date, (config('app.ACC_KEY')));

            $data = [];
            $detailItem = [];
            $orderitemraw = DB::table('twhorderitem')
                ->select(
                    'twhorderitem.partno_acc',
                    'twhorderheader.order_no',
                    'twhorderitem.required_date',
                    'twhorderitem.qty',
                    'twhpartnumber.part_no',
                    'twhorderitem.description',
                    'twhorderitem.manuf',
                    'twhorderitem.notes',
                    'tprojectmaster.name as uom',
                    'twhorderitem.pk_whorderitem_id',
                    'twhorderitem.fk_whorderheader_id',
                    'twhorderitem.fk_whreqitem_id',
                    'twhorderitem.fk_whpartnumber_id',
                    'twhorderitem.fk_whitemnumber_id',
                    'twhorderitem.fk_whlocation_id',
                    'twhorderitem.fk_whpartnumberlocation_id',
                    'twhorderitem.fk_partnoaccsms_id',
                    'twhorderitem.fk_whreqheader_id',
                    'twhorderitem.fk_unit_id',
                    'tunit.unit_no',

                )
                ->join('tprojectmaster', 'pk_projectmaster_id', 'twhorderitem.fk_uom_id')
                ->join('twhorderheader', 'pk_whorderheader_id', 'twhorderitem.fk_whorderheader_id')
                ->leftJoin('twhpartnumber', 'twhpartnumber.pk_whpartnumber_id', 'twhorderitem.fk_whpartnumber_id')
                ->leftJoin('tunit', 'tunit.pk_unit_id', 'twhorderitem.fk_unit_id')
                ->where('fk_whorderheader_id', $event['id'])
                ->whereNull('req_header_id_acc')
                ->get();
            $i = 0;
            if (count($orderitemraw) == 0) {
                return;
            }
            $data = [
                'transDate' => Carbon::createFromFormat('Y-m-d', $orderheader->order_date)->format('d/m/Y'),
                "number" => $orderheader->order_no,
            ];
            foreach ($orderitemraw as $orderitem) {
                $datatoacc = [
                    "orderitem_id" => encryptId($orderitem->pk_whorderitem_id),
                    "orderheader_id" => encryptId($orderitem->fk_whorderheader_id),
                    "reqheader_id" => encryptId($orderitem->fk_whreqheader_id),
                    "reqitem_id" => encryptId($orderitem->fk_whreqitem_id),
                    "partnoaccsms_id" => encryptId($orderitem->fk_partnoaccsms_id),
                    "unit_id" => encryptId($orderitem->fk_unit_id),
                    "whitemnumber_id" => encryptId($orderitem->fk_whitemnumber_id),
                    "whpartnumber_id" => encryptId($orderitem->fk_whpartnumber_id),
                    "whlocation_id" => encryptId($orderitem->fk_whlocation_id),
                    "whpartnumberlocation_id" => encryptId($orderitem->fk_whpartnumberlocation_id),
                ];
                $data['detailItem[' . $i . ']'] = [
                    "itemNo" => $orderitem->partno_acc,
                    "requiredDate" => Carbon::createFromFormat('Y-m-d', $orderitem->required_date)->format('d/m/Y'),
                    "unitPrice" => 0,
                    "detailName" => $orderitem->description,
                    "quantity" => $orderitem->qty,
                    "detailNotes" => $orderitem->order_no . ' | ' . $orderitem->notes,
                    "charField1" => $orderitem->uom,
                    "charField2" => $orderitem->unit_no,
                    "charField4" => $orderitem->part_no,
                    "charField6" => $orderitem->manuf,
                    "charField15" => json_encode($datatoacc),
                ];
                $i++;
            }
            $response = Http::withToken((config('app.ACC_TOKEN')))
                ->withHeaders([
                    'X-Api-Timestamp' => $date,
                    'X-Api-Signature' => $hash
                ])->post($host . '/accurate/api/purchase-requisition/save.do', $data);

            $dataresponse = json_decode($response->body(), true);

            DB::table('tlogaccurate')
                ->insert([
                    "source" => "PURCHASE REQUISITION",
                    "response" => json_encode($dataresponse, true),
                    "actiontype" => "SAVE",
                    "created_by" => $event['fullName'],
                    "created_date" => now(),
                ]);

            $responsebody = $dataresponse['r'];


            if (isset($responsebody['id'])) {
                $event['aolid'] = $responsebody['id'];
                $this->insertToApp($event);
            }
        } catch (\Throwable $th) {
            log::critical('SAVE - ' . $event['fullName'] . ' SOURCE - PURCHASE REQUISITION. ' . $th->getMessage());
            throw new \Exception($th);
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
            if (isset($dataresponse['d']['number'])) {
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
                'charField1' => $responsebody['charField1'],
                'charField2' => $responsebody['charField2'],
                "charField3" => $responsebody['charField3'],
                "charField4" => $responsebody['charField4'],
                "charField5" => $responsebody['charField5'],
                "charField6" => $responsebody['charField6'],
                "charField7" => $responsebody['charField7'],
                "charField8" => $responsebody['charField8'],
                "charField9" => $responsebody['charField9'],
                "charField10" => $responsebody['charField10'],
                "numericField1" => $responsebody['numericField1'],
                "numericField2" => $responsebody['numericField2'],
                "numericField3" => $responsebody['numericField3'],
                "numericField4" => $responsebody['numericField4'],
                "numericField5" => $responsebody['numericField5'],
                "numericField6" => $responsebody['numericField6'],
                "numericField7" => $responsebody['numericField7'],
                "numericField8" => $responsebody['numericField8'],
                "numericField9" => $responsebody['numericField9'],
                "numericField10" => $responsebody['numericField10'],
                "dateField1" => !empty($responsebody['dateField1']) && Carbon::hasFormat($responsebody['dateField1'], 'd/m/Y')
                    ? Carbon::createFromFormat('d/m/Y', $responsebody['dateField1'])->format('Y-m-d')
                    : null,
                "dateField2" => !empty($responsebody['dateField2']) && Carbon::hasFormat($responsebody['dateField2'], 'd/m/Y')
                    ? Carbon::createFromFormat('d/m/Y', $responsebody['dateField2'])->format('Y-m-d')
                    : null
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
                    "charField2" => $detailItem['charField2'],
                    "charField3" => $detailItem['charField3'],
                    "charField4" => $detailItem['charField4'],
                    "charField5" => $detailItem['charField5'],
                    "charField6" => $detailItem['charField6'],
                    "charField7" => $detailItem['charField7'],
                    "charField8" => $detailItem['charField8'],
                    "charField9" => $detailItem['charField9'],
                    "charField10" => $detailItem['charField10'],
                    "charField11" => $detailItem['charField11'],
                    "charField12" => $detailItem['charField12'],
                    "charField13" => $detailItem['charField13'],
                    "charField14" => $detailItem['charField14'],
                    "charField15" => $detailItem['charField15'],
                    "numericField1" => $detailItem['numericField1'],
                    "numericField2" => $detailItem['numericField2'],
                    "numericField3" => $detailItem['numericField3'],
                    "numericField4" => $detailItem['numericField4'],
                    "numericField5" => $detailItem['numericField5'],
                    "numericField6" => $detailItem['numericField6'],
                    "numericField7" => $detailItem['numericField7'],
                    "numericField8" => $detailItem['numericField8'],
                    "numericField9" => $detailItem['numericField9'],
                    "numericField10" => $detailItem['numericField10'],
                    "dateField1" => !empty($responsebody['dateField1']) && Carbon::hasFormat($responsebody['dateField1'], 'd/m/Y')
                        ? Carbon::createFromFormat('d/m/Y', $responsebody['dateField1'])->format('Y-m-d')
                        : null,
                    "dateField2" => !empty($responsebody['dateField2']) && Carbon::hasFormat($responsebody['dateField2'], 'd/m/Y')
                        ? Carbon::createFromFormat('d/m/Y', $responsebody['dateField2'])->format('Y-m-d')
                        : null,
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
            throw new \Exception($th);
        }
    }
}
