<?php

namespace app\common\model;

class Promotion extends Cosmetic
{
    use \traits\model\SoftDelete;

    // 表名
    protected $name = 'promotion';
    public $keywordsFields = ["name", "idcode"];

    public function getPictureListAttr($value, $data)
    {
        $value = $value ? $value : $data['pictures'];
        if ($value) {
            $value = explode(",", $value);
        }
        return $value ? $value : [];
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->setEagerlyType(0);
    }

}
