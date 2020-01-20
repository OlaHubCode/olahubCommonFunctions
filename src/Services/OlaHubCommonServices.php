<?php

namespace OlaHub\Services;

abstract class OlaHubCommonServices extends OlaHubCommonServicesHelper {

    protected $repo;
    protected $select;
    protected $requestData;
    protected $requestFilter;
    protected $columnsValues;
    protected $columnsValidation;
    protected $requestValidator;
    protected $criteria;
    protected $responseHandler;
    protected $requestIgnoredFilterKeys;
    protected $filterValidator;
    public $countryHeader = false;
    public $portalUtilities;
    protected $countryColumnName;
    protected $uploadFieldName;
    public $uploadFolderName;
    public $imageColumn;
    public $trash;

    public function __construct($countryHeader = false) {
        $this->select = ['*'];
        $this->columnsValues = [];
        $this->criteria = [];
        $this->requestIgnoredFilterKeys = ['page'];
        $this->filterValidator = [];
        $this->trash = FALSE;
        $this->countryHeader = $countryHeader;
    }

    function getAll() {
        $data = $this->repo->findAll($this->select, $this->trash);
        if (!$data || $data->count() <= 0) {
            return ['status' => false, 'msg' => 'No data found'];
        }
        $response = $this->handlingResponseCollection($data);
        $response['status'] = true;
        return $response;
    }

    function getPagination() {

        $data = $this->repo->findAllPaginate($this->select, $this->trash);
        if (!$data || $data->count() <= 0) {
            return ['status' => false, 'msg' => 'No data found'];
        }
        $response = $this->handlingResponseCollectionPginate($data);
        $response['status'] = true;
        return $response;
    }

    function getAllCeriatria() {
        $this->handlingRequestFilter($this->repo);
        $data = $this->repo->findBy($this->criteria, $this->select, $this->trash);
        if (!$data || $data->count() <= 0) {
            return ['status' => false, 'msg' => 'No data found'];
        }
        $response = $this->handlingResponseCollection($data);
        $response['status'] = true;
        return $response;
    }

    function getPaginationCeriatria() {
        $this->handlingRequestFilter($this->repo);
        $data = $this->repo->findPaginateBy($this->criteria, $this->select, $this->trash);
        if (!$data || $data == 'no_filter' || $data->count() <= 0) {
            return ['status' => false, 'msg' => 'No data found'];
        }
        $response = $this->handlingResponseCollectionPginate($data);
        $response['status'] = true;
        return $response;
    }

    function getOneByID($id) {
        $data = $this->repo->findOneID($id, $this->criteria, $this->select, $this->trash);
        if (!$data) {
            return ['status' => false, 'msg' => 'No data found'];
        }
        $response = $this->handlingResponseItem($data);
        $response['status'] = true;
        return $response;
    }

    function getOneByFilter() {
        $this->handlingRequestFilter($this->repo);
        $data = $this->repo->findOneBy($this->criteria, $this->select, $this->trash);
        if (!$data) {
            return ['status' => false, 'msg' => 'No data found'];
        }
        $response = $this->handlingResponseItem($data);
        $response['status'] = true;
        return $response;
    }

    function saveNewData() {
        $this->handlingRequestData();
        if (!$this->checkValidation($this->repo)) {
            return ['status' => false, 'msg' => 'Some data is wrong'];
//            return ['error' => 415, 'msg' => $this->requestValidator->errors()->toArray()];
        }
        $this->mapDataNaming($this->repo);
        $saved = $this->repo->createNewData($this->columnsValues);
        if (array_key_exists('error', $saved)) {
            if (env('APP_ENV') == 'local') {
                return ['status' => false, 'msg' => $saved['msg']];
            }
            return ['status' => false, 'msg' => 'An error has been occured'];
        }
        $finalData = $this->uploadFile($saved, true);
        $response = $this->handlingResponseItem($finalData);
        $response['status'] = true;
        return $response;
    }

