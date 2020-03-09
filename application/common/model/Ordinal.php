<?php

namespace app\common\model;

use think\Model;

class Ordinal extends Cosmetic
{
    // 表名
    protected $name = 'ordinal';
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
    public function syllable() {
        return $this->hasOne('syllable','id','syllable_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function policy() {
        return $this->hasOne('policy','id','policy_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
