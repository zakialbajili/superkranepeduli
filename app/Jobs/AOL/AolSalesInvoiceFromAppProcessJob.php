<?php

namespace App\Jobs\AOL;

use App\Services\ServiceAccurateSalesInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AolSalesInvoiceFromAppProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $ServiceAolSalesInvoice;
    protected $method;
    protected $event;
    public function __construct(ServiceAccurateSalesInvoice $ServiceAolSalesInvoice, $method, $event)
    {
        $this->ServiceAolSalesInvoice = $ServiceAolSalesInvoice;
        $this->event = $event;
        $this->method = $method;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (\Str::upper($this->method) == 'TOAPP') {
            $this->ServiceAolSalesInvoice->insertToApp($this->event);
        }
    }
}
