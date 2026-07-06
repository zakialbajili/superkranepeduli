<?php

namespace App\Jobs\AOL;

use App\Services\ServiceAccuratePurchaseInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AolPurchaseInvoiceFromAppProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $ServiceAolPurchaseInvoice;
    protected $method;
    protected $event;
    public function __construct(ServiceAccuratePurchaseInvoice $ServiceAolPurchaseInvoice, $method, $event)
    {
        $this->ServiceAolPurchaseInvoice = $ServiceAolPurchaseInvoice;
        $this->event = $event;
        $this->method = $method;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (\Str::upper($this->method) == 'TOAPP') {
            $this->ServiceAolPurchaseInvoice->insertToApp($this->event);
        }
    }
}
