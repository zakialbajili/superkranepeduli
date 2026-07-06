<?php

namespace App\Jobs\AOL;

use App\Services\ServiceAccuratePurchaseReceive;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AolPurchaseReceiveFromAppProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $ServiceAolPurchaseReceive;
    protected $method;
    protected $event;
    public function __construct(ServiceAccuratePurchaseReceive $ServiceAolPurchaseReceive, $method, $event)
    {
        $this->ServiceAolPurchaseReceive = $ServiceAolPurchaseReceive;
        $this->event = $event;
        $this->method = $method;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (\Str::upper($this->method) == 'TOAPP') {
            $this->ServiceAolPurchaseReceive->insertToApp($this->event);
        }
    }
}
