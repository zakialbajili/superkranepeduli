<?php

namespace App\Jobs\AOL;

use App\Services\ServiceAccurateOrderRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AolOrderRequestFromAppProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $ServiceAolOrderRequest;
    protected $method;
    protected $event;
    public function __construct(ServiceAccurateOrderRequest $ServiceAolOrderRequest, $method, $event)
    {
        $this->ServiceAolOrderRequest = $ServiceAolOrderRequest;
        $this->event = $event;
        $this->method = $method;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (\Str::upper($this->method) == 'SAVE') {
            $this->ServiceAolOrderRequest->SAVE($this->event);
        }
        if (\Str::upper($this->method) == 'TOAPP') {
            $this->ServiceAolOrderRequest->insertToApp($this->event);
        }
    }
}
