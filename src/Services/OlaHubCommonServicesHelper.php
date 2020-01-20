<?php

namespace OlaHub\Services;

use Validator;
use \League\Fractal\Manager;
use \League\Fractal\Resource\Collection as FractalCollection;
use \League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

abstract class OlaHubCommonServicesHelper {

    public function setRequestData($request) {
        if ($request) {
            $this->requestData = $request;
            if ($this->countryHeader) {
                $this->requestData[$this->countryColumnName] = $this->countryHeader;
            }
        } else {
            $this->requestData = [];
        }
    }

    public function updateRequestData($column, $value) {
        $this->requestData[$column] = $value;
    }

    public function setRequestFilter($request) {
        $this->requestFilter = $request;
        if ($this->countryHeader) {
            $this->requestFilter[$this->countryColumnName]['is'] = $this->countryHeader;
        }
    }

    public function updateRequestFilter($column, $type, $value) {
        $this->requestFilter[$column][$type] = $value;
    }

    protected function setPublish($status, $repo) {
        $this->columnsValidation[$repo->statusColumn] = $status;
    }

    protected function checkValidation($repo) {
        if ($this->columnsValidation) {
            $this->requestValidator = Validator::make($this->columnsValidation, $repo->getRequestValidationRules());
            if ($this->requestValidator->fails()) {
                return false;
            }
            return true;
        }
        return false;
    }

    protected function mapDataNaming($repo, $status = false) {
        $columnsMaping = $repo->getModelColumnsMaping();
        foreach ($this->columnsValidation as $name => $value) {
            if (isset($columnsMaping[$name])) {
                $this->checkColumns($columnsMaping, $name, $value);
            }
        }
    }

    protected function mapFilterNaming($columnsMaping, $columnName, $columData) {
        if (isset($columnsMaping[$columnName])) {
            foreach ($columData as $name => $value) {
                $mapingData = $columnsMaping[$columnName];
                $this->criteria[$mapingData['filterColumn']]['type'] = $name;
                isset($mapingData['filterFunc']) ? $this->criteria[$mapingData['filterColumn']]['func'] = $mapingData['filterFunc'] : "";
                $this->criteria[$mapingData['filterColumn']]['value'] = $value;
            }
        }
    }

    protected function checkColumns($columnsMaping, $name, $value) {
        if (isset($columnsMaping[$name]['manyToMany']) && $columnsMaping[$name]['manyToMany']) {
            $this->setColumnsValuesRelation($columnsMaping[$name], $value);
        } elseif (isset($columnsMaping[$name]['syncData']) && $columnsMaping[$name]['syncData']) {
            $this->setColumnsValuesSync($columnsMaping[$name], $value);
        } elseif (isset($columnsMaping[$name]['mainTable']) && $columnsMaping[$name]['mainTable']) {
            $this->setColumnsValuesMainTable($columnsMaping[$name], $value);
        } else {
            $this->setColumnsValues($columnsMaping[$name], $value);
        }
    }

    protected function setColumnsValuesMainTable($mapData, $columnValues) {
        $checkVal = $this->checkTypes($mapData['type'], $columnValues);
        if ($checkVal) {
            $this->columnsValues['main'][$mapData['mainTable']][$mapData['column']] = $checkVal;
        }
    }

    protected function setColumnsValuesRelation($mapData, $columnValues) {
        $manyToManyRepo = new \OlaHub\Repositories\OlaHubCommonRepositories($mapData['manyToMany']);
        $dataMaping = $manyToManyRepo->getModelColumnsMaping();
        foreach ($columnValues as $key => $columnValue) {
            foreach ($columnValue as $dataKey => $dataValue) {
                if (isset($dataMaping[$dataKey])) {
                    $checkVal = $this->checkTypes($dataMaping[$dataKey]['type'], $dataValue);
                    if ($checkVal) {
                        $this->columnsValues[$mapData['manyToMany']][$key][$dataMaping[$dataKey]['column']] = $checkVal;
                    }
                }
            }
        }
    }

    protected function setColumnsValuesSync($mapData, $columnValues) {
        $this->columnsValues['sync'][$mapData['syncData']] = $columnValues;
    }

    protected function setColumnsValues($mapData, $columnValues) {
        $checkVal = $this->checkTypes($mapData['type'], $columnValues);
        if ($checkVal) {
            $this->columnsValues['in'][$mapData['column']] = $checkVal;
        }
    }

    protected function uploadFile($model, $new) {
        if (isset($this->requestData[$this->uploadFieldName])) {
            $conexion = \OlaHub\Libraries\APIAlfresco::getInstance();
            $request = \Illuminate\Http\Request::capture();
            try {
                $picName = $this->requestData[$this->uploadFieldName]->getPathname();
                $conexion->connect(env('ALFRES_URL'), env('ALFRES_USERNAME'), env('ALFRES_PASSWORD'));
                if ($new) {
                    $conexion->setFolderByPath($this->uploadFolderName);
                    $conexion->createFolder($model->id);
                }
                $conexion->setFolderByPath($this->uploadFolderName . "/$model->id");
                $file = $conexion->uploadFile($picName);
                return $this->handleUploaldFile($file, $model, $new);
            } catch (Exception $e) {
                return false;
            }
        }
        return $model;
    }

