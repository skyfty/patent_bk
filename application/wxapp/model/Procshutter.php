<?php

namespace app\wxapp\model;

use app\admin\library\Auth;
use think\Model;
use traits\model\SoftDelete;

class Procshutter extends   \app\common\model\Procshutter
{
    protected static function init()
    {
        parent::init();
    }
}
