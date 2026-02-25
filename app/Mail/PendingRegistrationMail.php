<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $link;
    public string $tipo;

    public function __construct(string $link, string $tipo)
    {
        $this->link = $link;
        $this->tipo = $tipo;
    }

    public function build()
    {
        return $this->subject('Confirma tu registro - Portal UNAH')
            ->view('emails.pending_registration');
    }
}
