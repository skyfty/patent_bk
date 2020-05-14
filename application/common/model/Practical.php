<?php

namespace app\common\model;

use think\Model;

class Practical extends Professional
{
    // 表名
    protected $name = 'practical';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PR%06d", $maxid);
        });
    }


    public function patentclass()
    {
        return $this->hasOne('patentclass','id','patentclass_cascader_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function promotion() {
        return $this->hasOne('promotion','id','promotion_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
