<?php

namespace App\Jobs;

use App\Services\VoucherService;
use Illuminate\Auth\Authenticatable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveVouchers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $xmlContents;
    protected $user;
    protected $voucherService;


    /**
     * Create a new job instance.
     */
    public function __construct(array $xmlContents,  $user)
    {
        $this->xmlContents = $xmlContents;
        $this->user = $user;
        $this->voucherService = new VoucherService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->voucherService->storeVouchersFromXmlContents($this->xmlContents, $this->user);
    }
}
