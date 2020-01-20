<?php

namespace OlaHub\Repositories;

abstract class OlaHubCommonRepositoriesHelpers {

    /////////////////////////////////////////////////////////////////////
    //   Public functions uses outside class as getters and setters   //
    ///////////////////////////////////////////////////////////////////

    public function getRequestValidationRules($validationType = 'validation') {
        return $this->mutation->getValidationsRules($validationType);
    }

    public function getModelColumnsMaping() {
        return $this->mutation->getColumnsMaping();
    }
    
    public function setExceptGlobalScope($scopeName){
        return $this->query->withoutGlobalScope($scopeName);
    }

    //////////////////////////////////////////////////////////
    //  Protected functions used to handling data formats  //
    ////////////////////////////////////////////////////////

    protected function setRelationData() {
        foreach ($this->columnsValue as $model => $willSaveData) {
            if ($model != 'in' && $model != 'sync' && $model != 'main') {
                $this->relationData[$model] = $willSaveData;
            }
        }
    }

    protected function setSyncData() {
        if (isset($this->columnsValue['sync'])) {
            foreach ($this->columnsValue['sync'] as $model => $willSaveData) {
                $this->syncData[$model] = $willSaveData;
            }
        }
    }

    protected function syncDataSave($modelObject) {
        foreach ($this->syncData as $model => $willSaveData) {
            $additionalData = $this->mutation->getSyncAdditionalData($model, $modelObject);
            if (is_array($additionalData) && count($additionalData) > 0) {
                $saveData = array_merge($additionalData, $willSaveData);
            } else {
                $saveData = $willSaveData;
            }
            $modelObject->$model()->sync($saveData);
        }
    }

    protected function relationDataSave($id = false) {
        foreach ($this->relationData as $model => $willSaveData) {
            foreach ($willSaveData as $oneRow) {
                $manyQuery = new $model;
                if (isset($oneRow['localID']) && $oneRow['localID'] > 0) {
                    $this->updateExistRelationEntry($oneRow, $manyQuery);
                } else {
                    $this->createNewRelationEntry($oneRow, $manyQuery, $id);
                }
            }
        }
    }

    protected function createNewRelationEntry($oneRow, $manyQuery, $id) {
        $oneRow[$manyQuery->localKey] = $id;
        $manyQuery->create($oneRow);
    }

    protected function updateExistRelationEntry($oneRow, $manyQuery) {
        $relationID = $oneRow['localID'];
        unset($oneRow['localID']);
        $manyQuery->where($manyQuery->getKeyName(), (int) $relationID)->update($oneRow);
    }

    protected function setFilterData($criteria) {
        if (count($criteria) > 0) {
            foreach ($criteria as $columnName => $filterValue) {
                if (isset($filterValue['func']) && $filterValue['func']) {
                    $this->handlingFilterTypesRelationMaping($columnName, $filterValue['func'], $filterValue['type'], $filterValue['value']);
                } else {
                    $this->handlingFilterTypesMaping($columnName, $filterValue['type'], $filterValue['value']);
                }
            }
        }
        if ($this->filterCount) {
            return true;
        }
        return FALSE;
    }

    protected function handlingFilterTypesRelationMaping($columnName, $filterFunc, $filterType, $filterValue) {

        $this->query->whereHas($filterFunc, function ($q) use($columnName, $filterType, $filterValue) {
            switch ($filterType) {
                case 'is':
                    if (is_array($filterValue)) {
                        $q->whereIn($columnName, $filterValue);
                    } else {
                        $q->where($columnName, $filterValue);
                    }
                    $this->filterCount += 1;
                    break;
                case 'not_is':
                    if (is_array($value)) {
                        $q->whereNotIn($columnName, $filterValue);
                    } else {
                        $q->where($columnName, '!=', $filterValue);
                    }
                    $this->filterCount += 1;
                    break;
                case 'match':
                    if (is_string($value)) {
                        $q->where($columnName, 'like', "%$filterValue%");
                        $this->filterCount += 1;
                    }
                    break;
                case 'from':
                    $q->where($columnName, '>=', $filterValue);
                    $this->filterCount += 1;
                    break;
                case 'to':
                    $q->where($columnName, '<=', $filterValue);
                    $this->filterCount += 1;
                    break;
                case 'ordring':
                    $q->orderBy($columnName, $filterValue);
                    $this->filterCount += 1;
                    break;
                case 'grouping':
                    $q->groupBy($columnName, $filterValue);
                    $this->filterCount += 1;
                    break;
            }
        });
    }

    protected function handlingFilterTypesMaping($columnName, $filterType, $filterValue) {
        switch ($filterType) {
            case 'null':
                    $this->query->whereNull($columnName);
                $this->filterCount += 1;
                break;
            case 'is':
                if (is_array($filterValue)) {
                    $this->query->whereIn($columnName, $filterValue);
                } else {
                    $this->query->where($columnName, $filterValue);
                }
                $this->filterCount += 1;
                break;
            case 'not_is':
                if (is_array($filterValue)) {
                    $this->query->whereNotIn($columnName, $filterValue);
                } else {
                    $this->query->where($columnName, '!=', $filterValue);
                }
                $this->filterCount += 1;
                break;
            case 'match':
                if (is_string($filterValue)) {
                    $this->query->where($columnName, 'like', "%$filterValue%");
                    $this->filterCount += 1;
                }
                break;
            case 'from':
                $this->query->where($columnName, '>=', $filterValue);
                $this->filterCount += 1;
                break;
            case 'to':
                $this->query->where($columnName, '<=', $filterValue);
                $this->filterCount += 1;
                break;
            case 'ordring':
                $this->query->orderBy($columnName, $filterValue);
                $this->filterCount += 1;
                break;
            case 'grouping':
                $this->query->groupBy($columnName, $filterValue);
                $this->filterCount += 1;
                break;
        }
    }

    protected function setColumnsDataValues($data) {
        $this->columnsValue = $data;
    }

    protected function saveMainData() {
        if (isset($this->columnsValue['main'])) {
            foreach ($this->columnsValue['main'] as $model => $columns) {
                $manyQuery = new $model;
                $mainID = $manyQuery->create($columns);
            }
            $this->columnsValue['in'][$this->mutation->localKey] = (int) $mainID->id;
            $this->status = $this->query->create($this->columnsValue['in']);
        } else {
            $this->status = $this->query->create($this->columnsValue['in']);
        }
    }

    protected function updateMainData($id, $item) {
        if (isset($this->columnsValue['main'])) {
            foreach ($this->columnsValue['main'] as $model => $columns) {
                $manyQuery = new $model;
                $mainID = $manyQuery->where($manyQuery->getKeyName(), $item->{$this->mutation->localKey})->update($columns);
            }
            $q = $this->query->find((int) $id);
            foreach ($this->columnsValue['in'] as $key => $val) {
                $q->$key = $val;
            }
            $this->status = $q->save();
        } elseif (isset($this->columnsValue['in'])) {
            $q = $this->query->find((int) $id);
            foreach ($this->columnsValue['in'] as $key => $val) {
                $q->$key = $val;
            }
            $this->status = $q->save();
        } else {
            $this->status = 'noIn';
        }
    }

}
