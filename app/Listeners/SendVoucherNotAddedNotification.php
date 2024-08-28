<?php

namespace App\Listeners;

use App\Events\Vouchers\VouchersNotCreated;
use App\Mail\VouchersNotCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendVoucherNotAddedNotification implements ShouldQueue
{
    public function handle(VouchersNotCreated $event): void
    {
        $mail = new VouchersNotCreatedMail($event->vouchers, $event->user);

        Mail::to($event->user->email)->send($mail);
    }
}
