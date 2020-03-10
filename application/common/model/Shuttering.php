<?php

namespace app\common\model;

use think\Model;

class Shuttering extends Cosmetic
{
    // 表名
    protected $name = 'shuttering';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CO%06d", $maxid);
        });
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
