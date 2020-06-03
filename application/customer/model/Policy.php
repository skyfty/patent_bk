<?php

namespace app\customer\model;
use app\admin\library\Auth;

use think\Model;
use traits\model\SoftDelete;

class Policy extends   \app\common\model\Policy
{
    // 追加属性

    protected static function init()
    {
        parent::init();
    }
}