    function updateByID($id, $status = false) {
        config(['currentID' => $id]);
        $this->handlingRequestData($this->repo, $status);
        $this->handlingRequestFilter($this->repo);
        if ($status === false && !$this->checkValidation($this->repo)) {
            return ['status' => false, 'msg' => 'Some data is wrong'];
//            return ['error' => 415, 'msg' => $this->requestValidator->errors()->toArray()];
        }
        $this->mapDataNaming($this->repo, $status);
        $updated = $this->repo->updateDataByID($id, $this->columnsValues);
        if (array_key_exists('error', $updated)) {
            if (env('APP_ENV') == 'local') {
                return ['status' => false, 'msg' => $updated['msg']];
            }
            return ['status' => false, 'msg' => 'An error has been occured'];
        }

        if ($updated == 'no_data') {
            return ['status' => false, 'msg' => 'No data found'];
        }
        $finalData = $this->uploadFile($updated, false);
        $response = $this->handlingResponseItem($finalData);
        $response['status'] = true;
        return $response;
    }

    function updateByFilter($status = false) {
        $this->setPublish($status);
        $this->handlingRequestData();
        $this->handlingRequestFilter($this->repo);

        if (!$this->checkValidation()) {
            return ['status' => false, 'msg' => 'Some data is wrong'];
//            return ['error' => 415, 'msg' => $this->requestValidator->errors()->toArray()];
        }
        $updated = $this->repo->updateDataByFilter($this->criteria, $this->columnsValues);
        if (array_key_exists('error', $updated)) {
            if (env('APP_ENV') == 'local') {
                return ['status' => false, 'msg' => $updated['msg']];
            }
            return ['status' => false, 'msg' => 'An error has been occured'];
        }

        if ($updated == 'no_data') {
            return ['status' => false, 'msg' => 'No data found'];
        }
        $response = $this->handlingResponseCollection($updated);
        $response['status'] = true;
        return $response;
    }

    function deleteById($id) {
        $deleted = $this->repo->deleteDataByID($id, $this->trash);
        if (!$deleted) {
            return ['status' => false, 'msg' => 'An error has been occured'];
        } elseif ($deleted == 'no_data') {
            return ['status' => false, 'msg' => 'No data found'];
        }
        return ['status' => true, 'msg' => 'Data has been deleted successfully'];
    }

    function deleteByFilter() {
        $this->handlingRequestFilter($this->repo);
        $deleted = $this->repo->deleteDataByFilter($this->criteria, $this->trash);
        if (!$deleted) {
            return ['status' => false, 'msg' => 'An error has been occured'];
        } elseif ($deleted == 'no_data') {
            return ['status' => false, 'msg' => 'No data found'];
        }
        return ['status' => true, 'msg' => 'Data has been deleted successfully'];
    }

    function restoreById($id) {
        $restored = $this->repo->restoreDataByID($id);
        if (!$restored) {
            return ['status' => false, 'msg' => 'An error has been occured'];
        } elseif ($restored == 'no_data') {
            return ['status' => false, 'msg' => 'No data found'];
        }
        $response = $this->handlingResponseItem($restored);
        $response['status'] = true;
        return $response;
    }

    function restoreByFilter() {
        $this->handlingRequestFilter($this->repo);
        $restored = $this->repo->restoreDataByFilter($this->criteria);
        if (!$restored) {
            return ['status' => false, 'msg' => 'An error has been occured'];
        } elseif ($restored == 'no_data') {
            return ['status' => false, 'msg' => 'No data found'];
        }
        $response = $this->handlingResponseCollection($restored);
        $response['status'] = true;
        return $response;
    }

    public function checkUniqueData() {
        $this->handlingRequestUniqueFilter($this->repo);
        $data = $this->repo->findOneBy($this->criteria, $this->select, $this->trash);
        if (!$data || $data->count() <= 0) {
            return ['status' => true, 'msg' => 'Item is not exist (is Unique)'];
        } else {
            return ['status' => false, 'msg' => 'Item already exist (not Unique)'];
        }
    }

}
