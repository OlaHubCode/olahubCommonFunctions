<?php


namespace App\Mail;

use Illuminate\Mail\Mailable;

class EmailFunction extends Mailable
{
    protected $body;
    protected $emailSubject;
    protected $emailCC;
    protected $emailfromName;
    protected $emailfromEmail;
    
    public function __construct($stringBody,$stringSubject,$ccMail,$stringFromName = 'OlaHub system',$stringFromMail = 'no-replay@olahub.com') {
        $this->body = $stringBody;
        $this->emailSubject = $stringSubject;
        $this->emailCC = $ccMail;
        $this->emailfromName = $stringFromName;
        $this->emailfromEmail = $stringFromMail;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if(is_array($this->emailCC)){
            foreach ($this->emailCC as $one){
                if(is_array($one)){
                    if(count($one) == 2){
                        $this->cc($one[0], $one[1]);
                    }else{
                        $this->cc($one[0]);
                    }
                }else{
                    $this->cc($one);
                }
            }
        }
        return $this->subject($this->emailSubject)->from($this->emailfromEmail, $this->emailfromName)->view('sendEmail',['body' => $this->body]);
    }
}