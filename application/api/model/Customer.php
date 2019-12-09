<?php

namespace app\api\model;

use think\Model;

class Customer extends  \app\common\model\Customer
{
    // 追加属性
    protected $append = [
    ];
    protected static function init()
    {
        parent::init();
    }

    public function getBirthdayAttr($value, $data)
    {
        return date("Y-m-d",$data['birthday']);

    }

}

