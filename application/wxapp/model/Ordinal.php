<?php

namespace app\wxapp\model;

use app\admin\library\Auth;
use think\Model;
use traits\model\SoftDelete;

class Ordinal extends    \app\common\model\Ordinal
{
// 追加属性
    public $append = ["condition_text"];

    protected static function init()
    {
        parent::init();

    }
}
