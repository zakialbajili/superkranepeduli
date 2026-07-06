<?php

namespace App\Jobs;

use App\Jobs\AOL\AolOrderRequestFromAppProcessJob;
use App\Jobs\AOL\AolSalesInvoiceFromAppProcessJob;
use App\Services\ServiceAccurateSalesInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
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

class AccurateRenewWebhook implements ShouldQueue
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

            $date = Carbon::now()->format('d/m/Y H:i:s');
            $hash = hash_hmac('SHA256', $date, (config('app.ACC_KEY')));
            $response = \Http::withToken((config('app.ACC_TOKEN')))
                ->withHeaders([
                    'X-Api-Timestamp' => $date,
                    'X-Api-Signature' => $hash
                ])->post('https://account.accurate.id/api/webhook-renew.do');
        } catch (\Throwable $th) {
            Log::info('Accurate Renew Webhook Handler - ' . $th->getMessage());
        }
    }
}
