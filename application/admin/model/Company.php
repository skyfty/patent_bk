<?php

namespace app\admin\model;

use app\admin\library\Auth;
use fast\Random;
use think\Model;

class Company extends \app\common\model\Company
{
    // 追加属性
    protected $append = [
    ];

    protected static function init()
    {
        parent::init();
    }

}

