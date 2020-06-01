<?php

namespace app\common\model;

use think\Model;

class Equipment extends Cosmetic
{
    protected $name = 'equipment';
    // 表名
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();
        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("EQ%06d", $maxid);
        });
    }
    public function warehouse()
    {
        return $this->morphMany('warehouse', 'model');
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function classroom() {
        return $this->hasOne('classroom','id','classroom_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

}
