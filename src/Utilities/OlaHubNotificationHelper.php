<?php

namespace OlaHub\Libraries;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OlaHubNotificationHelper {

    public $type = 'email';
    public $template_code;
    public $to;
    public $cc;
    public $replace;
    public $replace_with;
    private $body;
    private $subject;
    private $final_to;
    private $final_cc = [];
    private $template_data;
    private $notifTypes = [
        'email',
        'sms',
        'timeline',
        'alert',
    ];

    public function __construct() {
        
    }

    public function send() {
        if (in_array($this->type, $this->notifTypes)) {
            $this->{'send' . $this->type}();
        }
    }

    protected function sendemail() {
        $this->getTemplateData();
        $this->getSubjectData();
        $this->getBodyData();
        $this->getTo();
        $this->getCC();
        $sendEmail = new \OlaHub\Libraries\SendEmails;
        $sendEmail->subject = $this->subject;
        $sendEmail->body = $this->body;
        $sendEmail->to = $this->final_to;
        $sendEmail->ccMail = $this->final_cc;
        $sendEmail->send();
    }

    private function getTemplateData() {
        if ($this->template_code) {
            $this->template_data = \OlaHub\Models\MessageTemplate::where('code', $this->template_code)->first();
        }
        $this->checkVarValue('template_data');
    }

    private function getSubjectData() {
        $this->subject = \OlaHub\Helpers\OlaHubCommonHelper::returnCurrentLangField($this->template_data, "subject");
        $this->checkVarValue('subject');
    }

    private function getBodyData() {
        $this->body = \OlaHub\Helpers\OlaHubCommonHelper::returnCurrentLangField($this->template_data, "body");
        $this->checkVarValue('body');
        if ($this->replace && $this->replace_with) {
            $this->body = str_replace($this->replace, $this->replace_with, $this->body);
        }
    }

    private function getTo() {
        if (is_array($this->to)) {
            foreach ($this->to as $one) {
                if (is_array($one)) {
                    if (count($one) == 2) {
                        $this->final_to[] = [
                            'email' => $one[0],
                            'name' => $one[1],
                        ];
                    } else {
                        $this->final_to[] = [
                            'email' => $one[0]
                        ];
                    }
                } else {
                    $this->final_to = $this->to;
                }
            }
        } else {
            $this->final_to = $this->to;
        }
        $this->checkVarValue('final_to');
    }

    private function getCC() {
        if (is_array($this->cc) && count($this->cc)) {
            $this->final_cc = $this->cc;
        } elseif (strlen($this->cc) > 0) {
            $this->final_cc[] = $this->cc;
        }
        array_push($this->final_cc, ['mohamed.elabsy@olahub.com']);
    }

    private function checkVarValue($var) {
        if (!$this->$var || $this->$var == 'N/A') {
            throw new BadRequestHttpException(404);
        }
    }

}
