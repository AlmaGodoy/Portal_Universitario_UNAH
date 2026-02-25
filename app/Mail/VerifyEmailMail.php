<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VerifyEmailMail extends Mailable
{
    public string $link;

    public function __construct(string $link)
    {
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('Activa tu cuenta - Portal UNAH')
            ->view('emails.verify_email');
    }
}