    private function handleUploaldFile($file, $model, $new) {
        $this->columnsValidation[$this->uploadFieldName] = str_replace(";1.0", '', $file->id);
        $this->mapDataNaming($this->repo, false);
        $updated = $this->repo->updateDataByID($model->id, $this->columnsValues);
        if ($updated) {
            if (!$new && $model->{$this->imageColumn}) {
                try {
                    $conexion = \OlaHub\Libraries\APIAlfresco::getInstance();
                    $conexion->delete($model->{$this->imageColumn});
                } catch (Exception $e) {
                    return FALSE;
                }
            }
        }
        return $updated;
    }

    protected function checkTypes($type, $value, $new = true) {
        switch ($type) {
            case 'json':
                return json_encode($value);
            case 'int':
                return (int) $value;
            case 'file':
                if (strpos($value, "/tmp/") === false) {
                    return $value;
                }
                return false;
            case 'double':
                return (double) $value;
            case 'serialize':
                return serialize($value);
            case 'multiLang':
                return \OlaHub\Helpers\OlaHubCommonHelper::translate($value);
            case 'bool':
                return $value ? 1 : 0;
            case 'date':
                return date('Y-m-d h:i:s', strtotime($value));
            default :
                return $value;
        }
    }

    protected function handlingRequestData($repo = false, $status = false) {
        if (isset($this->requestData)) {
            foreach ($this->requestData as $key => $value) {
                $this->columnsValidation[$key] = $value;
            }
        }
        if ($status !== false) {
            $this->setPublish($status, $repo);
        }
    }

    protected function handlingRequestFilter($repo) {
        if (isset($this->requestFilter)) {
            $this->filterValidator = $repo->getRequestValidationRules('filterValidation');
            $columnsMaping = $repo->getModelColumnsMaping();
            foreach ($this->requestFilter as $columnName => $data) {
                if (!in_array($columnName, $this->requestIgnoredFilterKeys) && $this->validateFilterData($columnName, $data)) {
                    $this->mapFilterNaming($columnsMaping, $columnName, $data);
                }
            }
        }
    }

    protected function handlingResponseItem($data, $responseHandler = false) {
        if (!$responseHandler) {
            $responseHandler = $this->responseHandler;
        }
        $fractal = new Manager();
        $resource = new FractalItem($data, new $responseHandler);
        return $fractal->createData($resource)->toArray();
    }

    protected function handlingResponseCollection($data, $responseHandler = false) {
        if (!$responseHandler) {
            $responseHandler = $this->responseHandler;
        }
        $collection = $data;
        $fractal = new Manager();
        $resource = new FractalCollection($collection, new $responseHandler);
        return $fractal->createData($resource)->toArray();
    }

    protected function handlingResponseCollectionPginate($data, $responseHandler = false) {
        if (!$responseHandler) {
            $responseHandler = $this->responseHandler;
        }
        $collection = $data->getCollection();
        $fractal = new Manager();
        $resource = new FractalCollection($collection, new $responseHandler);
        $resource->setPaginator(new IlluminatePaginatorAdapter($data));
        return $fractal->createData($resource)->toArray();
    }

    protected function validateFilterData($column, $data) {
        $return = true;
        if (isset($this->filterValidator[$column])) {
            foreach ($data as $filter) {
                if (is_array($filter)) {
                    foreach ($filter as $one) {
                        $validator = Validator::make([$column => $one], $this->filterValidator);
                        if ($validator->fails()) {
                            return false;
                        }
                    }
                } else {
                    $validator = Validator::make([$column => $filter], $this->filterValidator);
                    if ($validator->fails()) {
                        return false;
                    }
                }
            }
        }

        return $return;
    }

    protected function handlingRequestUniqueFilter($repo) {
        if (isset($this->requestData)) {
            $columnsMaping = $repo->getModelColumnsMaping();
            foreach ($this->requestData as $columnName => $data) {
                $this->mapUniqueFilterNaming($columnsMaping, $columnName, $data);
            }
        }
    }

    protected function mapUniqueFilterNaming($columnsMaping, $columnName, $columData) {
        if (isset($columnsMaping[$columnName])) {
            $mapingData = $columnsMaping[$columnName];
            $this->criteria[$mapingData['column']] = [
                'type' => 'is',
                'func' => isset($mapingData['filterFunc']) ? $mapingData['filterFunc'] : false,
                'value' => $columData,
            ];
        }
    }

}
