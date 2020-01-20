<?php

namespace OlaHub\Models;

use Illuminate\Database\Eloquent\Model;

class OlaHubCommonModelsHelper extends Model {

    protected $defaultColumns = [];

    public static function boot() {
        parent::boot();
    }

    public function getValidationsRules($validationType = 'validation') {
        foreach ($this->columnsMaping as $dataName => $oneColumn) {
            if (isset($oneColumn[$validationType])) {
                $this->requestValidationRules[$dataName] = $oneColumn[$validationType];
            }
        }
        $this->setDefaultValidation($validationType);
        return $this->requestValidationRules;
    }

    protected function setDefaultValidation($validationType) {
        foreach ($this->defaultColumns as $dataName => $oneColumn) {
            if (isset($oneColumn[$validationType])) {
                $this->requestValidationRules[$dataName] = $oneColumn[$validationType];
            }
        }
    }

    function getManyToManyFilters() {
        return $this->manyToManyFilters;
    }

    function getColumnsMaping() {
        return $this->columnsMaping;
    }

    function additionalQueriesFired() {
        
    }

    function getSyncAdditionalData($syncName,$model) {
        
    }

}
