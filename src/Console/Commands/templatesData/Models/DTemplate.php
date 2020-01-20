<?php

/**
 * DTemplates model 
 * To connect with database and make all queries  
 * all functions return with eloqouent object or array of objects
 * 
 * @author Mohamed EL-Absy <mohamed.elabsy@yahoo.com>
 * @copyright (c) 2018, OlaHub LLC
 * @version 1.0.0 
 */

namespace OlaHub\Models;

class DTemplate extends OlaHubCommonModels {

    public function __construct(array $attributes = array()) {
        parent::__construct($attributes);
    }

    protected $table = 'dtemplate';
    
    protected $columnsMaping = [
        /*
         * Sample of how this variable will field
        'the that will come from request' => [
            'column' => 'the name in db table',
            'type' => 'if the data need handling before insert',
            'relation' => if this column in manyToMany model write full model name with name space,
            'validation' => 'laravel validation roles'
        ],
        'the that will come from request' => [
            'column' => 'the name in db table',
            'type' => 'if the data need handling before insert',
            'relation' => if this column in manyToMany model write full model name with name space,
            'validation' => 'laravel validation roles',
            'filterValidation' => 'laravel validation but in general to use in filter',
        ],
         */
    ];

    /*
     * Sample to relational request 
     *
    public function TemplateManyRelation() {
        return $this->hasMany('OlaHub\Models\ManyToMany\TemplateMany','foriegn_key');
    }

    public function TemplateData() {
        return $this->belongsTo('OlaHub\Models\TemplateOne','foriegn_key','primary_key');
    }
 */
}