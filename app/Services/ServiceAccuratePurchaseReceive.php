<?php
namespace App\Services;

use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class ServiceAccuratePurchaseReceive
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
                ])->get($host . '/accurate/api/receive-item/detail.do', ["id" => $id]);

            $dataresponse = json_decode($response->body(), true);
            $responsebody = '';
            if (isset($dataresponse['d']['number'])) {
                $responsebody = $dataresponse['d'];
            } else {
                return;
            }

            DB::beginTransaction();

            $aolprheader = [
                'id' => $id,
                'number' => $responsebody['number'],
                'vendorId' => $responsebody['vendorId'],
                'vendorNo' => data_get($responsebody, 'vendor.vendorNo', NULL),
                'vendorName' => data_get($responsebody, 'vendor.name', NULL),
                'currencyCode' => data_get($responsebody, 'currency.code', NULL),
                'transDate' => Carbon::createFromFormat('d/m/Y', $responsebody['transDate'])->format('Y-m-d'),
                'approvalStatus' => $responsebody['approvalStatus'],
                'status' => $responsebody['status'],
                'statusName' => $responsebody['statusName'],
                'shipDate' => Carbon::createFromFormat('d/m/Y', $responsebody['shipDate'])->format('Y-m-d'),
                'processHistory' => json_encode($responsebody['processHistory']),
                'created_date' => NOW(),
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
                    : null,
            ];

            $data = DB::table('taolpurchasereceiveheader')
                ->where("id", $id)
                ->first();
            if ($data) {
                DB::table('taolpurchasereceiveheader')
                    ->where("id", $id)
                    ->update($aolprheader);
            } else {
                DB::table('taolpurchasereceiveheader')
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
                    'id' => $detailItem['id'],
                    'receiveItemId' => $detailItem['receiveItemId'],
                    'unitPrice' => $detailItem['unitPrice'],
                    'shipDate' => Carbon::createFromFormat('d/m/Y', $detailItem['shipDate'])->format('Y-m-d'),
                    'seq' => $detailItem['seq'],
                    'purchaseOrderId' => $detailItem['purchaseOrderId'],
                    'detailName' => $detailItem['detailName'],
                    'itemNo' => data_get($detailItem, 'item.no', NULL),
                    'itemName' => data_get($detailItem, 'item.name', NULL),
                    'detailNotes' => $detailItem['detailNotes'],
                    'projectNo' => data_get($detailItem, 'project.no', NULL),
                    'projectId' => data_get($detailItem, 'project.id', NULL),
                    'projectName' => data_get($detailItem, 'project.name', NULL),
                    'departmentId' => data_get($detailItem, 'department.id', NULL),
                    'departmentName' => data_get($detailItem, 'department.name', NULL),
                    'quantity' => $detailItem['quantity'],
                    'currencyCode' => data_get($responsebody, 'currency.code', NULL),
                    'purchaseRequisitionId' => $detailItem['purchaseRequisitionId'],
                    'purchaseRequisition' => json_encode($detailItem['purchaseRequisition']),
                    'purchaseRequisitionDetail' => json_encode($detailItem['purchaseRequisitionDetail']),
                    'purchaseOrderDetailId' => $detailItem['purchaseOrderDetailId'],
                    'purchaseOrder' => json_encode($detailItem['purchaseOrder']),
                    'purchaseOrderDetail' => json_encode($detailItem['purchaseOrderDetail']),
                    'RInumber' => $responsebody['number'],
                    "req_header_id_app" => $reqheader_id,
                    "req_detail_id_app" => $reqitem_id,
                    "partnoaccapp_id" => $partnoaccsms_id,
                    "unit_id" => $unit_id,
                    "whpartnumber_id" => $whpartnumber_id,
                    "whlocation_id" => $whlocation_id,
                    "whpartnumberlocation_id" => $whpartnumberlocation_id,
                    "order_header_id_app" => $orderheader_id,
                    "order_detail_id_app" => $orderitem_id,
                    'status' => $responsebody['status'],
                    'statusName' => $responsebody['statusName'],
                    'approvalStatus' => $responsebody['approvalStatus'],
                    'transDate' => Carbon::createFromFormat('d/m/Y', $responsebody['transDate'])->format('Y-m-d'),
                    "created_date" => NOW(),
                    'charField1' => $detailItem['charField1'],
                    'charField2' => $detailItem['charField2'],
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
                    'charField15' => $detailItem['charField15'],
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
                ];
                $aolitem = DB::table('taolpurchasereceivedetail')
                    ->where("id", $detailItem['id'])
                    ->where("receiveItemId", $id)
                    ->first();
                if ($aolitem) {
                    DB::table('taolpurchasereceivedetail')
                        ->where("id", $detailItem['id'])
                        ->where("receiveItemId", $id)
                        ->update($aolprdetail);
                } else {
                    DB::table('taolpurchasereceivedetail')
                        ->insert($aolprdetail);
                }

            }

            DB::table('tlogaccurate')
                ->insert([
                    "source" => "PURCHASE RECEIVE",
                    "response" => json_encode($dataresponse, true),
                    "actiontype" => "SAVE",
                    "created_by" => $event['fullName'],
                    "created_date" => now(),
                ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            log::critical('INSERT TO APP - ' . $event['fullName'] . ' SOURCE - PURCHASE RECEIVE. ' . $th->getMessage());
            throw new \Exception($th);
        }
    }
}
