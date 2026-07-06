<?php

namespace App\Jobs\AOL;

use App\Services\ServiceAccuratePurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AolPurchaseOrderFromAppProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $ServiceAolPurchaseOrder;
    protected $method;
    protected $event;
    public function __construct(ServiceAccuratePurchaseOrder $ServiceAolPurchaseOrder, $method, $event)
    {
        $this->ServiceAolPurchaseOrder = $ServiceAolPurchaseOrder;
        $this->event = $event;
        $this->method = $method;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            if (\Str::upper($this->method) == 'TOAPP') {
                $this->ServiceAolPurchaseOrder->insertToApp($this->event);
            }
        } catch (\Throwable $th) {
            \log::critical('INSERT TO APP 1 - SOURCE - PURCHASE ORDER. ' . $th->getMessage());
            throw new \Exception($th);
        }
    }
}
