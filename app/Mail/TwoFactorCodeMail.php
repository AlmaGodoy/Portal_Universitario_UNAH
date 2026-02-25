<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TwoFactorCodeMail extends Mailable
{
    public function __construct(public string $code) {}

    public function build()
    {
        return $this->subject('Código de verificación (2FA)')
            ->view('emails.two_factor_code');
    }
}
