<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Plan extends \app\common\model\Plan
{
    // 追加属性
    protected $append = [
    ];

    protected static function init()
    {
        parent::init();
    }

}

