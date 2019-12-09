<?php

namespace app\common\model;

use think\Model;

class Modelx extends Model
{
    // 表名
    protected $name = 'model';

    public function getFieldsAttr($value, $data)
    {
        return is_array($value) ? $value : ($value ? explode(',', $value) : []);
    }

    public function fields()
    {
        return $this->hasMany('Fields','model_id');
    }

}
