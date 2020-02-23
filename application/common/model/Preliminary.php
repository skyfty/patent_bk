<?php

namespace app\common\model;

use think\Model;

class Preliminary extends Cosmetic
{
    // 表名
    protected $name = 'preliminary';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PM%06d", $maxid);
        });
    }

    public function relevance()
    {
        return $this->morphOne('promotion', 'relevance_model');
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
