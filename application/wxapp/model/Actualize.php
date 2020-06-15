<?php

namespace app\wxapp\model;

use think\Model;

class Actualize extends  \app\common\model\Actualize
{
    public $append = ['policy'];

    protected static function init()
    {
        parent::init();
    }
}
