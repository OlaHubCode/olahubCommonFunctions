<?php

namespace OlaHub\Repositories;

class OlaHubCommonRepositories extends OlaHubCommonRepositoriesHelpers {

    protected $query;
    protected $mutation;
    protected $columnsValue;
    protected $filterCount = 0;
    protected $relationData = [];
    protected $syncData = [];
    protected $status = false;
    public $statusColumn;
    public $orderBy;

    public function __construct($modelName) {
        $this->query = (new $modelName)->newQuery();
        $this->mutation = new $modelName;
        $this->statusColumn = $this->mutation->statusColumn;
        $this->orderBy = $this->mutation->orderByString;
    }

    /*
     * ---------------------------
     * |                         |
     * | Start queries functions |
     * |                         |
     * ---------------------------
     */

    ///////////////////////////////////
    //   Insert & Updates queries   //
    /////////////////////////////////

    public function createNewData(array $data) {
        try {
            \DB::connection()->beginTransaction();
            $this->setColumnsDataValues($data);
            $this->setRelationData();
            $this->setSyncData();
            $this->saveMainData();
            if ($this->status) {
                $this->relationDataSave($this->status->id);
                $newData = $this->findOneID((int) $this->status->id);
                $this->syncDataSave($newData);
            }
            \DB::connection()->commit();
            return $newData;
        } catch (\PDOException $e) {
            \DB::connection()->rollBack();
            return ['error' => '1', 'msg' => $e->getMessage()];
        }
    }

    public function updateDataByID($id, array $data, $criatria = []) {
        $item = $this->findOneID((int) $id, $criatria);
        if (!$item) {
            return 'no_data';
        }
        $this->status = false;
        try {
            \DB::connection()->beginTransaction();
            $this->setColumnsDataValues($data);
            $this->setRelationData();
            $this->setSyncData();
            $this->updateMainData((int) $id, $item);
            if ($this->status) {
                $this->relationDataSave((int) $id);
                $updatedData = $this->findOneID((int) $id);
                $this->syncDataSave($updatedData);
            }
            \DB::connection()->commit();
            return $updatedData;
        } catch (\PDOException $e) {
            \DB::connection()->rollBack();
            return ['error' => '1', 'msg' => $e->getMessage()];
        }
    }

    ///////////////////////////////////
    //   Delete & restore queries   //
    /////////////////////////////////

    public function deleteDataByID($id, $trash) {
        $item = $this->findOneID((int) $id, [], ['*'], $trash);
        if (!$item) {
            return 'no_data';
        }
        if ($trash) {
            $deleted = $this->query->where($this->mutation->getKeyName(), (int) $id)->forceDelete();
        } else {
            $deleted = $this->query->where($this->mutation->getKeyName(), (int) $id)->delete();
        }

        if ($deleted) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteDataByFilter(array $criteria, $trash) {
        $item = $this->findBy($criteria, ['*'], $trash);
        if ($item->count() <= 0) {
            return 'no_data';
        }
        if ($this->setFilterData($criteria)) {
            if ($trash) {
                $deleted = $this->query->forceDelete();
            } else {
                $deleted = $this->query->delete();
            }
            if ($deleted) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function restoreDataByID($id) {
        $item = $this->findOneID((int) $id, ['*'], true);
        if (!$item) {
            return 'no_data';
        }
        $this->query->withTrashed();
        $restored = $this->query->where($this->mutation->getKeyName(), (int) $id)->restore();
        if ($restored) {
            return $this->findOneID((int) $id);
        } else {
            return false;
        }
    }

    public function restoreDataByFilter(array $criteria) {
        $item = $this->findBy($criteria, ['*'], true);
        if (!$item) {
            return 'no_data';
        }
        $this->query->withTrashed();
        if ($this->setFilterData($criteria)) {
            $restored = $this->query->restore();
            if ($restored) {
                return $this->findBy($criteria);
            } else {
                return false;
            }
        }
        return false;
    }

    //////////////////////////////////
    //      Filtering queries      //
    ////////////////////////////////

    public function findAll($select = ['*'], $trashed = false) {
        if ($trashed) {
            $this->query->onlyTrashed();
        }
        if (!empty($this->orderBy)) {
            $this->query->orderByRaw($this->orderBy);
        }
        return $this->query->get($select);
    }

    public function findAllPaginate($select = ['*'], $trashed = false) {
        if ($trashed) {
            $this->query->onlyTrashed();
        }
        if (!empty($this->orderBy)) {
            $this->query->orderByRaw($this->orderBy);
        }

        return $this->query->paginate(env('PAGINATION_COUNT'), $select);
    }

    public function findPaginateBy(array $criteria, $select = ['*'], $trashed = false) {
        if ($trashed) {
            $this->query->onlyTrashed();
        }

        if (count($criteria)) {
            if ($this->setFilterData($criteria)) {
                return $this->query->orderBy('id', 'DESC')->paginate(env('PAGINATION_COUNT'), $select);
            } else {
                return 'no_filter';
            }
        }
        if (!empty($this->orderBy)) {
            $this->query->orderByRaw($this->orderBy);
        }
        return $this->query->paginate(env('PAGINATION_COUNT'), $select);
    }

    public function findBy(array $criteria, $select = ['*'], $trashed = false) {
        if ($trashed) {
            $this->query->onlyTrashed();
        }
        if (!empty($this->orderBy)) {
            $this->query->orderByRaw($this->orderBy);
        }
        if ($this->setFilterData($criteria)) {
            return $this->query->get($select);
        } else {
            return 'no_filter';
        }
    }

    public function findOneID($id, $criatria = [], $select = ['*'], $trashed = false) {
        if ($trashed) {
            $this->query->onlyTrashed();
        }
        count($criatria) > 0 ?? $this->setFilterData($criteria);
        if (!empty($this->orderBy)) {
            $this->query->orderByRaw($this->orderBy);
        }
        return $this->query->findOrFail((int) $id, $select);
    }

    public function findOneBy(array $criteria, $select = ['*'], $trashed = false) {
        if ($trashed) {
            $this->query->onlyTrashed();
        }
        $this->setFilterData($criteria);
        if (!empty($this->orderBy)) {
            $this->query->orderByRaw($this->orderBy);
        }
        return $this->query->first($select);
    }

    /*
     * ---------------------------
     * |                         |
     * |  End queries functions  |
     * |                         |
     * ---------------------------
     */

    public function updateDataByFilter(array $criteria, array $data) {
        $item = $this->findBy($criteria);
        if (!$item) {
            return 'no_data';
        }
        $update = false;
        try {
            \DB::connection()->beginTransaction();
            $this->setColumnsDataValues($data);
            if ($this->setFilterData($criteria)) {
                $update = $this->query->update($this->columnsValue);
                if ($update) {
                    $updatedData = $this->findBy($criteria);
                    $this->mutation->additionalQueriesFired(['model' => $updatedData, 'type' => 'update', 'count' => 'many']);
                    \DB::connection()->commit();
                    return $updatedData;
                }
            }
            \DB::connection()->rollBack();
            return ['error' => '1', 'msg' => 'No data found'];
        } catch (\PDOException $e) {
            \DB::connection()->rollBack();
            return ['error' => '1', 'msg' => $e->getMessage()];
        }
    }

}
