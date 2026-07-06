<?php

namespace App\Jobs;

use App\Jobs\AOL\AolOrderRequestFromAppProcessJob;
use App\Jobs\AOL\AolSalesInvoiceFromAppProcessJob;
use App\Services\ServiceAccurateCategoryData;
use App\Services\ServiceAccurateCustomer;
use App\Services\ServiceAccurateSalesInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\AOL\AolPurchaseInvoiceFromAppProcessJob;
use App\Jobs\AOL\AolPurchaseOrderFromAppProcessJob;
use App\Jobs\AOL\AolPurchaseReceiveFromAppProcessJob;
use App\Services\AolWebhookItemHandler;
use App\Services\ServiceAccurateOrderRequest;
use App\Services\ServiceAccuratePurchaseInvoice;
use App\Services\ServiceAccuratePurchaseOrder;
use App\Services\ServiceAccuratePurchaseReceive;

class AccurateJobHandler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $idjobs = [];
        try {
            //get job list from table : aolappsjobs
            $aolappsjobquery = DB::table("aolappsjobs")
                ->where("status", "=", "NEW")
                ->limit(8);

            $aolappsjobraws = $aolappsjobquery->get();
            $idjobs = $aolappsjobquery->pluck("pk_aolappsjobs_id");
            DB::table("aolappsjobs")
                ->where("status", "=", "NEW")
                ->whereIn("pk_aolappsjobs_id", $aolappsjobquery->pluck("pk_aolappsjobs_id"))
                ->update([
                    "status" => "INPROGRESS",
                    "status_updated_date" => NOW(),
                ]);

            if ($aolappsjobraws) {
                foreach ($aolappsjobraws as $aolappjobraw) {
                    $type = $aolappjobraw->type;
                    switch ($type) {
                        case "Warehouse Order":
                            $AolOrderRequest = new ServiceAccurateOrderRequest();
                            // $AolOrderRequest = app('App\Services\ServiceAccurateOrderRequest');
                            $AolOrderRequest->save(json_decode($aolappjobraw->event, true));
                            DB::table("aolappsjobs")
                                ->where("pk_aolappsjobs_id", $aolappjobraw->pk_aolappsjobs_id)
                                ->delete();
                            break;
                        case "Data Clasification":
                            $AolOrderRequest = new ServiceAccurateCategoryData();
                            // $AolOrderRequest = app('App\Services\ServiceAccurateOrderRequest');
                            $AolOrderRequest->save(json_decode($aolappjobraw->event, true));
                            DB::table("aolappsjobs")
                                ->where("pk_aolappsjobs_id", $aolappjobraw->pk_aolappsjobs_id)
                                ->delete();
                            break;
                        default:
                            # code...
                            break;
                    }
                }
            }
        } catch (\Throwable $th) {
            try {
                DB::table("aolappsjobs")
                    ->where("status", "=", "NEW")
                    ->whereIn("pk_aolappsjobs_id", $idjobs)
                    ->update([
                        "status" => "NEW",
                        "status_updated_date" => NOW(),
                        "retry" => DB::raw('retry + 1')
                    ]);
            } catch (\Throwable $th) {
                Log::info('Accurate Job Handler Increment - ' . $th->getMessage());
            }
            Log::info('Accurate Job Handler - ' . $th->getMessage());
        }

        sleep(3);

        try {
            $aolwebhookquery = DB::table("aolwebhook")
                ->where("status", "=", "NEW")
                ->limit(8);

            $aolwebhookraws = $aolwebhookquery->get();
            $idwebhooks = $aolwebhookquery->pluck("id");

            DB::table("aolwebhook")
                ->where("status", "=", "NEW")
                ->whereIn("id", $idwebhooks)
                ->update([
                    "status" => "INPROGRESS",
                    "status_updated_date" => NOW(),
                ]);

            if ($aolwebhookraws) {
                $aolwebhookitem = null;
                foreach ($aolwebhookraws as $aolwebhookraw) {
                    $type = $aolwebhookraw->type;
                    $payload = json_decode($aolwebhookraw->payload, true);

                    switch ($type) {
                        case 'ITEM':
                            if ($payload['data'][0]['action'] == "WRITE") {
                                $aolwebhookitem = new AolWebhookItemHandler();
                                $gldata[0] = $payload['data'][0]['itemId'];
                            }
                            break;
                        case 'PURCHASE_REQUISITION':
                            if ($payload['data'][0]['action'] == "WRITE") {
                                $aolwebhookitem = new ServiceAccurateOrderRequest();
                                $gldata[0] = [
                                    "aolid" => $payload['data'][0]['purchaseRequisitionId'],
                                    "fullName" => 'SYSTEM',
                                    "employee_no" => '9999999',
                                ];
                            }
                            break;
                        case 'PURCHASE_ORDER':
                            if ($payload['data'][0]['action'] == "WRITE") {
                                $aolwebhookitem = new ServiceAccuratePurchaseOrder();
                                $gldata[0] = [
                                    "aolid" => $payload['data'][0]['purchaseOrderId'],
                                    "fullName" => 'SYSTEM',
                                    "employee_no" => '9999999',
                                ];
                            }
                            break;
                        case 'RECEIVE_ITEM':
                            if ($payload['data'][0]['action'] == "WRITE") {
                                $aolwebhookitem = new ServiceAccuratePurchaseReceive();
                                $gldata[0] = [
                                    "aolid" => $payload['data'][0]['receiveItemId'],
                                    "fullName" => 'SYSTEM',
                                    "employee_no" => '9999999',
                                ];
                            }
                            break;
                        case 'PURCHASE_INVOICE':
                            if ($payload['data'][0]['action'] == "WRITE") {
                                $aolwebhookitem = new ServiceAccuratePurchaseInvoice();
                                $gldata[0] = [
                                    "aolid" => $payload['data'][0]['purchaseInvoiceId'],
                                    "fullName" => 'SYSTEM',
                                    "employee_no" => '9999999',
                                ];
                            }
                            break;
                        case 'SALES_INVOICE':
                            foreach ($payload['data'] as $payloaddata) {
                                if ($payloaddata['action'] == "WRITE") {
                                    $aolwebhookitem = new ServiceAccurateSalesInvoice();
                                    $gldata[] = [
                                        "aolid" => $payloaddata['salesInvoiceId'],
                                        "fullName" => 'SYSTEM',
                                        "employee_no" => '9999999',
                                    ];
                                }
                            }
                            break;
                        case 'CUSTOMER':
                            if ($payload['data'][0]['action'] == "WRITE") {
                                $aolwebhookitem = new ServiceAccurateCustomer();
                                $gldata[0] = [
                                    "aolid" => $payload['data'][0]['customerId'],
                                    "fullName" => 'SYSTEM',
                                    "employee_no" => '9999999',
                                ];
                            }
                            break;
                        default:
                            # code...
                            break;
                    }

                    try {
                        foreach ($gldata as $gl) {
                            $aolwebhookitem->insertToApp($gl);
                        }
                        DB::table("aolwebhook")
                            ->where("id", $aolwebhookraw->id)
                            ->delete();
                    } catch (\Throwable $th) {
                        DB::table("aolwebhook")
                            ->where("status", "=", "INPROGRESS")
                            ->where("id", $aolwebhookraw->id)
                            ->update([
                                "status" => "NEW",
                                "retry" => DB::raw('retry + 1'),
                                "status_updated_date" => NOW(),
                                "error" => $th->getMessage(),
                            ]);
                    }

                }
            }
        } catch (\Throwable $th) {
            Log::info('Accurate Webhook Handler - ' . $th->getMessage());
        }
    }
}
