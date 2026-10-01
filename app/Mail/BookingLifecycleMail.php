<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class BookingLifecycleMail extends Mailable
{
    public function __construct(public array $details)
    {
    }

    public function build(): self
    {
        return $this->subject(
            $this->details['subject'] ?? 'BhaktiDeep booking update'
        )
            ->view('emails.booking-lifecycle')
            ->with([
                'details' => $this->details,
            ]);
    }
}
