<?php

namespace app\common\model;

use think\Model;

class Alternating extends Cosmetic
{
    // 表名
    protected $name = 'alternating';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("IN%06d", $maxid);
        });
    }

    public function procedure() {
        return $this->hasOne('procedure','id','procedure_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
