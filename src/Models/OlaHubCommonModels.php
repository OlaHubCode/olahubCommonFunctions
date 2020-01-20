<?php

namespace OlaHub\Models;

class OlaHubCommonModels extends OlaHubCommonModelsHelper {

    protected $dates = ['deleted_at'];
    protected $guarded = array('created_at', 'updated_at', 'deleted_at', 'id');
    public $setLogUser = true;
    public $manyToManyFilters = [];
    public $statusColumn = '';
    protected $columnsMaping = [];
    protected $requestValidationRules = [];
    public $orderByString = '';
}

