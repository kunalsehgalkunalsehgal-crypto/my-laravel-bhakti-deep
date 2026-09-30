<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PaymentSuccessfulMail extends Mailable
{
    public function __construct(public array $details)
    {
    }

    public function build(): self
    {
        return $this->subject(
            $this->details['subject']
            ?? 'Payment successful | BhaktiDeep'
        )
            ->view('emails.payment-successful')
            ->with([
                'details' => $this->details,
            ]);
    }
}