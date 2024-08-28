<?php

namespace App\Events\Vouchers;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;


class VouchersNotCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param [] $vouchers
     * @param User $user
     */
    public function __construct(
        public readonly array $vouchers,
        public readonly User $user
    ) {
    }
}
