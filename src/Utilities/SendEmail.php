<?php

namespace OlaHub\Libraries;

use App\Mail\EmailFunction;
use Illuminate\Support\Facades\Mail;

class SendEmails {

    public
            $subject,
            $body,
            $to,
            $ccMail;

    function send() {
        Mail::to($this->to)->send(new EmailFunction($this->body, $this->subject,  $this->ccMail));
        return true;
    }

}
