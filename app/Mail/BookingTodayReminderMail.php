<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class BookingTodayReminderMail extends Mailable
{
    public function __construct(public array $details) {}

    public function build(): self
    {
        return $this->subject(
            $this->details['subject'] ?? 'Today booking reminder | BhaktiDeep'
        )
            ->view('emails.booking-today-reminder')
            ->with(['details' => $this->details]);
    }
}
