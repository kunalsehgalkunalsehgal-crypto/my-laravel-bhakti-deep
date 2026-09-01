<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class BookingRefundMail extends Mailable
{
    public function __construct(public string $refundMessage)
    {
    }

    public function build()
    {
        return $this->subject('BhaktiDeep booking refund update')
            ->view('emails.booking-refund');
    }
}
