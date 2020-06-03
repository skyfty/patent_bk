<?php

namespace app\customer\model;

use think\Model;

class Actualize extends  \app\common\model\Actualize
{
    public $append = ['policy'];

    protected static function init()
    {
        parent::init();
    }
}
