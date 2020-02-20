<?php

namespace app\common\model;

class Business extends Cosmetic
{
    // 表名
    protected $name = 'business';
    public $keywordsFields = ["name", "idcode"];

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function relevance()
    {
        return $this->morphMany('provider', 'provider_model');
    }
}
