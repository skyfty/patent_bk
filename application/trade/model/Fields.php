<?php

namespace app\trade\model;

use app\admin\library\Alter;
use app\common\model\Config;
use think\Model;
use think\Db;

class Fields extends \app\common\model\Fields
{
    // 追加属性
    protected $append = [
        'content_list',
    ];

    protected static function init()
    {

    }

}
