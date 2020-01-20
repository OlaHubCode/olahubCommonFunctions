<?php

/**
 * DTemplates Services
 * This the main class that used to send/recieved data from/to API
 * all functions return with Json object
 * 
 * @author Mohamed EL-Absy <mohamed.elabsy@yahoo.com>
 * @copyright (c) 2018, OlaHub LLC
 * @version 1.0.0
 */

namespace OlaHub\Services;

use OlaHub\Repositories\DTemplatesRepository;

class DTemplatesServices extends OlaHubCommonServices {

    public function __construct() {
        parent::__construct();
        $this->repo = new DTemplatesRepository('OlaHub\Models\DTemplate');
        $this->responseHandler = '\OlaHub\ResponseHandlers\DTemplatesResponseHandler';
    }
}