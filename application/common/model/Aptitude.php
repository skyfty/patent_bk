<?php

namespace app\common\model;

class Aptitude extends Cosmetic
{
    // 表名
    protected $name = 'aptitude';
    public $keywordsFields = ["name", "idcode"];

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
