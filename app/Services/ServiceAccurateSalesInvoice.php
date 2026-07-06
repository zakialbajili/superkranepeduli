<?php
namespace App\Services;

use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class ServiceAccurateSalesInvoice
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
                ])->get($host . '/accurate/api/sales-invoice/detail.do', ["id" => $id]);

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
                'customerId' => $responsebody['customerId'],
                'customerNo' => data_get($responsebody, 'customer.customerNo', NULL),
                'customerName' => data_get($responsebody, 'customer.name', NULL),
                'retailWpName' => $responsebody['retailWpName'],
                'hasNPWP' => $responsebody['hasNPWP'],
                'currencyCode' => data_get($responsebody, 'currency.code', NULL),
                'outstanding' => $responsebody['outstanding'],
                'approvalStatus' => $responsebody['approvalStatus'],
                'createdBy' => $responsebody['createdBy'],
                'salesAmountBase' => $responsebody['salesAmountBase'],
                'status' => $responsebody['status'],
                'salesAmount' => $responsebody['salesAmount'],
                'statusName' => $responsebody['statusName'],
                'subTotal' => $responsebody['subTotal'],
                'primeOwing' => $responsebody['primeOwing'],
                'rate' => $responsebody['rate'],
                'totalAmount' => $responsebody['totalAmount'],
                'paymentTermName' => data_get($responsebody, 'paymentTerm.name', NULL),
                'duedate' => Carbon::createFromFormat('d/m/Y', $responsebody['dueDate'])->format('Y-m-d'),
                'taxDate' => Carbon::createFromFormat('d/m/Y', $responsebody['taxDate'])->format('Y-m-d'),
                'transDate' => Carbon::createFromFormat('d/m/Y', $responsebody['transDate'])->format('Y-m-d'),
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

            $data = DB::table('taolsalesinvoiceheader')
                ->where("id", $id)
                ->first();
            if ($data) {
                $aolprheader['updated_date'] = now();
                DB::table('taolsalesinvoiceheader')
                    ->where("id", $id)
                    ->update($aolprheader);
            } else {
                $aolprheader['created_date'] = now();
                DB::table('taolsalesinvoiceheader')
                    ->insert($aolprheader);
            }

            foreach ($responsebody['detailItem'] as $detailItem) {
                $systemdata = json_decode($detailItem['charField15'], true);

                $aolprdetail = [
                    'id' => $detailItem['id'],
                    'salesInvoiceId' => $detailItem['salesInvoiceId'],
                    'SiNumber' => $responsebody['number'],
                    'customerNo' => data_get($responsebody, 'customer.customerNo', NULL),
                    'customerName' => data_get($responsebody, 'customer.name', NULL),
                    'statusName' => $responsebody['statusName'],
                    'approvalStatus' => $responsebody['approvalStatus'],
                    'unitPrice' => $detailItem['unitPrice'],
                    'itemNo' => data_get($detailItem, 'item.no', NULL),
                    'itemName' => data_get($detailItem, 'item.name', NULL),
                    'detailNotes' => $detailItem['detailNotes'],
                    'totalPrice' => $detailItem['totalPrice'],
                    'seq' => $detailItem['seq'],
                    'projectNo' => data_get($detailItem, 'project.no', NULL),
                    'projectId' => data_get($detailItem, 'project.id', NULL),
                    'projectName' => data_get($detailItem, 'project.name', NULL),
                    'quantity' => $detailItem['quantity'],
                    'departmentId' => data_get($detailItem, 'department.id', NULL),
                    'departmentName' => data_get($detailItem, 'department.name', NULL),
                    'detailName' => $detailItem['detailName'],
                    'currencyCode' => data_get($responsebody, 'currency.code', NULL),
                    'status' => $responsebody['status'],
                    'rate' => $responsebody['rate'],
                    'transDate' => Carbon::createFromFormat('d/m/Y', $responsebody['transDate'])->format('Y-m-d'),
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

                $aolitem = DB::table('taolsalesinvoicedetail')
                    ->where("id", $detailItem['id'])
                    ->where("salesInvoiceId", $id)
                    ->first();
                if ($aolitem) {
                    $aolprdetail['updated_date'] = now();
                    DB::table('taolsalesinvoicedetail')
                        ->where("id", $detailItem['id'])
                        ->where("salesInvoiceId", $id)
                        ->update($aolprdetail);
                } else {
                    $aolprdetail['created_date'] = now();
                    DB::table('taolsalesinvoicedetail')
                        ->insert($aolprdetail);
                }

            }

            foreach ($responsebody['receiptHistory'] as $detailItem) {

                $aolprdetail = [
                    'id' => $detailItem['id'],
                    'historyDate' => Carbon::createFromFormat('d/m/Y', $detailItem['historyDate'])->format('Y-m-d'),
                    'historyNumber' => $detailItem['historyNumber'],
                    'historyAmount' => $detailItem['historyAmount'],
                    'historyPaymentId' => $detailItem['historyPaymentId'],
                    'historyPaymentName' => $detailItem['historyPaymentName'],
                    'salesid' => $id,
                ];

                $aolitem = DB::table('taolsalesreceiptdetail')
                    ->where("id", $detailItem['id'])
                    ->where("salesid", $id)
                    ->first();
                if ($aolitem) {
                    DB::table('taolsalesreceiptdetail')
                        ->where("id", $detailItem['id'])
                        ->where("salesid", $id)
                        ->update($aolprdetail);
                } else {
                    $aolprdetail['created_date'] = now();
                    DB::table('taolsalesreceiptdetail')
                        ->insert($aolprdetail);
                }

            }

            DB::table('tlogaccurate')
                ->insert([
                    "source" => "SALES INVOICE",
                    "response" => json_encode($dataresponse, true),
                    "actiontype" => "SAVE",
                    "created_by" => $event['fullName'],
                    "created_date" => now(),
                ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            log::critical('INSERT TO APP - ' . $event['fullName'] . ' SOURCE - SALES INVOICE. ' . $th->getMessage());
            throw new \Exception($th);
        }
    }
}
