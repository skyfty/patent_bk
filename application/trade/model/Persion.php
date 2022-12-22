<?php

namespace app\trade\model;

use app\admin\library\Auth;
use fast\Random;
use think\Model;

class Persion extends \app\common\model\Persion
{
    // 追加属性
    protected $append = [
    ];

    protected static function init()
    {
        parent::init();
    }

}

