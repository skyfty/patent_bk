<?php

namespace app\common\model;

class Aptitude extends Professional
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

    public function company() {
        return $this->hasOne('company','id','company_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
