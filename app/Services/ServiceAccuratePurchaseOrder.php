<?php
namespace App\Services;

use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class ServiceAccuratePurchaseOrder
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
                ])->get($host . '/accurate/api/purchase-order/detail.do', ["id" => $id]);

            $dataresponse = json_decode($response->body(), true);

            DB::table('tlogaccurate')
                ->insert([
                    "source" => "PURCHASE ORDER",
                    "response" => json_encode($dataresponse, true),
                    "actiontype" => "SAVE",
                    "created_by" => $event['fullName'],
                    "created_date" => now(),
                ]);

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
                'number' => $responsebody['number'],
                'processHistory' => json_encode($responsebody['processHistory']),
                "shipDate" => Carbon::createFromFormat('d/m/Y', $responsebody['shipDate'])->format('Y-m-d'),
                'status' => $responsebody['status'],
                'statusName' => $responsebody['statusName'],
                'rate' => $responsebody['rate'],
                'approvalStatus' => $responsebody['approvalStatus'],
                'deliveryOrder' => $responsebody['deliveryOrder'] == "false" ? 0 : 1,
                'purchaseReturn' => $responsebody['purchaseReturn'] == "falsse" ? 0 : 1,
                'vendorId' => $responsebody['vendorId'],
                'vendorNo' => data_get($responsebody, 'vendor.vendorNo', NULL),
                'vendorName' => data_get($responsebody, 'vendor.name', NULL),
                'subTotal' => $responsebody['subTotal'],
                'totalAmount' => $responsebody['totalAmount'],
                'currencyCode' => data_get($responsebody, 'currency.code', NULL),
                'paymentTermName' => data_get($responsebody, 'paymentTerm.name', NULL),
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
                    : null,
            ];

            $data = DB::table('taolpurchaseorderheader')
                ->where("id", $id)
                ->first();
            if ($data) {
                DB::table('taolpurchaseorderheader')
                    ->where("id", $id)
                    ->update($aolprheader);
            } else {
                DB::table('taolpurchaseorderheader')
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
                $whitemnumber_id = decryptForNumber(data_get($systemdata, 'whitemnumber_id', "0"));
                $whpartnumberlocation_id = decryptForNumber(data_get($systemdata, 'whpartnumberlocation_id', "0"));

                $aolprdetail = [
                    "id" => $detailItem['id'],
                    'purchaseOrderId' => $detailItem['purchaseOrderId'],
                    'charField1' => $detailItem['charField1'],
                    'charField2' => $detailItem['charField2'],
                    'charField15' => $detailItem['charField15'],
                    'unitPrice' => $detailItem['unitPrice'],
                    'itemName' => data_get($detailItem, 'item.name', NULL),
                    'itemNo' => data_get($detailItem, 'item.no', NULL),
                    'detailNotes' => $detailItem['detailNotes'],
                    'detailName' => $detailItem['detailName'],
                    'totalPrice' => $detailItem['totalPrice'],
                    'purchaseRequisitionDetailId' => $detailItem['purchaseRequisitionDetailId'],
                    'seq' => $detailItem['seq'],
                    'projectNo' => data_get($detailItem, 'project.no', NULL),
                    'projectId' => data_get($detailItem, 'project.id', NULL),
                    'projectName' => data_get($detailItem, 'project.name', NULL),
                    'quantity' => $detailItem['quantity'],
                    'purchaseRequisitionId' => $detailItem['purchaseRequisitionId'],
                    'purchaseRequisition' => isset($detailItem['purchaseRequisition']) ? json_encode($detailItem['purchaseRequisition']) : NULL,
                    'purchaseRequisitionDetail' => isset($detailItem['purchaseRequisitionDetail']) ? json_encode($detailItem['purchaseRequisitionDetail']) : NULL,
                    'departmentId' => data_get($detailItem, 'department.id', NULL),
                    'departmentName' => data_get($detailItem, 'department.name', NULL),
                    "req_header_id_app" => $reqheader_id,
                    "req_detail_id_app" => $reqitem_id,
                    "partnoaccapp_id" => $partnoaccsms_id,
                    "unit_id" => $unit_id,
                    "whitemnumber_id" => $whitemnumber_id,
                    "whpartnumber_id" => $whpartnumber_id,
                    "whlocation_id" => $whlocation_id,
                    "whpartnumberlocation_id" => $whpartnumberlocation_id,
                    "order_header_id_app" => $orderheader_id,
                    "order_detail_id_app" => $orderitem_id,
                    'status' => $responsebody['status'],
                    'statusName' => $responsebody['statusName'],
                    'approvalStatus' => $responsebody['approvalStatus'],
                    'POnumber' => $responsebody['number'],
                    "transdate" => Carbon::createFromFormat('d/m/Y', $responsebody['transDate'])->format('Y-m-d'),
                    "created_date" => NOW(),
                    'currencyCode' => data_get($responsebody, 'currency.code', NULL),
                    'rate' => $responsebody['rate'],
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
                $aolitem = DB::table('taolpurchaseorderdetail')
                    ->where("id", $detailItem['id'])
                    ->where("purchaseOrderId", $id)
                    ->first();
                if ($aolitem) {
                    DB::table('taolpurchaseorderdetail')
                        ->where("id", $detailItem['id'])
                        ->where("purchaseOrderId", $id)
                        ->update($aolprdetail);
                } else {
                    DB::table('taolpurchaseorderdetail')
                        ->insert($aolprdetail);
                }

            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            log::critical('INSERT TO APP - ' . $event['fullName'] . ' SOURCE - PURCHASE ORDER. ' . $th->getMessage());
            throw new \Exception($th);
        }
    }

    public function getData($event, $request)
    {
        try {
            $dataarray = [];
            $startdate = $event['startdate']->format('d/m/Y 00:00:00');
            $enddate = $event['enddate']->format('d/m/Y 23:59:00');
            $authdata = getAuthAcc();
            if (!isset($authdata['d']['database']['host'])) {
                return;
            }
            $host = $authdata['d']['database']['host'];

            $date = Carbon::now()->format('d/m/Y H:i:s');
            $hash = hash_hmac('SHA256', $date, (config('app.ACC_KEY')));
            $dataparam = [
                'fields' => 'id,printUserName,number,vendor,transDate',
                'filter.lastUpdate.val[0]' => $startdate,
                'filter.lastUpdate.val[1]' => $enddate,
                'filter.lastUpdate.op' => 'BETWEEN',
                'sp.pageSize' => $request->length,
                'sp.page' => $request->page,
            ];
            $response = Http::withToken((config('app.ACC_TOKEN')))
                ->withHeaders([
                    'X-Api-Timestamp' => $date,
                    'X-Api-Signature' => $hash
                ])->get($host . '/accurate/api/purchase-order/list.do', $dataparam);

            $dataresponse = json_decode($response->body(), true);
            // {
            //     "s": true,
            //     "d": [
            //         {
            //             "number": "PO.2024.07.00001",
            //             "vendor": {
            //                 "vendorNo": "V-0020",
            //                 "name": "ALFA SCORPII PT.",
            //                 "id": 3119
            //             },
            //             "percentShipped": 0.000000,
            //             "id": 900
            //         },
            //         {
            //             "number": "PO.2024.06.00012",
            //             "vendor": {
            //                 "vendorNo": "V-0002",
            //                 "name": "Buana Amanah Karya",
            //                 "id": 350
            //             },
            //             "percentShipped": 0.000000,
            //             "id": 851
            //         }
            //     ],
            //     "sp": {
            //         "page": 1,
            //         "sort": null,
            //         "pageSize": 100,
            //         "pageCount": 1,
            //         "rowCount": 2,
            //         "start": 0,
            //         "limit": null
            //     }
            // }
            $responsebody = '';
            $datacount = 0;
            $datarecordfilteredcount = 0;
            if (isset($dataresponse['d'])) {
                $responsebody = $dataresponse['d'];
                $datacount = $dataresponse['sp']['rowCount'];
                $datarecordfilteredcount = $dataresponse['sp']['rowCount'];
            } else {
                goto commit;
            }

            foreach ($responsebody as $detailItem) {
                $isOnApps = false;
                $aolapss = DB::table('taolpurchaseorderheader')
                    ->where('id', $detailItem['id'])
                    ->first();
                $created_date = null;
                if ($aolapss) {
                    $isOnApps = true;
                    $created_date = $aolapss->created_date;
                }
                $number = $detailItem['number'];
                $transDate = $detailItem['transDate'];
                $id = $detailItem['id'];
                $name = $detailItem['vendor']['name'];
                $vendorNo = $detailItem['vendor']['vendorNo'];
                $dataarray[] = [
                    "number" => $number,
                    "name" => $name,
                    "vendorNo" => $vendorNo,
                    "transDate" => $transDate,
                    "isOnApps" => $isOnApps,
                    "createdDate" => $created_date,
                    "id" => encryptId($id)
                ];
            }

            commit:
            // Prepare DataTables response format
            $datatableData = [
                'draw' => $request->input('draw'),
                'recordsTotal' => $datacount, // Total records in API
                'recordsFiltered' => $datarecordfilteredcount, // Total records after filtering (if searching implemented)
                'data' => $dataarray,
            ];

            return $datatableData;
        } catch (\Throwable $th) {
            log::critical('Get Data AOL Purchase' . $th->getMessage());
        }
    }
}
