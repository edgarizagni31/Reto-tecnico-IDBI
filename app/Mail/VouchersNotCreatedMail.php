<?php

namespace App\Mail;

use App\Models\User;
use App\Services\VoucherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Log;
use Storage;
use Str;

class VouchersNotCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $vouchers;
    public User $user;

    public function __construct(array $vouchers, User $user)
    {
        $this->vouchers = $vouchers;
        $this->user = $user;
    }

    public function build(): self
    {
        $vouchersWithFile = array_filter( $this->vouchers, function($voucher) {
            return $voucher['file_was_created'];
        });
        $email = $this->view('emails.wrong-voucher')
            ->with(['comprobantes' => $this->vouchers, 'user' => $this->user]);

        foreach ($vouchersWithFile as $voucher) {
            $email->attach($voucher['file'], [
                'as' => basename($voucher['file']),
                'mime' => 'application/xml',
            ]);
        }

        return $email;
    }
}
