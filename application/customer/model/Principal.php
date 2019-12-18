<?php

namespace app\customer\model;
use app\admin\library\Auth;

use think\Model;
use traits\model\SoftDelete;

class Principal extends  \app\common\model\Principal
{
    // 追加属性

    protected static function init()
    {
        parent::init();
    }


}
