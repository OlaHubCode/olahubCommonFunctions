<?php

/**
 * DTemplates response handler 
 * Handling response object how it will show  
 * all functions return with object
 * 
 * @author Mohamed EL-Absy <mohamed.elabsy@yahoo.com>
 * @copyright (c) 2018, OlaHub LLC
 * @version 1.0.0
 */

namespace OlaHub\ResponseHandlers;

use OlaHub\Models\DTemplate;
use League\Fractal;

class DTemplatesResponseHandler extends Fractal\TransformerAbstract {

    private $return;
    private $data;

    public function transform(DTemplate $data) {
        $this->data = $data;
        $this->setDefaultData();
        /*
         * here to set other responses
         * 
         * $this->setDataOne();
         * $this->setDataTwo();
         * 
         */
        $this->setDates();
        return $this->return;
    }

    private function setDefaultData() {
        $this->return = [
            /*
             * "keyOne" => isset($this->data->column) ? $this->data->column : 'def value',
             * "keyTwo" => isset($this->data->column) ? $this->data->column : 'def value',
             * "keyThree" => isset($this->data->column) ? $this->data->column : 'def value',
             * 
             */
        ];
    }

    /* additional data 
     private function setDataOne() {
        $returnVar = [];
        foreach ($this->data->TemplateManyRelation as $oneEntry) {
            $langualField = \OlaHub\Helpers\DTemplatesHelper::returnCurrentLangField($oneEntry, 'column');
            $returnVar[] = [
                "keyOne" => isset($this->data->column) ? $this->data->column : 'def value',
                "keyOne" => isset($this->data->column) ? $this->data->column : 'def value',
                "keyOne" => isset($this->data->column) ? $this->data->column : 'def value',
                "langualKey" => $langualField,
            ];
        }

        $this->return["responseKey"] = $returnVar;
    }

    private function setDataTwo() {
        $TemplateData = $this->data->TemplateData;
        $this->return["responseKey"] = isset($TemplateData->column) ? $TemplateData->column : 0;
        $this->return["responseKey"] = isset($TemplateData->column) ? $TemplateData->column : 'N/A';
    }
     * 
     */

    private function setDates() {
        $this->return["created"] = isset($this->data->created_at) ? \OlaHub\Helpers\DTemplatesHelper::convertStringToDate($this->data->created_at) : "N/A";
        $this->return["creator"] = \OlaHub\Helpers\DTemplatesHelper::defineRowCreator($this->data);
        $this->return["updated"] = isset($this->data->updated_at) ? \OlaHub\Helpers\DTemplatesHelper::convertStringToDate($this->data->updated_at) : "N/A";
        $this->return["updater"] = \OlaHub\Helpers\DTemplatesHelper::defineRowUpdater($this->data);
    }

}