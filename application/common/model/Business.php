<?php

namespace app\common\model;

class Business extends Cosmetic
{
    // 表名
    protected $name = 'business';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("BU%06d", $maxid);
        });
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function relevance()
    {
        return $this->morphMany('provider', 'provider_model');
    }


    public function species()
    {
        return $this->hasOne('species','id','species_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
