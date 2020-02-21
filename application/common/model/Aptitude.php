<?php

namespace app\common\model;

class Aptitude extends Cosmetic
{
    // 表名
    protected $name = 'aptitude';
    public $keywordsFields = ["name", "idcode"];


    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("AP%06d", $maxid);
        });
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function relevance()
    {
        return $this->morphOne('provider', 'provider_model');
    }


    public function species()
    {
        return $this->hasOne('species','id','species_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
